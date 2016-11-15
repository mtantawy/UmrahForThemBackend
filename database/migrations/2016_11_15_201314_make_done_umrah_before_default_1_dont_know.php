<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeDoneUmrahBeforeDefault1DontKnow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deceased', function (Blueprint $table) {
            DB::statement("ALTER TABLE `deceased` CHANGE `done_umrah_before` `done_umrah_before` TINYINT(1) NOT NULL DEFAULT 1");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('deceased', function (Blueprint $table) {
            DB::statement("ALTER TABLE `deceased` CHANGE `done_umrah_before` `done_umrah_before` TINYINT(1) NOT NULL DEFAULT 0");
        });
    }
}
