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
            $table->date('logitem_date');
            $table->integer('log_id')->unsigned();
            $table->integer('logex_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->integer('exercise_id')->unsigned()->index();
            $table->double('logitem_weight', 20, 3);
            $table->double('logitem_time', 20, 3);
            $table->double('logitem_abs_weight', 20, 3);
            $table->double('logitem_1rm', 20, 3);
            $table->integer('logitem_reps');
            $table->integer('logitem_sets');
            $table->double('logitem_pre', 3, 1)->nullable()->default(NULL); // CAHNGED NAME
            $table->text('logitem_comment');
            $table->smallInteger('logitem_order');
            $table->smallInteger('logex_order');
            $table->boolean('is_bw');
            $table->boolean('is_time');
            $table->boolean('is_pr');
            $table->boolean('is_warmup');
            $table->enum('options', ['w']); // NEW colomn, set array
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
