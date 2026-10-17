<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Deactivate legacy admin and bendahara accounts
        User::whereIn('username', ['admin', 'bendahara'])->update([
            'is_active' => false,
        ]);

        // 2. Ensure kolektor user is active and primary
        User::where('username', 'kolektor')->update([
            'role' => 'kolektor',
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        User::whereIn('username', ['admin', 'bendahara'])->update([
            'is_active' => true,
        ]);
    }
};
