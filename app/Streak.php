<?php

namespace App;

class Streak
{
	public $count = 0;
	public $from;
	public $to;

	public function __construct($from = null)
	{
		$this->from = $from;
	}

	public function extend($date)
	{
		$this->to = $date;
		$this->count++;
	}
}
