<?php

namespace Tests\Feature;

use App\Models\Rfm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RfmAccessTest extends TestCase
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

    public function test_staff_cannot_approve(): void
    {
        $staff = $this->makeUser('staff');
        $rfm = $this->makeRfm($staff);

        $this->actingAs($staff)
            ->post(route('rfm.approve', $rfm), ['assigned_to' => $staff->id])
            ->assertForbidden();
    }

    public function test_hrd_can_approve_and_assign_technician(): void
    {
        $staff = $this->makeUser('staff');
        $hrd = $this->makeUser('hrd');
        $tech = $this->makeUser('technician');
        $rfm = $this->makeRfm($staff);

        $this->actingAs($hrd)
            ->post(route('rfm.approve', $rfm), ['assigned_to' => $tech->id])
            ->assertRedirect(route('rfm.index'));

        $rfm->refresh();
        $this->assertSame('assigned', $rfm->status);
        $this->assertSame($tech->id, $rfm->assigned_to);
    }

    public function test_approved_technician_must_have_technician_role(): void
    {
        $staff = $this->makeUser('staff');
        $hrd = $this->makeUser('hrd');
        $otherStaff = $this->makeUser('staff');
        $rfm = $this->makeRfm($staff);

        $this->actingAs($hrd)
            ->post(route('rfm.approve', $rfm), ['assigned_to' => $otherStaff->id])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame('open', $rfm->fresh()->status);
    }

    public function test_staff_only_sees_own_tickets(): void
    {
        $staff = $this->makeUser('staff');
        $otherStaff = $this->makeUser('staff');
        $own = $this->makeRfm($staff, ['title' => 'Milik Sendiri XYZ']);
        $this->makeRfm($otherStaff, ['title' => 'Milik Orang Lain XYZ']);

        $this->actingAs($staff)
            ->get(route('rfm.index'))
            ->assertOk()
            ->assertSee($own->title)
            ->assertDontSee('Milik Orang Lain XYZ');
    }

    public function test_technician_cannot_start_ticket_assigned_to_other(): void
    {
        $staff = $this->makeUser('staff');
        $techA = $this->makeUser('technician');
        $techB = $this->makeUser('technician');
        $rfm = $this->makeRfm($staff, ['status' => 'assigned', 'assigned_to' => $techA->id]);

        $this->actingAs($techB)
            ->post(route('rfm.start', $rfm))
            ->assertForbidden();
    }

    public function test_technician_can_start_own_assigned_ticket(): void
    {
        $staff = $this->makeUser('staff');
        $tech = $this->makeUser('technician');
        $rfm = $this->makeRfm($staff, ['status' => 'assigned', 'assigned_to' => $tech->id]);

        $this->actingAs($tech)
            ->post(route('rfm.start', $rfm))
            ->assertRedirect(route('rfm.index'));

        $this->assertSame('in_progress', $rfm->fresh()->status);
    }

    public function test_staff_is_blocked_from_creating_ticket_while_pending_review(): void
    {
        $staff = $this->makeUser('staff');
        $this->makeRfm($staff, ['status' => 'resolved', 'resolved_at' => now()]);

        $this->actingAs($staff)
            ->post(route('rfm.store'), [
                'title' => 'Tiket Baru',
                'description' => 'Deskripsi',
                'category' => 'Hardware',
                'priority' => 'low',
            ])
            ->assertSessionHasErrors('title');

        $this->assertSame(1, Rfm::where('user_id', $staff->id)->count());
    }
}
