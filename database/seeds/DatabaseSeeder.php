<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $date = Carbon::createFromDate(2010, 1, 4);
        while (true) {
            $date_string = $date->toDateString();
            DB::table('weeks')->insert([
                'week' => $date_string,
            ]);
            $date->addWeek();
            if ($date_string == '2020-12-28') {
                break;
            }
        }

        // $this->call(UserTableSeeder::class);

        Model::reguard();
    }
}
