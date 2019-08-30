<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranslationTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('translation_translations')){
            throw new Exception('"translation_translations" table already exist in selected database /n migration stopped');
        }

        Schema::create('translation_translations', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('translation_id')->unsigned();
            $table->text('text');
            $table->string('locale')->index();

            $table->foreign('translation_id', 'trans_trans_idx')->references('id')->on('translations')->onDelete('cascade');
            $table->unique(['translation_id','locale'], 'trans_locale_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('translation_translations');
    }
}
