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
		if ($this->wasVoid($player)) {
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
