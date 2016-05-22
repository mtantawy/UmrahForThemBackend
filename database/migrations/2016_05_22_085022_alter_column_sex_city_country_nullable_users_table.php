<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnSexCityCountryNullableUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('sex', ['male', 'female'])->nullable()->change();
            $table->string('country')->nullable()->change();
            $table->string('city')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('sex', ['male', 'female'])->nullable(false)->change();
            $table->string('country')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
        });
    }
}
