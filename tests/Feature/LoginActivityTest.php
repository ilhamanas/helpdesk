<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\Rfm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LoginActivityTest extends TestCase
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

    private function makeRfm(User $owner, array $attrs = []): Rfm
    {
        return Rfm::create(array_merge([
            'user_id' => $owner->id,
            'title' => 'Tiket ' . uniqid(),
            'description' => 'Deskripsi',
            'category' => 'Hardware',
            'priority' => 'medium',
            'status' => 'open',
        ], $attrs));
    }

    public function test_successful_login_creates_login_activity(): void
    {
        $user = $this->makeUser('staff');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect('dashboard');

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $user->id,
            'activity_type' => 'login',
        ]);
    }

    public function test_failed_login_does_not_create_activity(): void
    {
        $user = $this->makeUser('staff');

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'salah',
        ])->assertSessionHasErrors('email');

        $this->assertSame(0, DailyActivity::where('user_id', $user->id)->count());
    }

    public function test_logout_creates_logout_activity(): void
    {
        $user = $this->makeUser('staff');

        $this->actingAs($user)
            ->post(route('logout'))
            ->assertRedirect('/login');

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $user->id,
            'activity_type' => 'logout',
        ]);
    }

    public function test_dashboard_shows_login_and_logout_labels(): void
    {
        $user = $this->makeUser('staff');

        DailyActivity::create([
            'user_id' => $user->id,
            'activity_type' => 'login',
            'description' => 'Login ke sistem',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Aktivitas Terbaru')
            ->assertSee('Login ke sistem');
    }

    public function test_login_activity_is_visible_to_other_users(): void
    {
        $userA = $this->makeUser('staff');
        $userB = $this->makeUser('staff');

        DailyActivity::create([
            'user_id' => $userA->id,
            'activity_type' => 'login',
            'description' => 'Login ke sistem',
            'date' => now()->toDateString(),
        ]);

        $this->actingAs($userB)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee($userA->name);
    }

    public function test_staff_recent_activities_hide_other_users_tickets(): void
    {
        $staff = $this->makeUser('staff');
        $other = $this->makeUser('staff');

        $own = $this->makeRfm($staff, ['title' => 'Tiket Milik Sendiri']);
        $this->makeRfm($other, ['title' => 'Tiket Rahasia Orang Lain']);

        $activities = $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('activities');

        $this->assertTrue($activities->contains(
            fn ($activity) => str_contains($activity['ticket'], 'Tiket Milik Sendiri')
        ));
        $this->assertFalse($activities->contains(
            fn ($activity) => str_contains($activity['ticket'], 'Tiket Rahasia Orang Lain')
        ));
    }

    public function test_recent_activities_are_limited_to_five(): void
    {
        $staff = $this->makeUser('staff');

        for ($i = 1; $i <= 7; $i++) {
            $this->makeRfm($staff);
        }

        $activities = $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData('activities');

        $this->assertCount(5, $activities);
    }
}
