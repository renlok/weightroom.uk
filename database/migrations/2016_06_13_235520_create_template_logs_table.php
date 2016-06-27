<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTemplateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_logs', function (Blueprint $table) {
            $table->increments('template_log_id');
            $table->integer('template_id');
            $table->string('template_log_name')
            $table->text('template_log_description');
            $table->integer('template_log_week');
            $table->integer('template_log_day');
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
        Schema::drop('template_logs');
    }
}
