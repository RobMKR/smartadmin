<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')){
            throw new Exception('"users" table already exist in selected database /n migration stopped');
        }

        Schema::create('users', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('super');
            $table->enum('active',['active', 'inactive']);

            $table->rememberToken();

            $table->softDeletes();
            $table->timestamps();
        });

        DB::table('users')->insert(
            array(
                'email' => 'superadmin',
                'password' => bcrypt('123456'),
                'active' => 1,
                'super' => 1
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
        Schema::drop('users');
    }
}
