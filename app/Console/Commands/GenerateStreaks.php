<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Fluent;
use App\Player;
use App\PlayerStreak;
use App\ResultStreak;
use App\Queries\ResultQuery;

class GenerateStreaks extends Command
{
	protected $signature = 'streaks:generate {playerId?}';

	public function handle()
	{
		$this->generatePlayerStreaks()->each(function($ps) {
			$this->info($ps->player->shortName());

			collect($ps->streaks)->each(function($streaks, $type) {
				$this->info($type);
				collect($streaks)->groupBy('counter')->sortKeys()->last()->each(function($streak) {
					$this->info(" - {$streak->from} - " . ($streak->to ?: 'current') . ": {$streak->counter}");
				});
			});

			$this->info("");
		});
	}

	protected function generatePlayerStreaks()
	{
		$players = $this->argument('playerId') ? Player::find([$this->argument('playerId')]) : Player::all();

		$playerStreaks = $players->map(function($player) {
			return new PlayerStreak($player);
		});

		$results = (new ResultQuery(new Fluent))->get()->map(function($result) {
			return new ResultStreak($result);
		});

		return $results->reduce(function($playerStreaks, $match) {
			return $playerStreaks->each(function($ps) use ($match) {
				$match->updateStreakFor($ps);
			});
		}, $playerStreaks);
	}
}
