<?php

namespace App\Http\Controllers;

use App\Models\BankDeposit;
use App\Models\CashHandover;
use App\Models\Payment;
use App\Models\Sppt;
use App\Models\SpptMutation;
use App\Models\TaxObject;
use App\Models\ToCase;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        $village = Village::current();
        return view('reports.index', compact('village'));
    }

    /**
     * 1. Laporan Realisasi PBB-P2 per Dusun
     */
    public function realization(Request $request): View|StreamedResponse
    {
        $village = Village::current();
        $taxYear = (int)$request->query('tax_year', $village->active_tax_year);

        $dusunStats = TaxObject::select('tax_objects.dusun')
            ->selectRaw('COUNT(sppts.id) as total_sppts')
            ->selectRaw('COALESCE(SUM(sppts.total_bill), 0) as total_bill')
            ->selectRaw('COALESCE(SUM(sppts.paid_amount), 0) as paid_amount')
            ->selectRaw("SUM(CASE WHEN sppts.status = 'paid' THEN 1 ELSE 0 END) as count_paid")
            ->selectRaw("SUM(CASE WHEN sppts.status != 'paid' THEN 1 ELSE 0 END) as count_unpaid")
            ->join('sppts', function ($join) use ($taxYear) {
                $join->on('sppts.tax_object_id', '=', 'tax_objects.id')
                    ->where('sppts.tax_year', '=', $taxYear);
            })
            ->groupBy('tax_objects.dusun')
            ->orderBy('tax_objects.dusun')
            ->get()
            ->map(function ($row) {
                $bill = (float)$row->total_bill;
                $paid = (float)$row->paid_amount;
                $pct = $bill > 0 ? round(($paid / $bill) * 100, 2) : 0;
                return [
                    'dusun' => $row->dusun ?: 'Lainnya / Luar Dusun',
                    'total_sppts' => (int)$row->total_sppts,
                    'count_paid' => (int)$row->count_paid,
                    'count_unpaid' => (int)$row->count_unpaid,
                    'total_bill' => $bill,
                    'paid_amount' => $paid,
                    'remaining' => max(0, $bill - $paid),
                    'percent' => $pct,
                ];
            });

        $totals = [
            'total_sppts' => $dusunStats->sum('total_sppts'),
            'count_paid' => $dusunStats->sum('count_paid'),
            'count_unpaid' => $dusunStats->sum('count_unpaid'),
            'total_bill' => $dusunStats->sum('total_bill'),
            'paid_amount' => $dusunStats->sum('paid_amount'),
            'remaining' => $dusunStats->sum('remaining'),
            'percent' => $dusunStats->sum('total_bill') > 0 ? round(($dusunStats->sum('paid_amount') / $dusunStats->sum('total_bill')) * 100, 2) : 0,
        ];

        if ($request->query('export') === 'csv' || $request->query('export') === 'excel') {
            $filename = "Laporan_Realisasi_PBB_{$village->name}_{$taxYear}.csv";
            return response()->streamDownload(function () use ($dusunStats, $totals, $village, $taxYear) {
                $handle = fopen('php://output', 'w');
                // UTF-8 BOM for Excel
                fputs($handle, "\xEF\xBB\xBF");
                
                fputcsv($handle, ["LAPORAN REALISASI PENERIMAAN PBB-P2 PER WILAYAH DUSUN"]);
                fputcsv($handle, ["Desa {$village->name}, Kecamatan {$village->district_name}, Kabupaten {$village->regency_name}"]);
                fputcsv($handle, ["Tahun Pajak: {$taxYear}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['No', 'Wilayah Dusun', 'Jumlah SPPT', 'SPPT Lunas', 'SPPT Belum', 'Ketetapan Baku (Rp)', 'Realisasi Diterima (Rp)', 'Sisa Tunggakan (Rp)', 'Capaian (%)']);

                foreach ($dusunStats as $idx => $d) {
                    fputcsv($handle, [
                        $idx + 1,
                        $d['dusun'],
                        $d['total_sppts'],
                        $d['count_paid'],
                        $d['count_unpaid'],
                        $d['total_bill'],
                        $d['paid_amount'],
                        $d['remaining'],
                        $d['percent'] . '%',
                    ]);
                }

                fputcsv($handle, [
                    '',
                    'TOTAL KESELURUHAN',
                    $totals['total_sppts'],
                    $totals['count_paid'],
                    $totals['count_unpaid'],
                    $totals['total_bill'],
                    $totals['paid_amount'],
                    $totals['remaining'],
                    $totals['percent'] . '%',
                ]);

                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reports.realization', compact('village', 'taxYear', 'dusunStats', 'totals'));
    }

    /**
     * 2. Laporan Penerimaan Kuitansi Kasir
     */
    public function payments(Request $request): View|StreamedResponse
    {
        $village = Village::current();
        $startDate = $request->query('start_date', date('Y-m-01'));
        $endDate = $request->query('end_date', date('Y-m-d'));
        $cashierId = $request->query('cashier_id');

        $query = Payment::with(['sppt.taxObject.taxpayer', 'cashier'])
            ->where('status', 'valid')
            ->whereBetween('payment_date', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($cashierId) {
            $query->where('cashier_user_id', $cashierId);
        }

        $payments = $query->latest('payment_date')->get();
        $cashiers = User::whereIn('role', ['kadus', 'admin'])->get();

        $totalAmount = $payments->sum('total_amount');
        $totalPrincipal = $payments->sum('amount_principal');
        $totalFine = $payments->sum('amount_fine');

        if ($request->query('export') === 'csv' || $request->query('export') === 'excel') {
            $filename = "Laporan_Kuitansi_Penerimaan_{$startDate}_sd_{$endDate}.csv";
            return response()->streamDownload(function () use ($payments, $totalAmount, $totalPrincipal, $totalFine, $village, $startDate, $endDate) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");

                fputcsv($handle, ["LAPORAN TRANSAKSI PENERIMAAN KUITANSI PBB-P2"]);
                fputcsv($handle, ["Desa {$village->name} | Periode: {$startDate} s.d. {$endDate}"]);
                fputcsv($handle, []);
                fputcsv($handle, ['No', 'No. Kuitansi', 'Tanggal Bayar', 'NOP', 'Wajib Pajak', 'Dusun', 'Tahun', 'Kasir / Petugas', 'Pokok (Rp)', 'Denda (Rp)', 'Total Bayar (Rp)']);

                foreach ($payments as $idx => $p) {
                    fputcsv($handle, [
                        $idx + 1,
                        $p->receipt_number,
                        $p->payment_date->format('d/m/Y H:i'),
                        $p->sppt->taxObject->formatted_nop,
                        $p->sppt->taxObject->taxpayer->name,
                        $p->sppt->taxObject->dusun ?? '-',
                        $p->sppt->tax_year,
                        $p->cashier->name,
                        $p->amount_principal,
                        $p->amount_fine,
                        $p->total_amount,
                    ]);
                }

                fputcsv($handle, ['', '', '', '', '', '', '', 'TOTAL:', $totalPrincipal, $totalFine, $totalAmount]);
                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reports.payments', compact('village', 'payments', 'cashiers', 'startDate', 'endDate', 'cashierId', 'totalAmount', 'totalPrincipal', 'totalFine'));
    }

    /**
     * 3. Laporan Arus Kas & Setoran Bank
     */
    public function cashFlow(Request $request): View|StreamedResponse
    {
        $village = Village::current();

        $handovers = CashHandover::with(['cashier', 'collector', 'payments'])->latest('handover_date')->get();
        $deposits = BankDeposit::with(['collector', 'verifier'])->latest('deposit_date')->get();

        $totalHandoverFisik = $handovers->where('status', 'received')->sum('physical_amount');
        $totalHandoverSelisih = $handovers->sum('difference_amount');
        $totalBankDeposited = $deposits->where('status', 'approved')->sum('deposit_amount');

        if ($request->query('export') === 'csv' || $request->query('export') === 'excel') {
            $filename = "Laporan_Arus_Kas_dan_Setoran_Bank_{$village->name}.csv";
            return response()->streamDownload(function () use ($handovers, $deposits, $village) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");

                fputcsv($handle, ["REKAPITULASI SERAH TERIMA KAS KASIR -> KOLEKTOR"]);
                fputcsv($handle, ['No. BA', 'Waktu', 'Kasir Penyerah', 'Kolektor Penerima', 'Jml Trx', 'Total Sistem (Rp)', 'Fisik Diserahkan (Rp)', 'Selisih (Rp)', 'Status']);
                foreach ($handovers as $h) {
                    fputcsv($handle, [
                        $h->handover_number,
                        $h->handover_date->format('d/m/Y H:i'),
                        $h->cashier->name,
                        $h->collector->name,
                        $h->payments->count(),
                        $h->system_amount,
                        $h->physical_amount,
                        $h->difference_amount,
                        $h->status,
                    ]);
                }

                fputcsv($handle, []);
                fputcsv($handle, ["REKAPITULASI SETORAN BANK / BAPENDA"]);
                fputcsv($handle, ['No. Setoran', 'Tanggal Setor', 'Kolektor', 'Bank / Tujuan', 'No. Ref Bank', 'Nominal (Rp)', 'Status', 'Pengesah']);
                foreach ($deposits as $d) {
                    fputcsv($handle, [
                        $d->deposit_number,
                        $d->deposit_date->format('d/m/Y'),
                        $d->collector->name,
                        $d->destination_bank,
                        $d->bank_ref_number ?? '-',
                        $d->deposit_amount,
                        $d->status,
                        $d->verifier ? $d->verifier->name : '-',
                    ]);
                }

                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reports.cash-flow', compact('village', 'handovers', 'deposits', 'totalHandoverFisik', 'totalHandoverSelisih', 'totalBankDeposited'));
    }

    /**
     * 4. Laporan Kasus TO & Mutasi Objek Pajak
     */
    public function toAndMutations(Request $request): View|StreamedResponse
    {
        $village = Village::current();

        $toCases = ToCase::with(['taxObject.taxpayer', 'reportedBy', 'assignedTo'])->latest()->get();
        $mutations = SpptMutation::with(['taxObject', 'oldTaxpayer', 'newTaxpayer', 'requestedBy', 'approvedBy'])->latest()->get();

        if ($request->query('export') === 'csv' || $request->query('export') === 'excel') {
            $filename = "Laporan_TO_dan_Mutasi_{$village->name}.csv";
            return response()->streamDownload(function () use ($toCases, $mutations, $village) {
                $handle = fopen('php://output', 'w');
                fputs($handle, "\xEF\xBB\xBF");

                fputcsv($handle, ["DAFTAR KASUS TO (TANPA OBJEK / PEMILIK BELUM JELAS)"]);
                fputcsv($handle, ['No. Kasus', 'NOP', 'Subjek Tercatat', 'Letak Objek', 'Kategori Kendala', 'Status', 'Pelapor', 'Petugas Lapangan']);
                foreach ($toCases as $tc) {
                    fputcsv($handle, [
                        $tc->case_number,
                        $tc->taxObject->formatted_nop,
                        $tc->taxObject->taxpayer->name,
                        $tc->taxObject->formatted_address,
                        $tc->reason_label,
                        $tc->status,
                        $tc->reportedBy->name,
                        $tc->assignedTo ? $tc->assignedTo->name : '-',
                    ]);
                }

                fputcsv($handle, []);
                fputcsv($handle, ["DAFTAR MUTASI & BALIK NAMA OBJEK PAJAK"]);
                fputcsv($handle, ['No. Mutasi', 'Tanggal', 'NOP', 'Jenis Mutasi', 'WP Asal', 'WP Baru', 'Dasar Dokumen', 'Status', 'Pengesah']);
                foreach ($mutations as $m) {
                    fputcsv($handle, [
                        $m->mutation_number,
                        $m->created_at->format('d/m/Y'),
                        $m->taxObject->formatted_nop,
                        $m->type_label,
                        $m->oldTaxpayer->name,
                        $m->newTaxpayer->name,
                        $m->legal_doc_type . ' ' . ($m->legal_doc_number ?? ''),
                        $m->status,
                        $m->approvedBy ? $m->approvedBy->name : '-',
                    ]);
                }

                fclose($handle);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('reports.to-and-mutations', compact('village', 'toCases', 'mutations'));
    }
}
