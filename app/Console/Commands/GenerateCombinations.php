<?php

namespace App\Console\Commands;

use App\Combination;
use App\Team;
use Illuminate\Console\Command;

class GenerateCombinations extends Command
{
    protected $signature = 'combinations:generate';
    protected $counter = 0;

    public function handle()
    {
        $this->clear();

        $this->bar = $this->output->createProgressBar(Team::count());

        Team::with(['match', 'players' => function($q) {
            $q->orderBy('last_name');
        }])->chunk(25, function($teams) {
            $teams->each(function($team) {
                $this->counter += $team->createCombinations()->count();
                $this->bar->advance();
            });
        });

        $this->bar->finish();
        $this->info('Finished inserting ' . $this->counter . ' combos');
    }

    protected function clear()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        \DB::table('combinations')->truncate();
        \DB::table('combination_player')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
