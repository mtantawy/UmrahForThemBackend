<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeathCauseIdToDeceasedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deceased', function (Blueprint $table) {
            // death cause
            $table->integer('death_cause_id')->unsigned()->nullable();
            $table->foreign('death_cause_id')->references('id')->on('death_causes');
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
            $table->dropForeign('deceased_death_cause_id_foreign');
            $table->dropColumn('death_cause_id');
        });
    }
}
