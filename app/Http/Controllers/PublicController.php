<?php

namespace App\Http\Controllers;

use App\Models\Village;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $village = Village::current();

        $stats = [
            'target_amount' => $village->target_amount,
            'realization_amount' => 0,
            'realization_percent' => 0,
            'total_paid_sppts' => 0,
            'active_tax_year' => $village->active_tax_year,
        ];

        return view('public.home', compact('village', 'stats'));
    }
}
