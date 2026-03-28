<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dgus', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name')->nullable();
            $table->string('serial_number')->unique();
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('responsible_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->decimal('nominal_power_kw', 8, 2)->nullable();
            $table->string('model_name')->nullable();
            $table->string('region')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_manually_disabled')->default(false);
            $table->string('operational_state', 32)->default('stopped');
            $table->string('telemetry_token_hash', 64);
            $table->timestamp('last_telemetry_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dgus');
    }
};
