<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('global_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('parameter_slug')->unique();
            $table->decimal('min_value', 16, 6)->nullable();
            $table->decimal('max_value', 16, 6)->nullable();
            $table->boolean('ignore_max')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_thresholds');
    }
};
