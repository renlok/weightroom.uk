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
            $table->date('log_date'); // CHANGED NAME
            $table->double('pr_value', 25, 7); // CHANGED NAME
            $table->integer('pr_reps');
            $table->double('pr_1rm', 25, 7);
            $table->boolean('is_est1rm')->default('0'); // NEW COLOMN
            $table->boolean('is_time')->default('0');
            $table->boolean('is_endurance')->default('0'); // NEW COLOMN
            $table->boolean('is_distance')->default('0'); // NEW colomn
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
