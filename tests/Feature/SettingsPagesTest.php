<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_operator_cannot_open_audit(): void
    {
        $user = User::factory()->create();
        $user->assignRole('operator');
        $this->actingAs($user);

        $this->get(route('settings.audit.index'))->assertForbidden();
    }

    public function test_admin_can_open_audit_and_logs(): void
    {
        $admin = User::query()->where('email', 'admin@controldgu.local')->firstOrFail();
        $this->actingAs($admin);

        $this->get(route('settings.index'))->assertOk();
        $this->get(route('settings.audit.index'))->assertOk();
        $this->get(route('settings.logs.index'))->assertOk();
        $this->get(route('settings.users.index'))->assertOk();
    }

    public function test_user_can_open_notifications(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('notifications.index'))->assertOk();
    }
}
