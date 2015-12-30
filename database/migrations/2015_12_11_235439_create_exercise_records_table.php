<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExerciseRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exercise_records', function (Blueprint $table) {
            $table->increments('pr_id');
            $table->integer('exercise_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->date('pr_date');
            $table->double('pr_value', 20, 3); // CHANGED NAME
            $table->integer('pr_reps');
            $table->double('pr_1rm', 20, 3);
            $table->boolean('is_est1rm'); // NEW COLOMN
            $table->boolean('is_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exercise_records');
    }
}
