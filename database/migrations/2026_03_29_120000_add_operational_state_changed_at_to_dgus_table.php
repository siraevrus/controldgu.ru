<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dgus', function (Blueprint $table): void {
            $table->timestamp('operational_state_changed_at')->nullable()->after('operational_state');
        });

        DB::table('dgus')->whereNull('operational_state_changed_at')->update([
            'operational_state_changed_at' => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('dgus', function (Blueprint $table): void {
            $table->dropColumn('operational_state_changed_at');
        });
    }
};
