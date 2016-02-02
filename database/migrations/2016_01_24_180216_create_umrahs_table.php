<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUmrahsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('umrahs', function (Blueprint $table) {
            $table->increments('id');
            // user
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            // deceased
            $table->integer('deceased_id')->unsigned();
            $table->foreign('deceased_id')->references('id')->on('deceased');
            // umrah_status
            $table->integer('umrah_status_id')->unsigned();
            $table->foreign('umrah_status_id')->references('id')->on('umrah_statuses');

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
        Schema::drop('umrahs');
    }
}
