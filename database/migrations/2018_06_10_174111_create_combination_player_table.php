<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCombinationPlayerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('combination_player', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('combination_id')->unsigned();
            $table->integer('player_id')->unsigned();

            $table->foreign('combination_id')->references('id')->on('combinations');
            $table->foreign('player_id')->references('id')->on('players');
            $table->unique(['combination_id', 'player_id']);
            $table->index('combination_id');
            $table->index('player_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('combination_player');
    }
}
