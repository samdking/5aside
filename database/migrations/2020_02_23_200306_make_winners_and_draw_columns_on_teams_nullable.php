<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeWinnersAndDrawColumnsOnTeamsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teams', function($table)
        {
            $table->boolean('winners')->nullable()->change();
            $table->boolean('draw')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teams', function($table)
        {
            $table->boolean('winners')->nullable(false)->change();
            $table->boolean('draw')->nullable(false)->change();
        });
    }
}
