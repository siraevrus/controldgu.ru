<?php

namespace Tests\Feature;

use App\Models\Dgu;
use App\Support\TelemetryParameters;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelemetryIngestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_ingest_with_valid_token_creates_snapshot(): void
    {
        $dgu = Dgu::query()->firstOrFail();

        $response = $this->withToken('demo-ingest-token')->postJson("/api/v1/dgus/{$dgu->public_id}/telemetry", [
            'values' => [
                TelemetryParameters::POWER_CURRENT_KW => 80,
                TelemetryParameters::VOLTAGE_V => 400,
            ],
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('telemetry_snapshots', ['dgu_id' => $dgu->id]);
    }

    public function test_ingest_rejects_invalid_token(): void
    {
        $dgu = Dgu::query()->firstOrFail();

        $response = $this->withToken('wrong')->postJson("/api/v1/dgus/{$dgu->public_id}/telemetry", [
            'values' => [TelemetryParameters::VOLTAGE_V => 400],
        ]);

        $response->assertUnauthorized();
    }
}
