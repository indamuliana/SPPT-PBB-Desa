<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            // Expand status column or change to string/enum to include inactive
            // In MySQL, modifying an enum requires raw query or string
            $table->string('status', 20)->default('normal')->change();
            $table->string('deactivation_reason', 50)->nullable()->after('status');
            $table->text('deactivation_notes')->nullable()->after('deactivation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivation_notes']);
        });
    }
};
