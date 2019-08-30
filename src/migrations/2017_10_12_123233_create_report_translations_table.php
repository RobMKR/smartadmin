<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('report_translations')){
            throw new Exception('"report_translations" table already exist in selected database /n migration stopped');
        }

        Schema::create('report_translations', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('report_id')->unsigned();
            $table->string('name', 64);
            $table->string('locale')->index();

            $table->foreign('report_id', 'report_trans_idx')->references('id')->on('reports')->onDelete('cascade');
            $table->unique(['report_id','locale'], 'report_locale_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_translations');
    }
}
