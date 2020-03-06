<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Fluent;
use App\Queries\PlayerStreakQuery;

class GenerateStreaks extends Command
{
	protected $signature = 'streaks:generate {player?}';

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
		$query = new PlayerStreakQuery(new Fluent($this->arguments()));

		return $query->get();
	}
}
