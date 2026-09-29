<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMissedMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('missed_matches', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
        });

        Schema::create('missed_match_player', function (Blueprint $table) {
            $table->integer('missed_match_id')->unsigned();
            $table->integer('player_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::drop('missed_match_player');
        Schema::drop('missed_matches');
    }
}
