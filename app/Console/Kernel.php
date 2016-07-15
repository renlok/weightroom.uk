<?php

namespace App\Console;

use DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
		\App\Console\Commands\GlobalStats::class,
		\App\Console\Commands\ImportFiles::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
		$schedule->command('globalstats')->monthly();

		$schedule->command('importfiles')->hourly();

        $schedule->call(function () {
            DB::table('exercises')
                ->whereNotIn('exercise_id', DB::table('log_exercises')->groupBy('log_exercises.exercise_id')->pluck('exercise_id'))
                ->delete();
        })->monthly();
    }
}
