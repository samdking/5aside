<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlayerTeamTable extends Migration
{
    public function up()
    {
        Schema::create('player_team', function (Blueprint $table) {
            $table->integer('player_id')->unsigned();
            $table->integer('team_id')->unsigned();
        });
    }

    public function down()
    {
        Schema::drop('player_team');
    }
}
