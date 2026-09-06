<?php

namespace Tests\Feature\Admin;

use App\Models\JobCategory;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    private function hirer(): User
    {
        return User::factory()->hirer()->create();
    }

    private function category(): JobCategory
    {
        return JobCategory::create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
    }

    private function categoryDesign(): JobCategory
    {
        return JobCategory::create(['name' => 'Desain', 'slug' => 'desain']);
    }

    public function test_admin_can_view_analytics(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 150000,
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 100000,
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/analytics')
            ->assertOk()
            ->assertJsonCount(12, 'data.trend')
            ->assertJsonPath('data.completed_jobs', 2)
            ->assertJsonPath('data.total_transactions', 250000)
            ->assertJsonPath('data.period_label', 'Tahun ' . now()->year)
            ->assertJsonStructure([
                'data' => [
                    'timeframe',
                    'period_start',
                    'period_end',
                    'period_label',
                    'trend' => [['label', 'total']],
                    'task_status_distribution',
                ],
            ]);
    }

    public function test_analytics_monthly_covers_each_month_of_selected_year(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();
        $year = now()->year;

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 200000,
            'updated_at' => Carbon::create($year, 6, 15),
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 300000,
            'updated_at' => Carbon::create($year - 1, 11, 5),
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/analytics?' . http_build_query([
                'timeframe' => 'monthly',
                'year' => $year,
            ]))
            ->assertOk()
            ->assertJsonCount(12, 'data.trend')
            ->assertJsonPath('data.completed_jobs', 1)
            ->assertJsonPath('data.total_transactions', 200000)
            ->assertJsonPath('data.period_label', 'Tahun ' . $year)
            ->assertJsonPath('data.trend.0.label', 'Jan')
            ->assertJsonPath('data.trend.5.total', 200000)
            ->assertJsonPath('data.trend.11.total', 0);
    }

    public function test_analytics_weekly_covers_four_weeks_of_selected_month(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();
        $year = now()->year;
        $month = now()->month;

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 40000,
            'updated_at' => Carbon::create($year, $month, 3),
        ]);
        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 60000,
            'updated_at' => Carbon::create($year, $month, 25),
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/analytics?' . http_build_query([
                'timeframe' => 'weekly',
                'year' => $year,
                'month' => $month,
            ]))
            ->assertOk()
            ->assertJsonCount(4, 'data.trend')
            ->assertJsonPath('data.completed_jobs', 2)
            ->assertJsonPath('data.total_transactions', 100000)
            ->assertJsonPath('data.period_label', Carbon::create($year, $month, 1)->locale('id')->translatedFormat('F Y'))
            ->assertJsonPath('data.period_start', Carbon::create($year, $month, 1)->toDateString())
            ->assertJsonPath('data.trend.0.label', 'Pekan 1')
            ->assertJsonPath('data.trend.0.total', 40000)
            ->assertJsonPath('data.trend.3.total', 60000);
    }

    public function test_analytics_yearly_covers_last_ten_years(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();
        $year = now()->year;

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 50000,
            'updated_at' => Carbon::create($year, 6, 10),
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/admin/analytics?' . http_build_query([
                'timeframe' => 'yearly',
                'year' => $year,
            ]))
            ->assertOk()
            ->assertJsonCount(10, 'data.trend')
            ->assertJsonPath('data.completed_jobs', 1)
            ->assertJsonPath('data.period_label', ($year - 9) . ' - ' . $year)
            ->assertJsonPath('data.trend.0.label', (string) ($year - 9))
            ->assertJsonPath('data.trend.0.total', 0)
            ->assertJsonPath('data.trend.9.label', (string) $year)
            ->assertJsonPath('data.trend.9.total', 50000);
    }

    public function test_non_admin_cannot_view_analytics(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->getJson('/api/admin/analytics')
            ->assertStatus(403);
    }

    public function test_admin_can_download_report_pdf(): void
    {
        $category = $this->category();
        $hirer = $this->hirer();
        $worker = User::factory()->freelancer()->create();

        Task::factory()->completed()->withWorker($worker)->create([
            'owner_id' => $hirer->id,
            'category_id' => $category->id,
            'budget' => 250000,
        ]);

        $response = $this->actingAs($this->admin(), 'sanctum')
            ->get('/api/admin/report?timeframe=monthly')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_admin_can_download_report_pdf_for_selected_period(): void
    {
        $response = $this->actingAs($this->admin(), 'sanctum')
            ->get('/api/admin/report?' . http_build_query([
                'timeframe' => 'yearly',
                'year' => now()->year,
                'month' => now()->month,
            ]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_non_admin_cannot_download_report(): void
    {
        $this->actingAs(User::factory()->freelancer()->create(), 'sanctum')
            ->getJson('/api/admin/report')
            ->assertStatus(403);
    }
}