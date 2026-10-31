<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role): User
    {
        Role::firstOrCreate(['code' => $role], ['name' => ucfirst($role), 'description' => null]);

        return User::create([
            'name' => ucfirst($role) . ' Test',
            'email' => $role . '-' . uniqid() . '@test.local',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    public function test_non_admin_cannot_access_settings(): void
    {
        foreach (['staff', 'hrd', 'technician'] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)->get(route('settings.users.index'))->assertForbidden();
            $this->actingAs($user)->get(route('settings.roles.index'))->assertForbidden();
        }
    }

    public function test_admin_can_access_settings(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get(route('settings.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('settings.roles.index'))->assertOk();
    }

    public function test_settings_menu_hidden_for_non_admin(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Master User')
            ->assertDontSee('Hak Akses');
    }
}
