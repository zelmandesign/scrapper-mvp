<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    /** @test */
    public function schedule_definitions_have_expected_crons(): void
    {
        /** @var Schedule $schedule */
        $schedule = app(Schedule::class);

        $events = collect($schedule->events());
        $crons  = $events->map->expression;

        $this->assertTrue($crons->contains('10 2 * * *'));

        $this->assertTrue($crons->contains('10 3 */3 * *'));
    }

    /** @test */
    public function at_0210_scheduler_runs_high_tier_and_logs(): void
    {
        Log::spy();

        $this->travelTo(\Carbon\Carbon::create(2025, 9, 19, 2, 10, 0));

        $this->artisan('schedule:run');

        Log::shouldHaveReceived('info')
            ->with('[scheduler] Starting rescrape: tier=high')
            ->once();

        Log::shouldHaveReceived('info')
            ->with('[scheduler] Finished rescrape: tier=high')
            ->once();
    }
}
