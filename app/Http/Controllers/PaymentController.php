<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Dusun;
use App\Models\Payment;
use App\Models\PaymentCancellation;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PaymentController extends Controller
{
    /**
     * Loket Kasir POS: Pencarian NOP dan form pembayaran
     */
    public function pos(Request $request): View
    {
        $village = Village::current();
        $user = $request->user();
        $isKadus = $user && $user->isKadus();
        $assignedDusunIds = $isKadus ? $user->assignedDusunIds() : [];

        // Default Kadus ke wilayah binaannya ("my") jika belum ada parameter dusun di URL
        $dusun = $request->input('dusun');
        if ($isKadus && ! empty($assignedDusunIds) && ! $request->has('dusun')) {
            $dusun = 'my';
        }

        $search = $request->input('search');
        $statusFilter = $request->input('status', 'unpaid');

        // 1. Table Query for left panel
        $tableQuery = Sppt::with(['taxObject.taxpayer', 'taxObject.dusunRelation'])
            ->where('tax_year', $village->active_tax_year);

        if ($statusFilter === 'unpaid') {
            $tableQuery->whereIn('status', ['unpaid', 'partial']);
        } elseif ($statusFilter !== 'all') {
            $tableQuery->where('status', $statusFilter);
        }

        if ($search) {
            $cleanNop = preg_replace('/[^0-9]/', '', $search);
            $tableQuery->where(function ($q) use ($search, $cleanNop) {
                if (! empty($cleanNop)) {
                    $q->whereHas('taxObject', function ($oq) use ($cleanNop) {
                        $oq->where('nop', 'like', "%{$cleanNop}%");
                    });
                }
                $q->orWhereHas('taxObject.taxpayer', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($dusun === 'my' && ! empty($assignedDusunIds)) {
            $tableQuery->whereHas('taxObject', function ($oq) use ($assignedDusunIds) {
                $oq->whereIn('dusun_id', $assignedDusunIds);
            });
        } elseif ($dusun && $dusun !== 'all') {
            $tableQuery->whereHas('taxObject', function ($oq) use ($dusun) {
                if (is_numeric($dusun)) {
                    $oq->where('dusun_id', $dusun);
                } else {
                    $oq->where(function ($sub) use ($dusun) {
                        $sub->where('dusun', $dusun)
                            ->orWhereHas('dusunRelation', function ($dq) use ($dusun) {
                                $dq->where('name', $dusun);
                            });
                    });
                }
            });
        }

        $spptList = $tableQuery->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $dusunList = Dusun::orderBy('name')->get();

        // 2. Selection for right panel
        $selectedSppt = null;
        $taxObject = null;
        $unpaidSppts = collect();
        $paidHistory = collect();

        if ($request->filled('selected_sppt_id')) {
            $selectedSppt = Sppt::with(['taxObject.taxpayer', 'taxObject.dusunRelation', 'payments.cashier'])->find($request->selected_sppt_id);
            if ($selectedSppt) {
                $taxObject = $selectedSppt->taxObject;
            }
        } elseif ($search) {
            $cleanNop = preg_replace('/[^0-9]/', '', $search);
            $taxObject = TaxObject::with(['taxpayer', 'dusunRelation', 'sppts' => function ($q) {
                $q->orderByDesc('tax_year');
            }])
            ->where(function ($q) use ($search, $cleanNop) {
                if (! empty($cleanNop)) {
                    $q->where('nop', 'like', "%{$cleanNop}%");
                }
                $q->orWhereHas('taxpayer', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%");
                });
            })
            ->first();

            if ($taxObject) {
                $selectedSppt = $taxObject->sppts->where('status', '!=', 'paid')->first() ?? $taxObject->sppts->first();
            }
        }

        if ($taxObject) {
            $unpaidSppts = $taxObject->sppts->where('status', '!=', 'paid');
            $paidHistory = Payment::whereIn('sppt_id', $taxObject->sppts->pluck('id'))
                ->with('cashier')
                ->latest('payment_date')
                ->take(10)
                ->get();
        }

        return view('payments.pos', compact(
            'village', 
            'search', 
            'dusun', 
            'isKadus',
            'assignedDusunIds',
            'statusFilter', 
            'spptList', 
            'dusunList', 
            'selectedSppt', 
            'taxObject', 
            'unpaidSppts', 
            'paidHistory'
        ));
    }

    /**
     * Proses transaksi pembayaran tunai (penuh atau cicilan)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sppt_id' => ['required', 'exists:sppts,id'],
            'amount_principal' => ['required', 'numeric', 'min:1'],
            'amount_fine' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $fine = (float)($validated['amount_fine'] ?? 0);
        $principal = (float)$validated['amount_principal'];
        $totalPaid = $principal + $fine;

        $payment = DB::transaction(function () use ($validated, $principal, $fine, $totalPaid) {
            // Lock SPPT and related TaxObject for update to prevent concurrent race condition
            $sppt = Sppt::where('id', $validated['sppt_id'])->lockForUpdate()->firstOrFail();
            $taxObject = TaxObject::where('id', $sppt->tax_object_id)->lockForUpdate()->firstOrFail();

            // Strict rule: TO objects CANNOT receive payments until mutated
            if ($taxObject->isTo()) {
                throw new \InvalidArgumentException('SPPT berstatus Tanpa Objek (TO). Pembayaran ditolak sampai data mutasi pemilik baru diselesaikan.');
            }

            $currentRemaining = $sppt->remaining_bill;

            if ($principal > $currentRemaining) {
                throw new \InvalidArgumentException('Nominal pembayaran pokok (Rp ' . number_format($principal, 0, ',', '.') . ') melampaui sisa tagihan (Rp ' . number_format($currentRemaining, 0, ',', '.') . ').');
            }

            $newPaidAmount = (float)$sppt->paid_amount + $principal;
            $remainingAfter = max(0, (float)$sppt->total_bill - $newPaidAmount);

            // Update SPPT
            $sppt->paid_amount = $newPaidAmount;
            $sppt->recalculateStatus();
            $sppt->save();

            // Create Payment record
            $paymentRecord = Payment::create([
                'receipt_number' => Payment::generateReceiptNumber(),
                'verification_token' => Payment::generateVerificationToken(),
                'sppt_id' => $sppt->id,
                'cashier_user_id' => auth()->id(),
                'payment_date' => now(),
                'amount_principal' => $principal,
                'amount_fine' => $fine,
                'total_amount' => $totalPaid,
                'remaining_after' => $remainingAfter,
                'payment_method' => 'cash',
                'status' => 'valid',
                'notes' => $validated['notes'] ?? null,
            ]);

            AuditLog::log(
                'create',
                "Pembayaran PBB NOP {$taxObject->formatted_nop} Tahun {$sppt->tax_year} sebesar Rp " . number_format($totalPaid, 0, ',', '.') . " (No: {$paymentRecord->receipt_number})",
                $paymentRecord
            );

            return $paymentRecord;
        });

        return redirect()->route('payments.receipt', $payment)->with('success', 'Pembayaran berhasil disimpan. Kuitansi siap dicetak.');
    }

    /**
     * Tampilan Kuitansi Cetak (A4 2-in-1 dan Thermal 58/80mm)
     */
    public function receipt(Payment $payment, Request $request): View
    {
        $payment->load(['sppt.taxObject.taxpayer', 'cashier']);
        $village = Village::current();

        $format = $request->query('format', 'a4'); // 'a4' or 'thermal'

        // Generate QR code SVG (vector format for crisp printing)
        $qrCodeSvg = QrCode::size(110)->generate($payment->verification_url);

        return view('payments.receipt', compact('payment', 'village', 'format', 'qrCodeSvg'));
    }

    /**
     * Riwayat Kuitansi & Transaksi
     */
    public function history(Request $request): View
    {
        $user = $request->user();
        $query = Payment::with(['sppt.taxObject.taxpayer', 'cashier', 'cancellation']);

        // Kasir only sees their own transactions unless admin/kolektor/bendahara/kades
        if ($user->isKadus()) {
            $query->where('cashier_user_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $cleanNop = preg_replace('/[^0-9]/', '', $search);
            $query->where(function ($q) use ($search, $cleanNop) {
                $q->where('receipt_number', 'like', "%{$search}%");
                if (! empty($cleanNop)) {
                    $q->orWhereHas('sppt.taxObject', function ($oq) use ($cleanNop) {
                        $oq->where('nop', 'like', "%{$cleanNop}%");
                    });
                }
                $q->orWhereHas('sppt.taxObject.taxpayer', function ($tq) use ($search) {
                    $tq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('payment_date', $request->date);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();

        return view('payments.history', compact('payments'));
    }

    /**
     * Kasir mengajukan pembatalan pembayaran
     */
    public function requestCancellation(Payment $payment, Request $request): RedirectResponse
    {
        $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        if (! $payment->isValid()) {
            return back()->with('error', 'Hanya transaksi yang sah dan aktif yang dapat diajukan pembatalan.');
        }

        PaymentCancellation::create([
            'payment_id' => $payment->id,
            'requester_user_id' => auth()->id(),
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $payment->status = 'cancellation_requested';
        $payment->save();

        AuditLog::log('update', "Mengajukan pembatalan kuitansi #{$payment->receipt_number}. Alasan: {$request->reason}", $payment);

        return back()->with('success', 'Pengajuan pembatalan telah dikirimkan ke Admin Desa untuk ditinjau.');
    }

    /**
     * Admin menyetujui pembatalan pembayaran (pembalik saldo & tagihan)
     */
    public function approveCancellation(Payment $payment, Request $request): RedirectResponse
    {
        if ($payment->status !== 'cancellation_requested') {
            return back()->with('error', 'Transaksi tidak dalam status pengajuan pembatalan.');
        }

        DB::transaction(function () use ($payment) {
            $sppt = Sppt::where('id', $payment->sppt_id)->lockForUpdate()->firstOrFail();

            // Reverse the paid amount on SPPT
            $sppt->paid_amount = max(0, (float)$sppt->paid_amount - (float)$payment->amount_principal);
            $sppt->recalculateStatus();
            $sppt->save();

            // Update payment status
            $payment->status = 'cancelled';
            $payment->save();

            // Update cancellation record
            $cancellation = PaymentCancellation::where('payment_id', $payment->id)
                ->where('status', 'pending')
                ->first();

            if ($cancellation) {
                $cancellation->update([
                    'status' => 'approved',
                    'approver_user_id' => auth()->id(),
                    'approved_at' => now(),
                ]);
            }

            AuditLog::log(
                'cancel_payment',
                "Menyetujui pembatalan kuitansi #{$payment->receipt_number} sebesar Rp " . number_format($payment->total_amount, 0, ',', '.') . ". Tagihan SPPT NOP {$sppt->taxObject->formatted_nop} dipulihkan.",
                $payment
            );
        });

        return back()->with('success', "Pembatalan kuitansi #{$payment->receipt_number} berhasil disetujui. Sisa tagihan SPPT telah dipulihkan.");
    }

    /**
     * Admin menolak pengajuan pembatalan
     */
    public function rejectCancellation(Payment $payment, Request $request): RedirectResponse
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $cancellation = PaymentCancellation::where('payment_id', $payment->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $cancellation->update([
            'status' => 'rejected',
            'approver_user_id' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        $payment->status = 'valid';
        $payment->save();

        AuditLog::log('update', "Menolak pengajuan pembatalan kuitansi #{$payment->receipt_number}. Alasan penolakan: {$request->rejection_reason}", $payment);

        return back()->with('success', "Pengajuan pembatalan kuitansi #{$payment->receipt_number} telah ditolak. Transaksi tetap sah.");
    }
}
