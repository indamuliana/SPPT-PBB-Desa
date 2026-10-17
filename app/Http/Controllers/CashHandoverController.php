<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\CashHandover;
use App\Models\Payment;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashHandoverController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = CashHandover::with(['cashier', 'collector', 'payments']);

        if ($user->isKadus()) {
            $query->where('cashier_user_id', $user->id);
        } elseif ($user->isKolektor()) {
            $query->where('collector_user_id', $user->id);
        }

        $handovers = $query->latest('handover_date')->paginate(15)->withQueryString();

        return view('handovers.index', compact('handovers'));
    }

    public function create(): View
    {
        $user = auth()->user();

        // Payments recorded by this cashier that haven't been handed over
        $unhandedPayments = Payment::where('cashier_user_id', $user->id)
            ->where('status', 'valid')
            ->whereNull('handover_id')
            ->with(['sppt.taxObject.taxpayer'])
            ->latest('payment_date')
            ->get();

        $collectors = User::where('role', 'kolektor')->where('is_active', true)->get();

        return view('handovers.create', compact('unhandedPayments', 'collectors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'collector_user_id' => ['required', 'exists:users,id'],
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['exists:payments,id'],
            'physical_amount' => ['required', 'numeric', 'min:0'],
            'difference_reason' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $cashierId = auth()->id();

        $handover = DB::transaction(function () use ($validated, $cashierId) {
            $payments = Payment::whereIn('id', $validated['payment_ids'])
                ->where('cashier_user_id', $cashierId)
                ->where('status', 'valid')
                ->whereNull('handover_id')
                ->lockForUpdate()
                ->get();

            if ($payments->count() !== count($validated['payment_ids'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'payment_ids' => 'Satu atau lebih transaksi sudah pernah diserahterimakan atau tidak valid.',
                ]);
            }

            $systemAmount = (float)$payments->sum('total_amount');
            $physicalAmount = (float)$validated['physical_amount'];
            $difference = $physicalAmount - $systemAmount;

            if ($difference != 0 && empty($validated['difference_reason'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'difference_reason' => 'Terdapat selisih kas sebesar Rp ' . number_format(abs($difference), 0, ',', '.') . '. Anda wajib mengisi alasan selisih kas.',
                ]);
            }

            $handoverRecord = CashHandover::create([
                'handover_number' => CashHandover::generateHandoverNumber(),
                'cashier_user_id' => $cashierId,
                'collector_user_id' => $validated['collector_user_id'],
                'handover_date' => now(),
                'system_amount' => $systemAmount,
                'physical_amount' => $physicalAmount,
                'difference_amount' => $difference,
                'difference_reason' => $validated['difference_reason'] ?? null,
                'status' => 'submitted',
                'notes' => $validated['notes'] ?? null,
            ]);

            Payment::whereIn('id', $payments->pluck('id'))
                ->update(['handover_id' => $handoverRecord->id]);

            AuditLog::log(
                'handover',
                "Kasir menyerahkan kas #{$handoverRecord->handover_number} sebesar Rp " . number_format($physicalAmount, 0, ',', '.') . " ({$payments->count()} transaksi) ke Kolektor",
                $handoverRecord
            );

            return $handoverRecord;
        });

        return redirect()->route('handovers.show', $handover)->with('success', 'Serah terima kas berhasil dibuat dan menunggu konfirmasi fisik dari Kolektor.');
    }

    public function show(CashHandover $handover): View
    {
        $handover->load(['cashier', 'collector', 'payments.sppt.taxObject.taxpayer']);
        $village = Village::current();

        return view('handovers.show', compact('handover', 'village'));
    }

    public function receive(CashHandover $handover): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->isKolektor() && ! $user->isAdmin()) {
            abort(403, 'Hanya Kolektor atau Admin yang berwenang mengonfirmasi penerimaan fisik kas.');
        }

        if ($handover->isReceived()) {
            return back()->with('error', 'Serah terima ini sudah pernah dikonfirmasi sebelumnya.');
        }

        $handover->update([
            'status' => 'received',
            'received_at' => now(),
        ]);

        AuditLog::log(
            'handover',
            "Kolektor {$user->name} mengonfirmasi penerimaan fisik uang serah terima #{$handover->handover_number} sebesar Rp " . number_format($handover->physical_amount, 0, ',', '.'),
            $handover
        );

        return back()->with('success', "Serah terima #{$handover->handover_number} telah dikonfirmasi diterima. Saldo kas kolektor bertambah Rp " . number_format($handover->physical_amount, 0, ',', '.'));
    }
}
