<?php

namespace Tests\Feature;

use App\Models\DailyActivity;
use App\Models\Rfm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DailyActivityTest extends TestCase
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

    private function makeActivity(User $user, array $attrs = []): DailyActivity
    {
        return DailyActivity::create(array_merge([
            'user_id' => $user->id,
            'activity_type' => 'Perbaikan',
            'description' => 'Deskripsi ' . uniqid(),
            'date' => now()->toDateString(),
        ], $attrs));
    }

    private function payload(array $attrs = []): array
    {
        return array_merge([
            'date' => now()->toDateString(),
            'activity_type' => 'Perbaikan printer',
            'description' => 'Membersihkan roller printer',
            'duration_minutes' => 90,
        ], $attrs);
    }

    public function test_technician_can_create_activity(): void
    {
        $staff = $this->makeUser('staff');
        $tech = $this->makeUser('technician');
        $rfm = $this->makeRfm($staff, ['assigned_to' => $tech->id]);

        $this->actingAs($tech)
            ->post(route('daily-activity.store'), $this->payload(['rfm_id' => $rfm->id]))
            ->assertRedirect(route('daily-activity.index'));

        $this->assertDatabaseHas('daily_activities', [
            'user_id' => $tech->id,
            'rfm_id' => $rfm->id,
            'activity_type' => 'Perbaikan printer',
            'duration_minutes' => 90,
        ]);
    }

    public function test_only_technician_can_create_activity(): void
    {
        foreach (['staff', 'hrd', 'admin'] as $role) {
            $user = $this->makeUser($role);

            $this->actingAs($user)
                ->post(route('daily-activity.store'), $this->payload())
                ->assertForbidden();
        }
    }

    public function test_staff_cannot_open_daily_activity_page(): void
    {
        $this->actingAs($this->makeUser('staff'))
            ->get(route('daily-activity.index'))
            ->assertForbidden();
    }

    public function test_hrd_and_admin_can_open_daily_activity_page(): void
    {
        foreach (['hrd', 'admin'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->get(route('daily-activity.index'))
                ->assertOk();
        }
    }

    public function test_technician_only_sees_own_activities(): void
    {
        $techA = $this->makeUser('technician');
        $techB = $this->makeUser('technician');

        $own = $this->makeActivity($techA, ['description' => 'Aktivitas Milik A']);
        $this->makeActivity($techB, ['description' => 'Aktivitas Milik B']);

        $this->actingAs($techA)
            ->get(route('daily-activity.index'))
            ->assertOk()
            ->assertSee('Aktivitas Milik A')
            ->assertDontSee('Aktivitas Milik B');

        $this->assertSame($own->id, DailyActivity::where('description', 'Aktivitas Milik A')->value('id'));
    }

    public function test_hrd_sees_all_activities(): void
    {
        $techA = $this->makeUser('technician');
        $techB = $this->makeUser('technician');
        $hrd = $this->makeUser('hrd');

        $this->makeActivity($techA, ['description' => 'Aktivitas Teknisi A']);
        $this->makeActivity($techB, ['description' => 'Aktivitas Teknisi B']);

        $this->actingAs($hrd)
            ->get(route('daily-activity.index'))
            ->assertOk()
            ->assertSee('Aktivitas Teknisi A')
            ->assertSee('Aktivitas Teknisi B');
    }

    public function test_login_and_logout_rows_are_hidden(): void
    {
        $tech = $this->makeUser('technician');
        $login = $this->makeActivity($tech, ['activity_type' => 'login']);
        $logout = $this->makeActivity($tech, ['activity_type' => 'logout']);
        $work = $this->makeActivity($tech);

        $response = $this->actingAs($tech)
            ->get(route('daily-activity.index'))
            ->assertOk();

        $ids = $response->viewData('activities')->pluck('id');

        $this->assertFalse($ids->contains($login->id));
        $this->assertFalse($ids->contains($logout->id));
        $this->assertTrue($ids->contains($work->id));
    }

    public function test_activity_validation_rejects_invalid_input(): void
    {
        $tech = $this->makeUser('technician');

        $this->actingAs($tech)
            ->post(route('daily-activity.store'), [
                'date' => '',
                'activity_type' => 'login',
                'description' => '',
                'duration_minutes' => 1441,
            ])
            ->assertSessionHasErrors(['date', 'activity_type', 'description', 'duration_minutes']);
    }

    public function test_rfm_must_be_assigned_to_the_technician(): void
    {
        $staff = $this->makeUser('staff');
        $techA = $this->makeUser('technician');
        $techB = $this->makeUser('technician');
        $rfm = $this->makeRfm($staff, ['assigned_to' => $techB->id]);

        $this->actingAs($techA)
            ->post(route('daily-activity.store'), $this->payload(['rfm_id' => $rfm->id]))
            ->assertSessionHasErrors('rfm_id');

        $this->assertSame(0, DailyActivity::where('user_id', $techA->id)->count());
    }

    public function test_technician_can_update_and_delete_own_activity(): void
    {
        $tech = $this->makeUser('technician');
        $activity = $this->makeActivity($tech);

        $this->actingAs($tech)
            ->put(route('daily-activity.update', $activity), $this->payload([
                'activity_type' => 'Perawatan rutin',
            ]))
            ->assertRedirect(route('daily-activity.index'));

        $this->assertSame('Perawatan rutin', $activity->fresh()->activity_type);

        $this->actingAs($tech)
            ->delete(route('daily-activity.destroy', $activity))
            ->assertRedirect(route('daily-activity.index'));

        $this->assertNull($activity->fresh());
    }

    public function test_technician_cannot_edit_or_delete_other_technician_activity(): void
    {
        $techA = $this->makeUser('technician');
        $techB = $this->makeUser('technician');
        $activity = $this->makeActivity($techB);

        $this->actingAs($techA)
            ->put(route('daily-activity.update', $activity), $this->payload())
            ->assertForbidden();

        $this->actingAs($techA)
            ->delete(route('daily-activity.destroy', $activity))
            ->assertForbidden();
    }

    public function test_admin_and_hrd_cannot_edit_or_delete_activity(): void
    {
        $tech = $this->makeUser('technician');
        $activity = $this->makeActivity($tech);

        foreach (['admin', 'hrd'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->put(route('daily-activity.update', $activity), $this->payload())
                ->assertForbidden();

            $this->actingAs($this->makeUser($role))
                ->delete(route('daily-activity.destroy', $activity))
                ->assertForbidden();
        }

        $this->assertNotNull($activity->fresh());
    }

    public function test_login_rows_cannot_be_edited_or_deleted(): void
    {
        $tech = $this->makeUser('technician');
        $login = $this->makeActivity($tech, ['activity_type' => 'login']);

        $this->actingAs($tech)
            ->put(route('daily-activity.update', $login), $this->payload())
            ->assertNotFound();

        $this->actingAs($tech)
            ->delete(route('daily-activity.destroy', $login))
            ->assertNotFound();

        $this->assertNotNull($login->fresh());
    }

    public function test_menu_is_hidden_for_staff_and_visible_for_technician(): void
    {
        $this->actingAs($this->makeUser('staff'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Daily Activity');

        $this->actingAs($this->makeUser('technician'))
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Daily Activity');
    }
}
