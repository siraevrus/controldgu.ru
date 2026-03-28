<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_cannot_open_map(): void
    {
        $this->get(route('map.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_map(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('map.index'))
            ->assertOk()
            ->assertSee('Карта ДГУ', false);
    }
}
