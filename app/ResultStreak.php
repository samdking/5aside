<?php

namespace App;

class ResultStreak
{
	public $date;

	public function __construct($match)
	{
		$this->match = $match;
		$this->date = $match->date;
	}

	public function updateStreakFor(PlayerStreak $player)
	{
		if ($this->wasVoid()) {
			$player->void($this);
		} elseif ($this->wasWonBy($player)) {
			$player->win($this);
		} elseif ($this->wasLostBy($player)) {
			$player->lose($this);
		} elseif ($this->wasDrawnBy($player)) {
			$player->draw($this);
		} else {
			$player->noShow($this);
		}
	}

	public function wasVoid()
	{
		return $this->match->void;
	}

	public function wasWonBy($player)
	{
		return $this->match->winners->contains($player->id);
	}

	public function wasLostBy($player)
	{
		return $this->match->losers->contains($player->id);
	}

	public function wasDrawnBy($player)
	{
		return $this->match->draw->contains($player->id);
	}
}
