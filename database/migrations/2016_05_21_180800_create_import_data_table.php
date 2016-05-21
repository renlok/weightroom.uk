<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_data', function (Blueprint $table) {
            $table->increments('import_id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('log_date');
            $table->string('log_date_format');
            $table->double('log_weight', 7, 2);
            $table->string('exercise_name');
            $table->double('logitem_weight', 7, 2);
            $table->boolean('logitem_weight_is_kg')->default('1');
            $table->double('logitem_distance', 7, 2);
            $table->double('logitem_time', 7, 2);
            $table->integer('logitem_reps');
            $table->integer('logitem_sets');
            $table->string('logitem_comment');
            $table->double('logitem_pre', 3, 1)->nullable()->default(NULL);
            $table->integer('logex_order');
            $table->integer('logitem_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exercise_goals');
    }
}
