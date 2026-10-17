<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use App\Models\CashHandover;
use App\Models\Payment;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashBookController extends Controller
{
    public function index(Request $request): View
    {
        $village = Village::current();

        // 1. Total Pembayaran Sah Diterima
        $totalPaymentsValid = (float)Payment::where('status', 'valid')->sum('total_amount');

        // 2. Kas Dipegang Kasir (Transaksi sah yang belum diserahterimakan)
        $cashHeldByCashiers = (float)Payment::where('status', 'valid')
            ->whereNull('handover_id')
            ->sum('total_amount');

        // 3. Kas yang Sedang Diserahterimakan (Submitted tapi belum Dikonfirmasi Kolektor)
        $cashInTransit = (float)CashHandover::where('status', 'submitted')->sum('physical_amount');

        // 4. Kas Dipegang Kolektor (Diterima dari kasir, dikurangi setoran yang sudah di-approve)
        $collectorReceived = (float)CashHandover::where('status', 'received')->sum('physical_amount');
        $approvedDeposited = (float)BankDeposit::where('status', 'approved')->sum('deposit_amount');
        $cashHeldByCollectors = max(0, $collectorReceived - $approvedDeposited);

        // 5. Total Setoran Kas yang Telah Disahkan Masuk Bank
        $totalBankDeposited = $approvedDeposited;

        // 6. Akumulasi Selisih Kas Fisik
        $totalDifference = (float)CashHandover::sum('difference_amount');

        // Rekapitulasi per Kasir
        $cashiers = User::whereIn('role', ['kadus', 'admin'])->get()->map(function ($u) {
            $unhanded = (float)Payment::where('cashier_user_id', $u->id)
                ->where('status', 'valid')
                ->whereNull('handover_id')
                ->sum('total_amount');

            $totalReceived = (float)Payment::where('cashier_user_id', $u->id)
                ->where('status', 'valid')
                ->sum('total_amount');

            return [
                'user' => $u,
                'unhanded_cash' => $unhanded,
                'total_received' => $totalReceived,
            ];
        });

        // Rekapitulasi per Kolektor
        $collectors = User::where('role', 'kolektor')->get()->map(function ($u) {
            $rec = (float)CashHandover::where('collector_user_id', $u->id)->where('status', 'received')->sum('physical_amount');
            $dep = (float)BankDeposit::where('collector_user_id', $u->id)->where('status', 'approved')->sum('deposit_amount');

            return [
                'user' => $u,
                'held_balance' => max(0, $rec - $dep),
                'total_deposited' => $dep,
            ];
        });

        $summary = [
            'total_payments' => $totalPaymentsValid,
            'cashiers_held' => $cashHeldByCashiers,
            'in_transit' => $cashInTransit,
            'collectors_held' => $cashHeldByCollectors,
            'bank_deposited' => $totalBankDeposited,
            'total_difference' => $totalDifference,
            'current_village_cash' => $cashHeldByCashiers + $cashInTransit + $cashHeldByCollectors,
        ];

        // Jurnal Kas Kronologis (Buku Kas Umum)
        $payments = Payment::where('status', 'valid')
            ->with(['sppt.taxObject', 'cashier'])
            ->get()
            ->map(function ($p) {
                return [
                    'datetime' => $p->payment_date,
                    'ref' => $p->receipt_number,
                    'type' => 'in',
                    'type_label' => 'Uang Masuk',
                    'party' => $p->payer_name,
                    'description' => 'Penerimaan PBB NOP ' . ($p->sppt?->taxObject?->formatted_nop ?? '-') . ' (' . ($p->cashier->name ?? 'Kasir') . ')',
                    'amount_in' => (float)$p->total_amount,
                    'amount_out' => 0.0,
                ];
            });

        $deposits = BankDeposit::where('status', 'approved')
            ->with(['collector'])
            ->get()
            ->map(function ($d) {
                return [
                    'datetime' => $d->approved_at ?? ($d->created_at ?? \Illuminate\Support\Carbon::parse($d->deposit_date)->endOfDay()),
                    'ref' => $d->deposit_number,
                    'type' => 'out',
                    'type_label' => 'Uang Keluar',
                    'party' => $d->destination_bank ?? 'Bank BJB',
                    'description' => 'Setoran Kas PBB ke Bank (' . ($d->bank_ref_number ? "No. Bukti: {$d->bank_ref_number}, " : '') . 'Kolektor: ' . ($d->collector->name ?? '-') . ')',
                    'amount_in' => 0.0,
                    'amount_out' => (float)$d->deposit_amount,
                ];
            });

        $running = 0.0;
        $ledger = $payments->concat($deposits)
            ->sortBy('datetime')
            ->values()
            ->map(function ($item) use (&$running) {
                $running += $item['amount_in'] - $item['amount_out'];
                $item['running_balance'] = $running;
                return $item;
            })
            ->reverse()
            ->values();

        return view('cashbook.index', compact('village', 'summary', 'cashiers', 'collectors', 'ledger'));
    }
}
