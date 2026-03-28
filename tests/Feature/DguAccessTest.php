<?php

namespace Tests\Feature;

use App\Models\Dgu;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DguAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_view_dgus(): void
    {
        $this->get(route('dgus.index'))->assertRedirect(route('login'));
    }

    public function test_non_admin_cannot_create_dgu(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dgus.create'))->assertForbidden();
    }

    public function test_admin_can_create_dgu(): void
    {
        $admin = User::query()->where('email', 'admin@controldgu.local')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('dgus.create'))->assertOk();
    }

    public function test_authenticated_user_can_view_dgu_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('dgus.index'))->assertOk();
    }

    public function test_non_admin_cannot_update_thresholds(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $threshold = \App\Models\GlobalThreshold::query()->firstOrFail();
        $this->get(route('settings.thresholds.edit', $threshold))->assertForbidden();
    }
}
