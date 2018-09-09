<?php

namespace App;

class PlayerStreak
{
	public $id;
	private $player;
	private $streaks;
	private $currentStreak;

	function __construct($player)
	{
		$this->player = $player;
		$this->id = $player->id;
		$this->streaks = collect();
	}

	function startStreak($streak)
	{
		$this->currentStreak = $streak;
		$this->currentStreak->addPlayer();
	}

	function currentStreak()
	{
		return $this->currentStreak;
	}

	function allStreaks()
	{
		return $this->streaks;
	}

	function finaliseStreak()
	{
		$this->currentStreak->setLastPlayer($this->player);
		$this->streaks->push($this->freezeCurrentStreak());
	}

	function freezeCurrentStreak($date = null)
	{
		if ($this->currentStreak) return $this->currentStreak->freezeForPlayer($this->player, $date);
	}

	function endStreak($date)
	{
		if (! $this->currentStreak) return;

		$this->currentStreak->removePlayer($this->player, $date);
		$this->streaks->push($this->freezeCurrentStreak($date));

		$this->currentStreak = null;
	}

	function maxStreak()
	{
		return $this->streaks->sortByDesc('count')->first();
	}
}
