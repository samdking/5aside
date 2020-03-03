<?php

namespace App;

class ResultStreak
{
	public function __construct($match)
	{
		$this->match = $match;
	}

	public function wasVoid()
	{
		return $this->match->void;
	}

	public function wasWonBy($player)
	{
		return collect($this->match->winners)->contains($player->player->id);
	}

	public function wasLostBy($player)
	{
		return collect($this->match->losers)->contains($player->player->id);
	}

	public function wasDrawnby($player)
	{
		return collect($this->match->draw)->contains($player->player->id);
	}
}
