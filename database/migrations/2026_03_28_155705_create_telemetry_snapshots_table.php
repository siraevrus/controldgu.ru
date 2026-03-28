<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telemetry_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dgu_id')->constrained('dgus')->cascadeOnDelete();
            $table->timestamp('recorded_at');
            $table->json('values');
            $table->timestamps();

            $table->index(['dgu_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telemetry_snapshots');
    }
};
