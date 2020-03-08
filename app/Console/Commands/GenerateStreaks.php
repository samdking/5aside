<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Fluent;
use App\Queries\PlayerStreakQuery;

class GenerateStreaks extends Command
{
	protected $signature = 'streaks:generate {player?} {--year=}';

	public function handle()
	{
		$this->generatePlayerStreaks()["all"]->each(function($ps) {
			$this->info($ps->player->shortName());

			$ps->topStreaksByType()->each(function($streaks, $type) {
				$this->info($type);
				$streaks->each(function($streak) {
					$this->info(" - {$streak->from} - " . ($streak->to ?: 'current') . ": {$streak->count}");
				});
			});

			$this->info("");
		});
	}

	protected function generatePlayerStreaks()
	{
		$query = new PlayerStreakQuery(new Fluent($this->arguments() + $this->options()));

		return $query->get();
	}
}
