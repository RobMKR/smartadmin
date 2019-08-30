<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('role_translations')){
            throw new Exception('"role_translations" table already exist in selected database /n migration stopped');
        }

        Schema::create('role_translations', function(Blueprint $table)
        {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->string('alias');
            $table->string('locale')->index();

            $table->foreign('role_id', 'role_trans_idx')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['role_id','locale'], 'role_locale_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('role_translations');
    }
}
