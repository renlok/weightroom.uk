<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogTemplateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_template_items', function (Blueprint $table) {
            $table->increments('logtempitem_id');
            $table->integer('log_template_id')->unsigned();
            $table->integer('logtempex_id')->unsigned()->index();
            $table->integer('texercise_id')->unsigned()->index();
            $table->boolean('is_percent_1rm')->default(0);
            $table->boolean('is_weight')->default(1);
            $table->double('logtempitem_weight', 25, 7);
            $table->boolean('is_time')->default(0);
            $table->double('logtempitem_time', 25, 7);
            $table->boolean('is_distance')->default(0);
            $table->double('logtempitem_distance', 25, 7);
            $table->integer('logtempitem_reps');
            $table->integer('logtempitem_sets');
            $table->boolean('is_pre')->default(0);
            $table->double('logtempitem_pre', 3, 1)->nullable()->default(NULL);
            $table->text('logtempitem_comment');
            $table->smallInteger('logtempitem_order');
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
        Schema::drop('log_template_items');
    }
}
