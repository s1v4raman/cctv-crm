<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('site_code')->unique();
            $table->string('name');
            $table->string('client_name')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('alt_phone', 50)->nullable();
            $table->string('client_phone', 50)->nullable();
            $table->string('client_email', 150)->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->string('email')->nullable();
            $table->text('address');
            $table->string('area')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 20)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_maps_link', 500)->nullable();
            $table->string('google_maps_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
