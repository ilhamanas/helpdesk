<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function makeUser(string $role): User
    {
        Role::firstOrCreate(['code' => $role], ['name' => ucfirst($role), 'description' => null]);

        return User::create([
            'name' => ucfirst($role) . ' Test ' . uniqid(),
            'email' => $role . '-' . uniqid() . '@test.local',
            'password' => bcrypt('password'),
            'role' => $role,
        ]);
    }

    public function test_staff_cannot_access_report(): void
    {
        $this->actingAs($this->makeUser('staff'))
            ->get(route('report.index'))
            ->assertForbidden();
    }

    public function test_technician_cannot_access_report(): void
    {
        $this->actingAs($this->makeUser('technician'))
            ->get(route('report.index'))
            ->assertForbidden();
    }

    public function test_hrd_can_access_report(): void
    {
        $this->actingAs($this->makeUser('hrd'))
            ->get(route('report.index'))
            ->assertOk();
    }

    public function test_admin_can_access_report(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get(route('report.index'))
            ->assertOk();
    }

    public function test_report_menu_hidden_for_staff_and_technician(): void
    {
        foreach (['staff', 'technician'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertDontSee('route(\'report.index\')');
        }
    }

    public function test_report_menu_visible_for_hrd_and_admin(): void
    {
        foreach (['hrd', 'admin'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Report');
        }
    }
}
