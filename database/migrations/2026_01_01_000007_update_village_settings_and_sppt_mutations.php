<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villages', function (Blueprint $table) {
            $table->decimal('tax_rate_percent', 5, 3)->default(0.100)->after('target_amount');
        });

        Schema::table('sppt_mutations', function (Blueprint $table) {
            $table->string('split_component_type', 50)->nullable()->after('mutation_type');
            $table->decimal('split_area_m2', 12, 2)->nullable()->after('split_component_type');
            $table->decimal('split_njop_per_m2', 15, 2)->nullable()->after('split_area_m2');
            $table->foreignId('target_tax_object_id')->nullable()->after('new_taxpayer_id')->constrained('tax_objects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sppt_mutations', function (Blueprint $table) {
            $table->dropForeign(['target_tax_object_id']);
            $table->dropColumn(['split_component_type', 'split_area_m2', 'split_njop_per_m2', 'target_tax_object_id']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn('tax_rate_percent');
        });
    }
};
