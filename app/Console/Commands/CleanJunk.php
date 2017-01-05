<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

class CleanJunk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanjunk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean junk values from database';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // delete junk
        DB::table('exercises')
            ->whereNotIn('exercise_id', DB::table('log_exercises')->groupBy('log_exercises.exercise_id')->pluck('exercise_id')->all())
            ->delete();
        DB::table('logs')
            ->where('log_date', '0000-00-00')
            ->delete();
    }
}
