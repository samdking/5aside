<?php

namespace App;

class Streak
{
	private $players = 0;
	public $count = 0;
	public $record = false;
	public $from;
	public $to;
	public $active;
	public $last;
	public $player;

	function __construct($date)
	{
		$this->from = $date;
	}

	function addPlayer()
	{
		$this->active = true;
		$this->players++;
	}

	function removePlayer($player, $date)
	{
		$this->players--;

		if ($this->players == 0) {
			$this->active = false;
			$this->to = $date;
			$this->setLastPlayer($player);
		}
	}

	function freezeForPlayer($player, $date = null)
	{
		$streak = clone $this;
		$streak->player = $player->fullName();
		$streak->to = $date;
		return $streak;
	}

	function setLastPlayer($player)
	{
		$this->last = $player->fullName();
	}
}
