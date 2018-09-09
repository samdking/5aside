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

	function commitStreak()
	{
		$this->streaks->push($this->currentStreak->forPlayer($this->player));
	}

	function lastStreak()
	{
		return $this->streaks->last();
	}

	function endStreak()
	{
		$this->currentStreak->removePlayer($this->player);
		$this->commitStreak();

		$this->currentStreak = null;
	}

	function maxStreak()
	{
		return $this->streaks->sortByDesc('count')->first();
	}
}
