<?php

namespace App;

class PlayerStreak
{
	public $counter = 0;
	public $topCount = 0;

	public function __construct($player)
	{
		$this->player = $player;
	}

	public function fullName()
	{
		return $this->player->fullName();
	}

	public function increment($date)
	{
		if ($this->counter == 0) $this->fromDate = $date;
		$this->toDate = $date;
		$this->counter++;
	}

	public function reset()
	{
		$this->refreshTopCount();
		$this->counter = 0;
		$this->fromDate = null;
		$this->toDate = null;
	}

	public function refreshTopCount()
	{
		$this->topCount = max($this->topCount, $this->counter);
	}

	public function snapshot()
	{
		return [
			'player' => $this->fullName(),
			'count' => $this->counter,
			'from' => $this->fromDate,
			'to' => $this->toDate,
		];
	}

	public function onStreak()
	{
		return $this->counter > 0;
	}
}
