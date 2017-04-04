<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Carbon\Carbon;
use DB;

class CreateWeeksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('weeks', function ($table) {
            $table->increments('week_id');
            $table->date('week')->unique();
            $table->tinyInteger('empty')->default(0);
        });

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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('weeks');
    }
}
