<?php

namespace App\Console\Commands;

use App\Player;
use App\Match;
use App\PlayerStreakCruncher;
use Illuminate\Console\Command;

class PlayerStreaksCalculator extends Command
{
	protected $signature = 'calculate:streaks';

	public function handle()
	{
		$matches = Match::with('teams.players')->orderBy("date")->get();
		$allPlayers = Player::all()->keyBy('id');

		$calculator = new PlayerStreakCruncher($allPlayers);

		$calculator->crunch($matches);

		$this->info('All-time highest streaks');
		foreach($calculator->maxStreaks() as $streak) {
			$this->line(
				"{$streak['player']} - {$streak['count']} ({$streak['to']->format('Y-m-d')})"
			);
		}

		$this->line('');

		$this->info('Historical streaks');
		foreach($calculator->historicalStreaks() as $streak) {
			$this->line(
				"{$streak['from']->format('Y-m-d')} - {$streak['to']->format('Y-m-d')}: {$streak['player']} ({$streak['count']})"
			);
		}

		$this->line('');

		$this->info('Current streaks');
		foreach($calculator->currentStreaks() as $streak) {
			$this->line(
				"{$streak['from']->format('Y-m-d')}: {$streak['player']} - {$streak['count']}"
			);
		}
	}
}
