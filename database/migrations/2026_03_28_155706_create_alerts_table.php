<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dgu_id')->constrained('dgus')->cascadeOnDelete();
            $table->string('parameter_slug');
            $table->string('status', 32)->default('open');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('triggered_value')->nullable();
            $table->timestamp('triggered_at');
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('acknowledge_comment')->nullable();
            $table->timestamps();

            $table->index(['dgu_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
