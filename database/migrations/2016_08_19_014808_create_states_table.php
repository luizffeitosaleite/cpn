<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->increments('id');
            $table->text('data');
            $table->bigInteger('version');
            $table->bigInteger('net_id');
            $table->bigInteger('author');
            $table->timestamps();

            $table->foreign('net_id')->references('id')->on('nets');
            $table->foreign('author')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::drop('states', function (Blueprint $table){
            $table->dropForeign(['net_id']);
            $table->dropForeign(['author']);
        });
        
    }
}
