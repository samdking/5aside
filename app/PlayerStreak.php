<?php

namespace App;

class PlayerStreak
{
	public $counter = 0;
	protected $streaks;

	public function __construct($player)
	{
		$this->player = $player;
		$this->streaks = collect();
	}

	public function increment($date)
	{
		if ($this->counter == 0) $this->fromDate = $date;
		$this->toDate = $date;
		$this->counter++;
	}

	public function topStreak()
	{
		return $this->streaks->sortByDesc('count')->first();
	}

	public function logStreak()
	{
		$this->streaks->push([
			'player' => $this->player->fullName(),
			'count' => $this->counter,
			'from' => $this->fromDate,
			'to' => $this->toDate,
		]);
		$this->counter = 0;
		$this->fromDate = null;
		$this->toDate = null;

		return $this->streaks->last();
	}

	public function onStreak()
	{
		return $this->counter > 0;
	}
}
