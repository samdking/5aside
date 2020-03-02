<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Match;
use App\Player;
use App\PlayerStreak;

class GenerateStreaks extends Command
{
	protected $signature = 'streaks:generate';

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
		$playerStreaks = Player::all()->map(function($player) {
			return new PlayerStreak($player);
		});

		$matches = Match::with('teams.players')->orderBy('date')->get();

		return $matches->reduce(function($playerStreaks, $match) {
			return $playerStreaks->each(function($ps) use ($match) {
				if ($match->is_void) {
					$ps->void($match);
				} elseif ($match->wasWonBy($ps->player)) {
					$ps->win($match);
				} elseif ($match->wasLostBy($ps->player)) {
					$ps->lose($match);
				} elseif ($match->wasDrawnBy($ps->player)) {
					$ps->draw($match);
				} else {
					$ps->noShow($match);
				}
			});
		}, $playerStreaks);
	}
}
