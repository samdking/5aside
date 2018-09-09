<?php

namespace App;

class PlayerStreakCruncher
{
	protected $streaks;
	protected $players;

	public function __construct($players)
	{
		$this->streaks = collect();
		$this->players = $players->map(function($player) {
			return new PlayerStreak($player);
		})->keyBy('id');
	}

	public function crunch($matches)
	{
		$matches->each(function($match) {
			$streak = new Streak($match->date);
			$participants = $match->participants()->pluck('id');

			# attendees
			$this->players->filter(function($player) use ($participants) {
				return $participants->contains($player->id);
			})->reject(function($player) {
				return $player->currentStreak();
			})->each(function($player) use ($streak) {
				$player->startStreak($streak);
			});

			# absentees
			$this->players->reject(function($player) use ($participants) {
				return $participants->contains($player->id);
			})->filter(function($player) {
				return $player->currentStreak();
			})->each(function($player) use ($match) {
				$player->endStreak();
			});

			$this->streaks->push($streak);

			$this->activeStreaks()->each(function($streak) use ($match) {
				$streak->count++;
				$streak->to = $match->date;
				if ($streak->count == $this->activeStreaks()->max('count')) {
					$streak->record = true;
				}
			});
		});

		$this->players->each(function($player) {
			if ($player->currentStreak()) {
				$player->commitStreak();
			}
		});
	}

	function maxStreaks()
	{
		return $this->players->map(function($player) {
			return $player->maxStreak();
		})->sortByDesc('count');
	}

	function historicalStreaks()
	{
		return $this->streaks->filter(function($streak) {
			return $streak->record;
		})->values();
	}

	function currentStreaks()
	{
		return $this->players->filter(function($p) {
			return $p->currentStreak();
		})->map(function($p) {
			$p->commitStreak();
			return $p->lastStreak();
		})->filter(function($streak) {
			return $streak->count > 1;
		})->sortByDesc('count');
	}

	private function activeStreaks()
	{
		return $this->streaks->filter(function($s) {
			return $s->active;
		});
	}
}
