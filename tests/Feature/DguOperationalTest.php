<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Dgu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DguOperationalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_change_operational_state(): void
    {
        $dgu = Dgu::query()->firstOrFail();

        $this->post(route('dgus.operational.update', $dgu), ['state' => 'stopped'])
            ->assertRedirect(route('login'));
    }

    public function test_user_without_role_cannot_change_operational_state(): void
    {
        $user = User::factory()->create();
        $dgu = Dgu::query()->firstOrFail();
        $this->actingAs($user);

        $this->post(route('dgus.operational.update', $dgu), ['state' => 'stopped'])
            ->assertForbidden();
    }

    public function test_admin_can_change_operational_state_and_writes_audit(): void
    {
        $admin = User::query()->where('email', 'admin@controldgu.local')->firstOrFail();
        $dgu = Dgu::query()->where('serial_number', 'AD100C-MSK-7VKM41')->firstOrFail();
        $this->assertSame('running', $dgu->operational_state);

        $this->actingAs($admin);

        $this->post(route('dgus.operational.update', $dgu), ['state' => 'stopped'])
            ->assertRedirect(route('dgus.show', $dgu));

        $dgu->refresh();
        $this->assertSame('stopped', $dgu->operational_state);

        $this->assertTrue(
            AuditLog::query()
                ->where('action', 'dgu.operational.stop')
                ->where('auditable_type', Dgu::class)
                ->where('auditable_id', $dgu->id)
                ->exists()
        );
    }

    public function test_operator_can_change_operational_state(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');
        $dgu = Dgu::query()->firstOrFail();
        $dgu->update(['operational_state' => 'stopped']);

        $this->actingAs($user);

        $this->post(route('dgus.operational.update', $dgu), ['state' => 'running'])
            ->assertRedirect(route('dgus.show', $dgu));

        $this->assertSame('running', $dgu->fresh()->operational_state);
    }
}
