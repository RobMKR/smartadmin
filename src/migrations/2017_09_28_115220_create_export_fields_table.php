<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportFieldsTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('export_fields')){
            throw new Exception('"export_fields" table already exist in selected database /n migration stopped');
        }

        Schema::create('export_fields', function (Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->unsignedInteger('export_id')->index();
            $table->string('field', 64);
            $table->string('alias', 255)->nullable();
            $table->unsignedInteger('position')->default(0);

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->softDeletes();
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
        Schema::drop('export_fields');
    }
}
