<?php

namespace App\Http\Controllers;

use App\Models\Dusun;
use App\Models\User;
use App\Models\Village;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class GuideController extends Controller
{
    /**
     * Tampilkan halaman panduan aplikasi dan alur peran interaktif.
     */
    public function index(Request $request): View
    {
        $village = Village::current();
        $dusuns = Dusun::orderBy('name')->get();
        $userRole = auth()->user()->role ?? 'admin';

        $roleStats = [
            'admin' => User::where('role', 'admin')->where('is_active', true)->count(),
            'kadus' => User::where('role', 'kadus')->where('is_active', true)->count(),
            'kolektor' => User::where('role', 'kolektor')->where('is_active', true)->count(),
            'bendahara' => User::where('role', 'bendahara')->where('is_active', true)->count(),
            'kades' => User::where('role', 'kades')->where('is_active', true)->count(),
        ];

        return view('guide.index', compact('village', 'dusuns', 'userRole', 'roleStats'));
    }

    /**
     * Generate dan unduh dokumen Panduan PDF Resmi.
     */
    public function downloadPdf(Request $request): Response
    {
        $village = Village::current();
        $dusuns = Dusun::orderBy('name')->get();

        $pdf = Pdf::loadView('guide.pdf', compact('village', 'dusuns'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 120,
            ]);

        $villageClean = preg_replace('/[^A-Za-z0-9]/', '_', $village->name);
        $fileName = "Buku_Panduan_SADAYA_PBB_{$villageClean}_{$village->active_tax_year}.pdf";

        return $pdf->download($fileName);
    }
}
