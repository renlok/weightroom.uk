<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('log_id');
            $table->integer('user_id')->unsigned()->index();
            $table->date('log_date');
            $table->text('log_text');
            $table->double('log_total_volume', 20, 2); // NEW colomn
            $table->integer('log_total_reps'); // NEW colomn
            $table->integer('log_total_sets'); // NEW colomn
            $table->double('log_failed_volume', 20, 2); // NEW colomn
            $table->double('log_failed_sets', 20, 2); // NEW colomn
            $table->double('log_warmup_volume', 20, 2); // NEW colomn
            $table->integer('log_warmup_reps'); // NEW colomn
            $table->integer('log_warmup_sets'); // NEW colomn
            $table->text('log_comment');
            $table->double('log_weight', 7, 2);
            $table->boolean('log_update_text')->default('0');
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
        Schema::drop('logs');
    }
}
