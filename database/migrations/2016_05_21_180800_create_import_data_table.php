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
			$table->integer('priority')->default(5);
			$table->string('hash');
            $table->integer('user_id')->unsigned()->index();
            $table->string('log_date');
            $table->string('log_date_format');
            $table->string('log_weight');
            $table->string('exercise_name');
            $table->string('logitem_weight');
            $table->boolean('logitem_weight_is_kg')->default(1);
            $table->string('logitem_distance');
            $table->string('logitem_time');
            $table->string('logitem_reps');
            $table->string('logitem_sets');
            $table->string('logitem_comment');
            $table->string('logitem_pre')->nullable()->default(NULL);
            $table->string('logex_order');
            $table->string('logitem_order');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('import_data');
    }
}
