<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Sppt;
use App\Models\TaxObject;
use App\Models\ToCase;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ToCaseController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $query = ToCase::with(['taxObject.taxpayer', 'reportedBy', 'assignedTo']);

        if ($status && in_array($status, ['open', 'investigating', 'resolved'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('case_number', 'like', "%{$search}%")
                  ->orWhereHas('taxObject', function ($sub) use ($search) {
                      $sub->where('nop', 'like', "%{$search}%")
                          ->orWhere('address', 'like', "%{$search}%");
                  });
            });
        }

        $cases = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'total' => ToCase::count(),
            'open' => ToCase::where('status', 'open')->count(),
            'investigating' => ToCase::where('status', 'investigating')->count(),
            'resolved' => ToCase::where('status', 'resolved')->count(),
        ];

        return view('to-cases.index', compact('cases', 'counts', 'status', 'search'));
    }

    public function create(Request $request): View
    {
        $taxObjectId = $request->query('tax_object_id');
        $spptId = $request->query('sppt_id');

        $selectedObject = $taxObjectId ? TaxObject::with('taxpayer')->find($taxObjectId) : null;
        $selectedSppt = $spptId ? Sppt::find($spptId) : null;

        $collectors = User::where('role', 'kolektor')->where('is_active', true)->get();

        return view('to-cases.create', compact('selectedObject', 'selectedSppt', 'collectors'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nop' => ['required', 'string'],
            'sppt_id' => ['nullable', 'exists:sppts,id'],
            'reason_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $rawNop = preg_replace('/[^0-9]/', '', $validated['nop']);
        $taxObject = TaxObject::where('nop', $rawNop)->orWhere('nop', $validated['nop'])->first();

        if (! $taxObject) {
            return back()->withInput()->with('error', 'Objek Pajak dengan NOP tersebut tidak ditemukan di master data desa.');
        }

        $case = DB::transaction(function () use ($validated, $taxObject) {
            $case = ToCase::create([
                'case_number' => ToCase::generateCaseNumber(),
                'tax_object_id' => $taxObject->id,
                'sppt_id' => $validated['sppt_id'] ?? null,
                'reported_by_user_id' => auth()->id(),
                'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
                'reason_type' => $validated['reason_type'],
                'description' => $validated['description'],
                'status' => 'open',
            ]);

            // Set Tax Object to TO
            $taxObject->update(['status' => 'to']);

            // Lock SPPTs from receiving payments
            $spptQuery = Sppt::where('tax_object_id', $taxObject->id);
            if (! empty($validated['sppt_id'])) {
                $spptQuery->where('id', $validated['sppt_id']);
            }
            $spptQuery->update([
                'is_to_blocked' => true,
            ]);

            AuditLog::log(
                'to_case',
                "NOP {$taxObject->formatted_nop} ditandai sebagai TO (Kasus #{$case->case_number}). Alasan: {$case->reason_label}",
                $case
            );

            return $case;
        });

        return redirect()->route('to-cases.show', $case)->with('success', 'Kasus TO berhasil dicatat. NOP dan SPPT terkait telah dikunci dari transaksi pembayaran.');
    }

    public function show(ToCase $toCase): View
    {
        $toCase->load(['taxObject.taxpayer', 'sppt', 'reportedBy', 'assignedTo', 'mutations.newTaxpayer']);
        $collectors = User::where('role', 'kolektor')->where('is_active', true)->get();

        return view('to-cases.show', compact('toCase', 'collectors'));
    }

    public function updateInvestigation(ToCase $toCase, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,investigating,resolved'],
            'new_notes' => ['required', 'string'],
            'assigned_to_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $timestamp = now()->format('d/m/Y H:i');
        $user = auth()->user()->name;
        $appendedNotes = trim($toCase->investigation_notes . "\n\n[" . $timestamp . " oleh " . $user . "]:\n" . $validated['new_notes']);

        $updates = [
            'status' => $validated['status'],
            'investigation_notes' => $appendedNotes,
        ];

        if (! empty($validated['assigned_to_user_id'])) {
            $updates['assigned_to_user_id'] = $validated['assigned_to_user_id'];
        }

        if ($validated['status'] === 'resolved' && ! $toCase->resolved_at) {
            $updates['resolved_at'] = now();
        }

        $toCase->update($updates);

        AuditLog::log(
            'to_case',
            "Memperbarui investigasi kasus TO #{$toCase->case_number}. Status: {$toCase->status}",
            $toCase
        );

        return back()->with('success', 'Catatan investigasi kasus TO berhasil diperbarui.');
    }
}
