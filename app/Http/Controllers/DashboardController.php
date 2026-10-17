<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
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

        $activeYear = $village->active_tax_year;
        $spptQuery = \App\Models\Sppt::where('tax_year', $activeYear);

        $totalBills = (clone $spptQuery)->sum('total_bill');
        $realizationAmount = (clone $spptQuery)->sum('paid_amount');
        $totalSppts = (clone $spptQuery)->count();

        // Target defaults to village target_amount or total SPPT bills if target is not set
        $targetAmount = $village->target_amount > 0 ? (float)$village->target_amount : (float)$totalBills;
        $remainingAmount = max(0, $targetAmount - $realizationAmount);
        $realizationPercent = $targetAmount > 0 ? round(($realizationAmount / $targetAmount) * 100, 1) : 0;

        $stats = [
            'village_name' => $village->name,
            'active_tax_year' => $activeYear,
            'target_amount' => $targetAmount,
            'realization_amount' => $realizationAmount,
            'realization_percent' => $realizationPercent,
            'remaining_amount' => $remainingAmount,
            'total_users' => User::count(),
            'total_sppts' => $totalSppts,
            'cash_in_hand' => 0,
            'unremitted_deposit' => 0,
        ];

        $recentAudits = AuditLog::with('user')
            ->latest('id')
            ->take(8)
            ->get();

        return view('dashboard.index', compact('village', 'user', 'stats', 'recentAudits'));
    }
}
