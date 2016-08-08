<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeDeathCauseNullableDeceasedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deceased', function (Blueprint $table) {
            \DB::statement('alter table deceased modify death_cause text null');
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
            \DB::statement('alter table deceased modify death_cause text not null');
        });
    }
}
