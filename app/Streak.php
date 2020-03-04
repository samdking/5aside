<?php

namespace App;

class Streak
{
	public $counter = 0;
	public $from;
	public $to;

	public function __construct($from = null)
	{
		$this->from = $from;
	}

	public function increment()
	{
		$this->counter++;
	}

	public function finish($date)
	{
		$this->to = $date;
	}
}
