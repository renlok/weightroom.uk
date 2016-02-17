<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_items', function (Blueprint $table) {
            $table->increments('logitem_id');
            $table->integer('log_id')->unsigned();
            $table->date('log_date'); //RENAMED colomn
            $table->integer('logex_id')->unsigned()->index(); // NEW colomn
            $table->integer('user_id')->unsigned()->index();
            $table->integer('exercise_id')->unsigned()->index();
            $table->double('logitem_weight', 20, 2);
            $table->double('logitem_time', 20, 2);
            $table->double('logitem_abs_weight', 20, 2);
            $table->double('logitem_1rm', 20, 2);
            $table->integer('logitem_reps');
            $table->integer('logitem_sets');
            $table->double('logitem_pre', 3, 1)->nullable()->default(NULL); // CAHNGED NAME
            $table->text('logitem_comment');
            $table->smallInteger('logitem_order');
            $table->smallInteger('logex_order');
            $table->boolean('is_bw')->default('0');
            $table->boolean('is_time')->default('0');
            $table->boolean('is_pr')->default('0');
            $table->boolean('is_warmup')->default('0');
            $table->boolean('is_endurance')->default('0'); // NEW colomn
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
        Schema::drop('log_items');
    }
}
