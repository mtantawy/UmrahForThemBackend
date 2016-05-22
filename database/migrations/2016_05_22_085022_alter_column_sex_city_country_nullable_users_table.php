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
            DB::statement("ALTER TABLE `users` CHANGE `sex` `sex` ENUM( 'male', 'female' ) NULL");
            DB::statement("ALTER TABLE `users` CHANGE `city` `city` VARCHAR( 255 ) NULL");
            DB::statement("ALTER TABLE `users` CHANGE `country` `country` VARCHAR( 255 ) NULL");
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
            DB::statement("ALTER TABLE `users` CHANGE `sex` `sex` ENUM( 'male', 'female' )");
            DB::statement("ALTER TABLE `users` CHANGE `city` `city` VARCHAR( 255 )");
            DB::statement("ALTER TABLE `users` CHANGE `country` `country` VARCHAR( 255 )");
        });
    }
}
