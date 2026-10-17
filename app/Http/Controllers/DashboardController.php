<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BankDeposit;
use App\Models\CashHandover;
use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\ToCase;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $village = Village::current();
        $user = $request->user();

        $activeYear = (int)$village->active_tax_year;
        $spptQuery = Sppt::where('tax_year', $activeYear);

        $totalBills = (float)(clone $spptQuery)->sum('total_bill');
        $realizationAmount = (float)(clone $spptQuery)->sum('paid_amount');
        $totalSppts = (clone $spptQuery)->count();
        $paidSppts = (clone $spptQuery)->where('status', 'paid')->count();
        $partialSppts = (clone $spptQuery)->where('status', 'partial')->count();
        $unpaidSppts = (clone $spptQuery)->where('status', 'unpaid')->count();

        // Target defaults to village target_amount or total SPPT bills
        $targetAmount = $village->target_amount > 0 ? (float)$village->target_amount : (float)$totalBills;
        $remainingAmount = max(0, $totalBills - $realizationAmount);
        $realizationPercent = $totalBills > 0 ? round(($realizationAmount / $totalBills) * 100, 1) : 0;
        $targetPercent = $targetAmount > 0 ? round(($realizationAmount / $targetAmount) * 100, 1) : 0;

        // 3-Tier Cash Position
        $cashiersHeld = (float)Payment::where('status', 'valid')->whereNull('handover_id')->sum('total_amount');
        $collectorReceived = (float)CashHandover::where('status', 'received')->sum('physical_amount');
        $approvedDeposited = (float)BankDeposit::where('status', 'approved')->sum('deposit_amount');
        $collectorsHeld = max(0, $collectorReceived - $approvedDeposited);
        $bankDeposited = $approvedDeposited;

        // TO Cases Stats
        $totalToObjects = TaxObject::where('status', 'to')->count();
        $activeToCases = ToCase::whereIn('status', ['open', 'investigating'])->count();

        // Realization by Dusun
        $dusunStats = TaxObject::select('tax_objects.dusun')
            ->selectRaw('COUNT(sppts.id) as total_sppts')
            ->selectRaw('COALESCE(SUM(sppts.total_bill), 0) as total_bill')
            ->selectRaw('COALESCE(SUM(sppts.paid_amount), 0) as paid_amount')
            ->join('sppts', function ($join) use ($activeYear) {
                $join->on('sppts.tax_object_id', '=', 'tax_objects.id')
                    ->where('sppts.tax_year', '=', $activeYear);
            })
            ->groupBy('tax_objects.dusun')
            ->orderBy('tax_objects.dusun')
            ->get()
            ->map(function ($row) {
                $bill = (float)$row->total_bill;
                $paid = (float)$row->paid_amount;
                $pct = $bill > 0 ? round(($paid / $bill) * 100, 1) : 0;
                return [
                    'dusun' => $row->dusun ?: 'Lainnya / Luar Dusun',
                    'total_sppts' => (int)$row->total_sppts,
                    'total_bill' => $bill,
                    'paid_amount' => $paid,
                    'remaining' => max(0, $bill - $paid),
                    'percent' => $pct,
                ];
            });

        // Monthly Payments Trend (12 Months of Active Year)
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = sprintf('%04d-%02d-01 00:00:00', $activeYear, $m);
            $monthEnd = date('Y-m-t 23:59:59', strtotime($monthStart));
            $sum = (float)Payment::where('status', 'valid')
                ->whereBetween('payment_date', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $monthlyTrend[] = $sum;
        }

        // Recent 5 Valid Payments
        $recentPayments = Payment::with(['sppt.taxObject.taxpayer', 'cashier'])
            ->where('status', 'valid')
            ->latest('payment_date')
            ->take(5)
            ->get();

        $stats = [
            'village_name' => $village->name,
            'active_tax_year' => $activeYear,
            'target_amount' => $targetAmount,
            'target_percent' => $targetPercent,
            'total_bills' => $totalBills,
            'realization_amount' => $realizationAmount,
            'realization_percent' => $realizationPercent,
            'remaining_amount' => $remainingAmount,
            'total_users' => User::count(),
            'total_sppts' => $totalSppts,
            'paid_sppts' => $paidSppts,
            'partial_sppts' => $partialSppts,
            'unpaid_sppts' => $unpaidSppts,
            'cashiers_held' => $cashiersHeld,
            'collectors_held' => $collectorsHeld,
            'bank_deposited' => $bankDeposited,
            'total_to_objects' => $totalToObjects,
            'active_to_cases' => $activeToCases,
        ];

        $recentAudits = AuditLog::with('user')
            ->latest('id')
            ->take(6)
            ->get();

        return view('dashboard.index', compact(
            'village', 
            'user', 
            'stats', 
            'dusunStats', 
            'monthlyLabels', 
            'monthlyTrend', 
            'recentPayments', 
            'recentAudits'
        ));
    }
}
