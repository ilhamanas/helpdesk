<?php

namespace Tests\Feature;

use App\Models\Rfm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardChartTest extends TestCase
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

    private function chartData(User $user, string $key): array
    {
        return $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->viewData($key);
    }

    public function test_status_chart_counts_each_status(): void
    {
        $staff = $this->makeUser('staff');
        $before = $this->chartData($staff, 'statusChart')['data'];

        $this->makeRfm($staff, ['status' => 'open']);
        $this->makeRfm($staff, ['status' => 'open']);
        $this->makeRfm($staff, ['status' => 'rejected']);

        $after = $this->chartData($staff, 'statusChart');

        $this->assertSame(
            ['Open', 'Assigned', 'In Progress', 'Resolved', 'Closed', 'Rejected'],
            $after['labels']
        );
        $this->assertSame($before[0] + 2, $after['data'][0]);
        $this->assertSame($before[5] + 1, $after['data'][5]);
    }

    public function test_category_chart_counts_each_category(): void
    {
        $staff = $this->makeUser('staff');
        $before = $this->chartData($staff, 'categoryChart')['data'];

        $this->makeRfm($staff, ['category' => 'Hardware']);
        $this->makeRfm($staff, ['category' => 'Lainnya']);

        $after = $this->chartData($staff, 'categoryChart');

        $this->assertSame(['Hardware', 'Software', 'Jaringan', 'Akun', 'Lainnya'], $after['labels']);
        $this->assertSame($before[0] + 1, $after['data'][0]);
        $this->assertSame($before[4] + 1, $after['data'][4]);
    }

    public function test_priority_chart_counts_each_priority(): void
    {
        $staff = $this->makeUser('staff');
        $before = $this->chartData($staff, 'priorityChart')['data'];

        $this->makeRfm($staff, ['priority' => 'low']);
        $this->makeRfm($staff, ['priority' => 'high']);

        $after = $this->chartData($staff, 'priorityChart');

        $this->assertSame(['Low', 'Medium', 'High'], $after['labels']);
        $this->assertSame($before[0] + 1, $after['data'][0]);
        $this->assertSame($before[2] + 1, $after['data'][2]);
    }

    public function test_trend_chart_has_30_days_and_counts_today(): void
    {
        $staff = $this->makeUser('staff');
        $before = $this->chartData($staff, 'trendChart');

        $this->assertCount(30, $before['labels']);
        $this->assertCount(30, $before['data']);

        $this->makeRfm($staff);

        $after = $this->chartData($staff, 'trendChart');

        $this->assertSame($before['data'][29] + 1, $after['data'][29]);
    }

    public function test_dashboard_renders_all_chart_canvases(): void
    {
        $staff = $this->makeUser('staff');

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Tiket per Status')
            ->assertSee('Tiket per Kategori')
            ->assertSee('Tiket per Priority')
            ->assertSee('Trend Tiket 30 Hari')
            ->assertSee('id="statusChart"', false)
            ->assertSee('id="categoryChart"', false)
            ->assertSee('id="priorityChart"', false)
            ->assertSee('id="trendChart"', false);
    }
}
