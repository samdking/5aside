<?php

namespace App\Commands;

use App\Match;
use App\Player;

class GenerateStreaks extends Command
{
	protected $signature = 'combinations:generate';

	public function handle()
	{
		$this->generatePlayerStreaks()->->each(function($ps) {
			$this->info($ps->player->name);

			$ps->streaks->each(function($type, $streaks) {
				$this->info(type);
				$streaks->each(function($streak) {
					$this->info(" - #{$streak->from} - #{$streak->to ?: 'current'}: #{$streak->counter}");
				});
			});

			$this->info("");
		});
	}

	protected function generatePlayerStreaks()
	{
		$playerStreaks = Player::all()->map(function($player) { return new PlayerStreak($player); });

		Match::all()->each(function($match) use ($playerStreaks) {
			$playerStreaks->each(function($ps) use ($match) {
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
		});

		return $playerStreaks;
	}
}
