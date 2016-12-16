<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_exercises', function (Blueprint $table) {
            $table->increments('logex_id');
            $table->integer('log_id')->unsigned()->index();
            $table->date('log_date');
            $table->integer('user_id')->unsigned()->index();
            $table->integer('exercise_id')->unsigned()->index();
            $table->double('logex_volume', 25, 7);
            $table->integer('logex_reps');
            $table->integer('logex_sets');
            $table->double('logex_failed_volume', 25, 7);
            $table->integer('logex_failed_sets');
            $table->double('logex_warmup_volume', 25, 7);
            $table->integer('logex_warmup_reps');
            $table->integer('logex_warmup_sets');
            $table->double('logex_inol', 25, 7);
            $table->double('logex_inol_warmup', 25, 7);
            $table->double('logex_time', 25, 7);
            $table->double('logex_distance', 25, 7);
            $table->string('logex_comment');
            $table->double('logex_1rm', 25, 7);
            $table->smallInteger('logex_order');
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
        Schema::drop('log_exercises');
    }
}
