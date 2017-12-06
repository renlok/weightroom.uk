<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Extend\Log_control;
use DB;

class GenerateCleanNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generatecleannames';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate url safe exercise names';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // pre fill
        DB::raw('UPDATE exercises SET exercise_name_clean = exercise_name');
        // get all exercises
        $exercises = DB::table('exercises')->get();
        foreach ($exercises as $exercise)
        {
            $clean_name = Format::urlSafeString($exercise->exercise_name);
            if ($exercise->exercise_name != $clean_name)
            {
                DB::table('exercises')->where('exercise_id', $exercise->exercise_id)
                    ->update(['exercise_name_clean' => $clean_name]);
            }
        }
        // pre fill
        DB::raw('UPDATE exercise_groups SET exgroup_name_clean = exgroup_name');
        // get all exercises
        $groups = DB::table('exercise_groups')->get();
        foreach ($groups as $group)
        {
            $clean_name = Format::urlSafeString($group->exgroup_name);
            if ($group->exgroup_name != $clean_name)
            {
                DB::table('exercise_groups')->where('exgroup_id', $group->exgroup_id)
                    ->update(['exgroup_name_clean' => $clean_name]);
            }
        }
    }
}
