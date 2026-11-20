<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCancelledMatchesTable extends Migration
{
    public function up()
    {
        Schema::create('cancelled_matches', function (Blueprint $table) {
            $table->increments('id');
            $table->date('date');
        });

        Schema::create('cancelled_match_player', function (Blueprint $table) {
            $table->integer('cancelled_match_id')->unsigned();
            $table->integer('player_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::drop('cancelled_match_player');
        Schema::drop('cancelled_matches');
    }
}
