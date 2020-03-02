<?php

namespace App;

use Illuminate\Eloquent\Model;

class Streak extends Model
{
	public $counter = 0;

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
