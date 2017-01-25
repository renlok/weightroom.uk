<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;
use Carbon\Carbon;

class GlobalStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'globalstats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate site wide statistics';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $users_with_logs = count(DB::table('logs')
            ->select('user_id')
            ->groupBy('user_id')
            ->get());
        $active_users_3m = DB::table('logs')
            ->select(DB::raw('COUNT(log_id) as log_count'))
            ->where('log_date', '>=', Carbon::now()->subMonths(3)->toDateString())
            ->groupBy('user_id')
            ->value('log_count');
        $active_users_1m = DB::table('logs')
            ->select(DB::raw('COUNT(log_id) as log_count'))
            ->where('log_date', '>=', Carbon::now()->subMonths(1)->toDateString())
            ->groupBy('user_id')
            ->value('log_count');
        $all_users = DB::table('users')->select(DB::raw('COUNT(user_id) as user_count'))->value('user_count');
        $all_comments = DB::table('comments')->select(DB::raw('COUNT(comment_id) as comment_count'))->value('comment_count');
        $all_replys = DB::table('comments')->select(DB::raw('COUNT(comment_id) as comment_count'))->whereNotNull('parent_id')->value('comment_count');
        $all_logs = DB::table('logs')->select(DB::raw('COUNT(log_id) as log_count'))->value('log_count');

        DB::table('global_stats')->insert([
            'gstat_date' => Carbon::now()->toDateString(),
            'total_users' => $all_users,
            'active_users_1m' => $active_users_1m,
            'active_users_3m' => $active_users_3m,
            'ever_active_users' => $users_with_logs,
            'total_comments' => $all_comments,
            'total_comment_replys' => $all_replys,
            'total_logs' => $all_logs
        ]);
    }
}
