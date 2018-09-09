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

	function forPlayer($player)
	{
		$this->last = $player->fullName();

		return clone $this;
	}

	function removePlayer($player)
	{
		$this->players--;

		if ($this->players == 0) {
			$this->active = false;
			$this->last = $player->fullName();
		}
	}
}
