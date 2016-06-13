<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTemplateExercisesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_template_exercises', function (Blueprint $table) {
            $table->increments('logtempex_id');
            $table->integer('log_template_id')->unsigned()->index();
            $table->integer('texercise_id')->unsigned()->index();
            $table->boolean('is_volume')->default(0);
            $table->double('logtempex_volume', 25, 7)->default(0);
            $table->boolean('is_time')->default(0);
            $table->double('logtempex_time', 25, 7);
            $table->boolean('is_distance')->default(0);
            $table->double('logtempex_distance', 25, 7);
            $table->string('logtempex_comment');
            $table->smallInteger('logtempex_order');
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
        Schema::drop('log_template_exercises');
    }
}
