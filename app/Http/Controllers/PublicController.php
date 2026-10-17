<?php

namespace App\Http\Controllers;

use App\Models\Dusun;
use App\Models\Payment;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $village = Village::current();
        $activeYear = $village->active_tax_year ?? (int)date('Y');
        $targetAmount = (float)($village->target_amount ?? 0);

        // Agregat realisasi pembayaran sah tahun berjalan
        $realizationAmount = (float)Payment::where('status', 'valid')
            ->whereHas('sppt', fn($q) => $q->where('tax_year', $activeYear))
            ->sum('amount_principal');

        $realizationPercent = $targetAmount > 0 ? round(($realizationAmount / $targetAmount) * 100, 1) : 0;
        $totalPaidSppts = Sppt::where('tax_year', $activeYear)->where('status', 'paid')->count();
        $totalSppts = Sppt::where('tax_year', $activeYear)->count();
        $remainingTarget = max(0, $targetAmount - $realizationAmount);

        // Capaian transparansi per Dusun
        $dusunStats = Dusun::with('kadus')->orderBy('name')->get()->map(function ($dusun) use ($activeYear) {
            $sppts = Sppt::where('tax_year', $activeYear)
                ->whereHas('taxObject', fn($q) => $q->where('dusun_id', $dusun->id)->orWhere('dusun', $dusun->name))
                ->get();

            $target = (float)$sppts->sum('total_bill');
            $paid = (float)$sppts->sum('paid_amount');
            $percent = $target > 0 ? round(($paid / $target) * 100, 1) : 0;
            $kadusList = $dusun->kadus->pluck('name')->implode(', ');

            return [
                'name' => $dusun->name,
                'kadus' => $kadusList ?: 'Petugas Dusun',
                'target' => $target,
                'paid' => $paid,
                'percent' => $percent,
                'sppt_count' => $sppts->count(),
            ];
        });

        $stats = [
            'active_tax_year' => $activeYear,
            'target_amount' => $targetAmount,
            'realization_amount' => $realizationAmount,
            'remaining_target' => $remainingTarget,
            'realization_percent' => $realizationPercent,
            'total_paid_sppts' => $totalPaidSppts,
            'total_sppts' => $totalSppts,
        ];

        return view('public.home', compact('village', 'stats', 'dusunStats'));
    }

    public function checkNop(Request $request): JsonResponse
    {
        $rawNop = trim((string)$request->query('nop', ''));
        $cleanNop = preg_replace('/[^0-9]/', '', $rawNop);

        if (strlen($cleanNop) < 8) {
            return response()->json([
                'success' => false,
                'message' => 'Silakan masukkan minimal 8 digit angka NOP.',
            ], 422);
        }

        $village = Village::current();
        $activeYear = $village->active_tax_year ?? (int)date('Y');

        $taxObject = TaxObject::with(['taxpayer', 'dusunRelation.kadus'])
            ->where('nop', $cleanNop)
            ->orWhere('nop', $rawNop)
            ->first();

        if (! $taxObject) {
            return response()->json([
                'success' => false,
                'message' => "Objek Pajak dengan NOP tersebut tidak ditemukan di master data Desa {$village->name}. Mohon pastikan 18 digit nomor pada lembar SPPT Anda benar.",
            ], 404);
        }

        // Cari SPPT tahun aktif atau SPPT termutakhir
        $sppt = Sppt::where('tax_object_id', $taxObject->id)
            ->where('tax_year', $activeYear)
            ->first();

        if (! $sppt) {
            $sppt = Sppt::where('tax_object_id', $taxObject->id)
                ->orderByDesc('tax_year')
                ->first();
        }

        $kadusNames = $taxObject->dusunRelation?->kadus->pluck('name')->implode(', ') ?: 'Kolektor / Kasir Kantor Desa';

        return response()->json([
            'success' => true,
            'nop' => $taxObject->nop,
            'formatted_nop' => $taxObject->formatted_nop,
            'taxpayer_name' => $taxObject->taxpayer?->name ?? 'Tanpa Nama',
            'taxpayer_nik' => $taxObject->taxpayer?->nik ? substr($taxObject->taxpayer->nik, 0, 6) . '******' . substr($taxObject->taxpayer->nik, -4) : '-',
            'address' => $taxObject->formatted_address,
            'dusun' => $taxObject->dusunRelation?->name ?? $taxObject->dusun ?? '-',
            'kadus_name' => $kadusNames,
            'is_to_blocked' => (bool)($sppt?->is_to_blocked || $taxObject->isTo()),
            'is_inactive' => $taxObject->isInactive(),
            'tax_year' => $sppt?->tax_year ?? $activeYear,
            'total_bill' => $sppt ? (float)$sppt->total_bill : 0,
            'paid_amount' => $sppt ? (float)$sppt->paid_amount : 0,
            'remaining_bill' => $sppt ? (float)$sppt->remaining_bill : 0,
            'status' => $sppt?->status ?? 'unpaid',
            'status_label' => $sppt ? $sppt->status_label : 'Belum Lunas',
            'due_date' => $sppt?->due_date ? $sppt->due_date->format('d/m/Y') : '30/09/' . ($sppt?->tax_year ?? $activeYear),
        ]);
    }
}
