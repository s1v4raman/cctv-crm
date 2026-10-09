<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Seed 3 executing companies specified in spec
        $now = now();
        DB::table('companies')->insert([
            ['name' => 'Precision IT Systems', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'NP Solutions', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Linepix', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
