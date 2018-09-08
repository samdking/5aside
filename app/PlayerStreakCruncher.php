<?php

namespace App;

class PlayerStreakCruncher
{
	public $playerStreaks;

	public function __construct($players)
	{
		$this->playerStreaks = collect();
		$this->players = $players;
	}

	public function crunch($matches)
	{
		foreach($matches as $match) {
			$this->match($match);
		}

		$this->playerStreaks->each(function($p) {
			$p->refreshTopCount();
			$this->logStreak($p);
		});
	}

	public function match($match)
	{
		$participants = $match->participants()->pluck('id');

		foreach($participants as $participant) {
			$this->appearance($participant, $match);
		}

		foreach($this->players as $id => $player) {
			if (! $participants->contains($id)) {
				$this->miss($id);
			}
		}
	}

	public function appearance($playerId, $match)
	{
		if ( ! $this->playerStreaks->has($playerId)) {
			$this->playerStreaks[$playerId] = new PlayerStreak($this->players[$playerId]);
		}

		$this->playerStreaks[$playerId]->increment($match->date);
	}

	public function miss($playerId)
	{
		if ( ! $this->playerStreaks->has($playerId)) return;

		$this->logStreak($this->playerStreaks[$playerId]);

		$this->playerStreaks[$playerId]->reset();
	}

	public function currentStreaks()
	{
		return $this->playerStreaks->filter(function($p) {
			return $p->onStreak();
		})->sortByDesc('counter');
	}

	public function maxStreaks()
	{
		return $this->playerStreaks->filter(function($p) {
			return $p->topCount > 1;
		})->sortByDesc('topCount');
	}

	protected function logStreak($playerStreak)
	{
		if ($playerStreak->counter < $this->playerStreaks->max('counter')) return;

		$this->streaks[] = $playerStreak->snapshot();
	}
}
