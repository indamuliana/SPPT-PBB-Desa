<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BankDeposit;
use App\Models\CashHandover;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BankDepositController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = BankDeposit::with(['collector', 'verifier']);

        if ($user->isKolektor()) {
            $query->where('collector_user_id', $user->id);
        }

        $deposits = $query->latest('deposit_date')->paginate(15)->withQueryString();

        // Calculate available cash balance held by collector
        $availableBalance = 0;
        if ($user->isKolektor()) {
            $receivedCash = (float)CashHandover::where('collector_user_id', $user->id)
                ->where('status', 'received')
                ->sum('physical_amount');

            $approvedDeposited = (float)BankDeposit::where('collector_user_id', $user->id)
                ->where('status', 'approved')
                ->sum('deposit_amount');

            $availableBalance = max(0, $receivedCash - $approvedDeposited);
        }

        return view('deposits.index', compact('deposits', 'availableBalance'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $village = Village::current();

        $receivedCash = (float)CashHandover::where('collector_user_id', $user->id)
            ->where('status', 'received')
            ->sum('physical_amount');

        $approvedDeposited = (float)BankDeposit::where('collector_user_id', $user->id)
            ->where('status', 'approved')
            ->sum('deposit_amount');

        $availableBalance = max(0, $receivedCash - $approvedDeposited);

        return view('deposits.create', compact('availableBalance', 'village'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $receivedCash = (float)CashHandover::where('collector_user_id', $user->id)
            ->where('status', 'received')
            ->sum('physical_amount');

        $approvedDeposited = (float)BankDeposit::where('collector_user_id', $user->id)
            ->where('status', 'approved')
            ->sum('deposit_amount');

        $availableBalance = max(0, $receivedCash - $approvedDeposited);

        $validated = $request->validate([
            'deposit_date' => ['required', 'date'],
            'destination_bank' => ['required', 'string', 'max:100'],
            'deposit_amount' => ['required', 'numeric', 'min:1000', "max:{$availableBalance}"],
            'bank_ref_number' => ['nullable', 'string', 'max:100'],
            'rejection_notes' => ['nullable', 'string'],
        ], [
            'deposit_amount.max' => 'Nominal setoran tidak boleh melebihi saldo kas fisik yang Anda pegang (Rp ' . number_format($availableBalance, 0, ',', '.') . ').',
        ]);

        $deposit = BankDeposit::create([
            'deposit_number' => BankDeposit::generateDepositNumber(),
            'collector_user_id' => $user->id,
            'deposit_date' => $validated['deposit_date'],
            'destination_bank' => $validated['destination_bank'],
            'deposit_amount' => $validated['deposit_amount'],
            'bank_ref_number' => $validated['bank_ref_number'] ?? null,
            'status' => 'approved',
            'verifier_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        AuditLog::log(
            'deposit',
            "Kolektor mencatat & mengesahkan setoran bank #{$deposit->deposit_number} sebesar Rp " . number_format($deposit->deposit_amount, 0, ',', '.') . " tujuan {$deposit->destination_bank}",
            $deposit
        );

        return redirect()->route('deposits.index')->with('success', 'Setoran kas ke bank berhasil dicatat dan disahkan.');
    }

    public function approve(BankDeposit $deposit): RedirectResponse
    {
        if (! auth()->user()->isKolektor() && ! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Kolektor Desa yang berwenang mengesahkan setoran bank.');
        }

        if ($deposit->isApproved()) {
            return back()->with('error', 'Setoran ini sudah disetujui sebelumnya.');
        }

        $deposit->update([
            'status' => 'approved',
            'verifier_user_id' => auth()->id(),
            'approved_at' => now(),
        ]);

        AuditLog::log(
            'deposit',
            "Kolektor mengesahkan setoran bank #{$deposit->deposit_number} sebesar Rp " . number_format($deposit->deposit_amount, 0, ',', '.'),
            $deposit
        );

        return back()->with('success', "Setoran bank #{$deposit->deposit_number} berhasil disahkan. Kas resmi masuk ke kas negara/desa.");
    }

    public function reject(BankDeposit $deposit, Request $request): RedirectResponse
    {
        if (! auth()->user()->isKolektor() && ! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Kolektor Desa yang berwenang menolak setoran bank.');
        }

        $request->validate([
            'rejection_notes' => ['required', 'string', 'max:500'],
        ]);

        $deposit->update([
            'status' => 'rejected',
            'verifier_user_id' => auth()->id(),
            'rejection_notes' => $request->rejection_notes,
        ]);

        AuditLog::log(
            'deposit',
            "Admin menolak setoran bank #{$deposit->deposit_number}. Alasan: {$request->rejection_notes}",
            $deposit
        );

        return back()->with('success', "Setoran bank #{$deposit->deposit_number} telah ditolak.");
    }
}
