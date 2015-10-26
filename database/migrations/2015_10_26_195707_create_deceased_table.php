<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeceasedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deceased', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->enum('sex', ['male', 'female']);
            $table->integer('age');
            $table->string('country');
            $table->string('city');
            $table->text('death_cause');
            $table->date('death_date');
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
        Schema::drop('deceased');
    }
}
