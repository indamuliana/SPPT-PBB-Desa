<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Village;
use Illuminate\View\View;

class PublicVerificationController extends Controller
{
    public function verify(string $token): View
    {
        $payment = Payment::with(['sppt.taxObject.taxpayer', 'cashier'])
            ->where('verification_token', $token)
            ->first();

        $village = Village::current();

        if (! $payment) {
            return view('public.verification_not_found', compact('token', 'village'));
        }

        // Masking sensitive taxpayer personal data
        $rawWpName = $payment->sppt->taxObject->taxpayer?->name ?? 'Wajib Pajak';
        $maskedWpName = $this->maskName($rawWpName);

        $rawNop = $payment->sppt->taxObject->formatted_nop;
        $maskedNop = $this->maskNop($rawNop);

        return view('public.verification', compact('payment', 'village', 'maskedWpName', 'maskedNop'));
    }

    private function maskName(string $name): string
    {
        $words = explode(' ', trim($name));
        $maskedWords = [];

        foreach ($words as $w) {
            $len = strlen($w);
            if ($len <= 2) {
                $maskedWords[] = $w;
            } else {
                $maskedWords[] = substr($w, 0, 1) . str_repeat('*', $len - 1);
            }
        }

        return implode(' ', $maskedWords);
    }

    private function maskNop(string $nop): string
    {
        // Example: 32.12.050.001.001-0001.0 -> 32.12.050.***.***-****.*
        $parts = explode('.', $nop);
        if (count($parts) >= 4) {
            return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.***.***-****.*';
        }

        $len = strlen($nop);
        if ($len > 8) {
            return substr($nop, 0, 8) . str_repeat('*', $len - 8);
        }

        return $nop;
    }
}
