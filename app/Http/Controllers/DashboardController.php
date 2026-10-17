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

        // In Phase 1, provide foundational counters and audit trail
        $stats = [
            'village_name' => $village->name,
            'active_tax_year' => $village->active_tax_year,
            'target_amount' => $village->target_amount,
            'realization_amount' => 0,
            'realization_percent' => 0,
            'remaining_amount' => $village->target_amount,
            'total_users' => User::count(),
            'total_sppts' => 0,
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
