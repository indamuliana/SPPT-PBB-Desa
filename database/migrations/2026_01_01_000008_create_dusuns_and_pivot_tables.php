<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Table Dusuns
        Schema::create('dusuns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 20)->nullable();
            $table->string('rw', 100)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Pivot Table Dusun - User (Kadus)
        Schema::create('dusun_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dusun_id')->constrained('dusuns')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['dusun_id', 'user_id']);
        });

        // 3. Add dusun_id to tax_objects
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->foreignId('dusun_id')->nullable()->after('rw')->constrained('dusuns')->nullOnDelete();
        });

        // 4. Data Backfilling: populate dusuns from existing tax_objects and users
        $existingDusuns = DB::table('tax_objects')
            ->whereNotNull('dusun')
            ->where('dusun', '!=', '')
            ->distinct()
            ->pluck('dusun');

        foreach ($existingDusuns as $dusunName) {
            $dusunName = trim($dusunName);
            if (! empty($dusunName)) {
                $dusunId = DB::table('dusuns')->insertGetId([
                    'name' => $dusunName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('tax_objects')
                    ->where('dusun', $dusunName)
                    ->update(['dusun_id' => $dusunId]);
            }
        }

        // Link existing kadus users by dusun_name
        $kaduses = DB::table('users')
            ->where('role', 'kadus')
            ->whereNotNull('dusun_name')
            ->where('dusun_name', '!=', '')
            ->get();

        foreach ($kaduses as $kadus) {
            $dusun = DB::table('dusuns')->where('name', trim($kadus->dusun_name))->first();
            if ($dusun) {
                DB::table('dusun_user')->insertOrIgnore([
                    'dusun_id' => $dusun->id,
                    'user_id' => $kadus->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('tax_objects', function (Blueprint $table) {
            $table->dropForeign(['dusun_id']);
            $table->dropColumn('dusun_id');
        });

        Schema::dropIfExists('dusun_user');
        Schema::dropIfExists('dusuns');
    }
};
