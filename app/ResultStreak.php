<?php

namespace App;

class ResultStreak
{
	public $date;

	public function __construct($match)
	{
		$this->match = $match;
		$this->date = $match->date;
		$this->year = $match->year;
	}

	public function updateStreakFor(PlayerStreak $player)
	{
		if ($this->wasCancelledFor($player)) {
			// Putting your name down for a match that didn't happen has no
			// effect on any streak
			return;
		} elseif ($this->wasVoid($player)) {
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

	public function wasCancelledFor($player)
	{
		return isset($this->match->cancelled) && $this->match->cancelled->contains($player->id);
	}

	public function wasVoid($player)
	{
		return $this->match->voided->contains($player->id);
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
