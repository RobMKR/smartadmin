<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateLangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('langs')){
            throw new Exception('"langs" table already exist in selected database /n migration stopped');
        }

        Schema::create('langs', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('code', 2)->index();
            $table->string('term', 64);

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
        
        DB::table('langs')->insert(
            array(
                array(
                    'code' => 'en',
                    'term' => 'English'
                ),
                array(
                    'code' => 'hy',
                    'term' => 'Հայերեն'
                ),
                array(
                    'code' => 'ru',
                    'term' => 'Русский'
                )
            )
        );

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('langs');
    }
}
