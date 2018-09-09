<?php

namespace App;

class PlayerStreakCruncher
{
	protected $streaks;
	protected $players;
	protected $playerStreaks;

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

		$this->finalStreaks = $this->playerStreaks->filter(function($p) {
			return $p->onStreak();
		})->map(function($p) {
			return $this->logStreak($p);
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
	}

	public function currentStreaks()
	{
		return $this->finalStreaks->sortByDesc('count');
	}

	public function historicalStreaks()
	{
		return $this->streaks;
	}

	public function maxStreaks()
	{
		return $this->playerStreaks->map(function($p) {
			return $p->topStreak();
		})->sortByDesc('count');
	}

	protected function logStreak($playerStreak)
	{
		$streak = $playerStreak->logStreak();

		if ($streak['count'] > $this->playerStreaks->max('counter')) {
			$this->streaks[] = $streak;
		}

		return $streak;
	}
}
