<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('to_cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number', 50)->unique();
            $table->foreignId('tax_object_id')->constrained('tax_objects')->cascadeOnDelete();
            $table->foreignId('sppt_id')->nullable()->constrained('sppts')->nullOnDelete();
            $table->foreignId('reported_by_user_id')->constrained('users');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users');
            $table->string('reason_type', 100);
            $table->text('description');
            $table->text('investigation_notes')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved'])->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sppt_mutations', function (Blueprint $table) {
            $table->id();
            $table->string('mutation_number', 50)->unique();
            $table->foreignId('tax_object_id')->constrained('tax_objects')->cascadeOnDelete();
            $table->foreignId('to_case_id')->nullable()->constrained('to_cases')->nullOnDelete();
            $table->string('mutation_type', 50)->default('balik_nama');
            $table->foreignId('old_taxpayer_id')->constrained('taxpayers');
            $table->foreignId('new_taxpayer_id')->constrained('taxpayers');
            $table->foreignId('requested_by_user_id')->constrained('users');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->string('legal_doc_type', 100);
            $table->string('legal_doc_number', 100)->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppt_mutations');
        Schema::dropIfExists('to_cases');
    }
};
