<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sppt;
use App\Models\SpptMutation;
use App\Models\TaxObject;
use App\Models\TaxObjectComponent;
use App\Models\Taxpayer;
use App\Models\ToCase;
use App\Models\Village;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SpptMutationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $query = SpptMutation::with(['taxObject.taxpayer', 'oldTaxpayer', 'newTaxpayer', 'requestedBy', 'approvedBy']);

        if ($status && in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('mutation_number', 'like', "%{$search}%")
                  ->orWhereHas('taxObject', function ($sub) use ($search) {
                      $sub->where('nop', 'like', "%{$search}%");
                  })
                  ->orWhereHas('newTaxpayer', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $mutations = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'total' => SpptMutation::count(),
            'pending' => SpptMutation::where('status', 'pending')->count(),
            'approved' => SpptMutation::where('status', 'approved')->count(),
            'rejected' => SpptMutation::where('status', 'rejected')->count(),
        ];

        return view('mutations.index', compact('mutations', 'counts', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        $taxObjectId = $request->query('tax_object_id');
        $toCaseId = $request->query('to_case_id');

        $selectedObject = $taxObjectId ? TaxObject::with('taxpayer')->find($taxObjectId) : null;
        $selectedToCase = $toCaseId ? ToCase::with(['taxObject.taxpayer'])->find($toCaseId) : null;

        if ($selectedToCase && ! $selectedObject) {
            $selectedObject = $selectedToCase->taxObject;
        }

        $taxpayers = Taxpayer::orderBy('name')->get();

        return view('mutations.create', compact('selectedObject', 'selectedToCase', 'taxpayers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nop' => ['required', 'string'],
            'mutation_type' => ['required', 'in:balik_nama,pemecahan,penggabungan,koreksi_data'],
            'new_taxpayer_id' => ['required', 'exists:taxpayers,id'],
            'legal_doc_type' => ['required', 'string', 'max:100'],
            'legal_doc_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string'],
            'to_case_id' => ['nullable', 'exists:to_cases,id'],
        ]);

        $rawNop = preg_replace('/[^0-9]/', '', $validated['nop']);
        $taxObject = TaxObject::with('taxpayer')->where('nop', $rawNop)->orWhere('nop', $validated['nop'])->first();

        if (! $taxObject) {
            return back()->withInput()->with('error', 'Objek Pajak dengan NOP tersebut tidak ditemukan di master data desa.');
        }

        if ($taxObject->taxpayer_id == $validated['new_taxpayer_id']) {
            return back()->withInput()->with('error', 'Wajib Pajak baru harus berbeda dari Wajib Pajak lama.');
        }

        $mutation = SpptMutation::create([
            'mutation_number' => SpptMutation::generateMutationNumber(),
            'tax_object_id' => $taxObject->id,
            'to_case_id' => $validated['to_case_id'] ?? null,
            'mutation_type' => $validated['mutation_type'],
            'old_taxpayer_id' => $taxObject->taxpayer_id,
            'new_taxpayer_id' => $validated['new_taxpayer_id'],
            'requested_by_user_id' => auth()->id(),
            'legal_doc_type' => $validated['legal_doc_type'],
            'legal_doc_number' => $validated['legal_doc_number'] ?? null,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLog::log(
            'mutation',
            "Pengajuan mutasi {$mutation->type_label} #{$mutation->mutation_number} untuk NOP {$taxObject->formatted_nop}",
            $mutation
        );

        return redirect()->route('mutations.show', $mutation)->with('success', 'Pengajuan mutasi berhasil didaftarkan dan menunggu pengesahan Administrator Desa.');
    }

    public function createSplitComponent(Request $request): View
    {
        $taxObjectId = $request->query('tax_object_id');
        $selectedObject = $taxObjectId ? TaxObject::with(['taxpayer', 'components'])->find($taxObjectId) : null;
        $taxObjects = TaxObject::with(['taxpayer', 'components'])->orderBy('nop')->get();
        $taxpayers = Taxpayer::orderBy('name')->get();

        return view('mutations.split-component', compact('selectedObject', 'taxObjects', 'taxpayers'));
    }

    public function storeSplitComponent(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tax_object_id' => ['required', 'exists:tax_objects,id'],
            'split_component_type' => ['required', 'in:bumi,bangunan,bumi_bersama,bangunan_bersama'],
            'split_area_m2' => ['required', 'numeric', 'min:0.01'],
            'new_taxpayer_id' => ['required', 'exists:taxpayers,id'],
            'target_tax_object_id' => ['nullable', 'exists:tax_objects,id'],
            'legal_doc_type' => ['required', 'string', 'max:100'],
            'legal_doc_number' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string'],
        ]);

        $taxObject = TaxObject::with(['taxpayer', 'components'])->findOrFail($validated['tax_object_id']);

        $component = $taxObject->components->where('type', $validated['split_component_type'])->first();
        if (! $component) {
            return back()->withInput()->with('error', "Komponen objek " . strtoupper(str_replace('_', ' ', $validated['split_component_type'])) . " tidak ditemukan pada NOP ini.");
        }

        if ((float)$validated['split_area_m2'] > (float)$component->area_m2) {
            return back()->withInput()->with('error', "Luas yang dialihkan ({$validated['split_area_m2']} m²) tidak boleh melebihi luas komponen saat ini ({$component->area_m2} m²).");
        }

        $mutation = SpptMutation::create([
            'mutation_number' => SpptMutation::generateMutationNumber(),
            'tax_object_id' => $taxObject->id,
            'mutation_type' => 'pemecahan',
            'split_component_type' => $validated['split_component_type'],
            'split_area_m2' => $validated['split_area_m2'],
            'split_njop_per_m2' => $component->njop_per_m2,
            'old_taxpayer_id' => $taxObject->taxpayer_id,
            'new_taxpayer_id' => $validated['new_taxpayer_id'],
            'target_tax_object_id' => $validated['target_tax_object_id'] ?? null,
            'requested_by_user_id' => auth()->id(),
            'legal_doc_type' => $validated['legal_doc_type'],
            'legal_doc_number' => $validated['legal_doc_number'] ?? null,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditLog::log(
            'mutation',
            "Pengajuan pecah komponen {$validated['split_component_type']} ({$validated['split_area_m2']} m²) #{$mutation->mutation_number} untuk NOP {$taxObject->formatted_nop}",
            $mutation
        );

        return redirect()->route('mutations.show', $mutation)->with('success', 'Pengajuan pemecahan komponen berhasil dibuat dan menunggu persetujuan Administrator Desa.');
    }

    public function show(SpptMutation $mutation): View
    {
        $mutation->load(['taxObject.components', 'targetTaxObject.components', 'oldTaxpayer', 'newTaxpayer', 'requestedBy', 'approvedBy', 'toCase']);
        $village = Village::current();

        return view('mutations.show', compact('mutation', 'village'));
    }

    public function approve(SpptMutation $mutation): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Administrator Desa yang berwenang mengesahkan mutasi objek pajak.');
        }

        if ($mutation->isApproved()) {
            return back()->with('error', 'Mutasi ini sudah pernah disetujui sebelumnya.');
        }

        DB::transaction(function () use ($mutation) {
            $mutation->load(['taxObject', 'newTaxpayer']);
            $taxObject = $mutation->taxObject;

            // 1. Update mutation record as approved by Village for Bapenda report
            $mutation->update([
                'status' => 'approved',
                'approved_by_user_id' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Note: Per village authority rules, mutation is an administrative recording & reporting
            // to Bapenda Kabupaten. Village does not alter the current active year's Taxpayer on Tax Object
            // or DHKP/SPPT. Official changes will take effect in the next tax year when Bapenda issues new SPPT.

            // 2. Unblock all SPPTs under this Tax Object if previously TO-blocked so tax can be paid
            if ($taxObject->isTo()) {
                $taxObject->update(['status' => 'normal']);
            }

            $sppts = Sppt::where('tax_object_id', $taxObject->id)->get();
            foreach ($sppts as $sppt) {
                if ($sppt->is_to_blocked) {
                    $sppt->is_to_blocked = false;
                    $sppt->recalculateStatus();
                    $sppt->save();
                }
            }

            // 3. Resolve TO Case if linked
            if ($mutation->to_case_id && $mutation->toCase) {
                $mutation->toCase->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'investigation_notes' => trim($mutation->toCase->investigation_notes . "\n\n[" . now()->format('d/m/Y H:i') . "]: Diselesaikan dan dicatat berkas mutasi #{$mutation->mutation_number} kepada {$mutation->newTaxpayer->name} untuk diteruskan ke Bapenda Kabupaten."),
                ]);
            }

            AuditLog::log(
                'mutation',
                "Admin mengesahkan dan mencatat berkas mutasi {$mutation->mutation_type} #{$mutation->mutation_number}: NOP {$taxObject->formatted_nop}",
                $mutation
            );
        });

        return back()->with('success', "Mutasi #{$mutation->mutation_number} berhasil disahkan dan dicatat dalam register desa untuk pelaporan ke Bapenda Kabupaten. Ketetapan SPPT tahun berjalan tetap berlaku atas nama WP lama sampai terbit SPPT penetapan baru tahun depan.");
    }

    public function reject(SpptMutation $mutation, Request $request): RedirectResponse
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Hanya Administrator Desa yang berwenang menolak mutasi.');
        }

        $validated = $request->validate([
            'rejection_notes' => ['required', 'string', 'max:500'],
        ]);

        $mutation->update([
            'status' => 'rejected',
            'approved_by_user_id' => auth()->id(),
            'rejection_notes' => $validated['rejection_notes'],
        ]);

        AuditLog::log(
            'mutation',
            "Admin menolak pengajuan mutasi #{$mutation->mutation_number}. Alasan: {$validated['rejection_notes']}",
            $mutation
        );

        return back()->with('success', "Pengajuan mutasi #{$mutation->mutation_number} telah ditolak.");
    }
}
