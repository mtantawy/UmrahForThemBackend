<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Deceased;

class UpdateAllDeceasedToDontknowForDoneUmrahBeforeField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('deceased', function (Blueprint $table) {
            Deceased::all()->each(function ($item) {
                $item->update(['done_umrah_before' => Deceased::DONE_UMRAH_BEFORE_DONTKNOW]);
            });
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
            Deceased::all()->each(function ($item) {
                $item->update(['done_umrah_before' => Deceased::DONE_UMRAH_BEFORE_FALSE]);
            });
        });
    }
}
