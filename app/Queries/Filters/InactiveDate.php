<?php

namespace App\Queries\Filters;

use DateInterval;

class InactiveDate
{
	function __construct($toDate)
	{
		$this->toDate = $toDate;
	}

	function get($request)
	{
		return $request->show_inactive ? '2015-01-01' : $this->toDate->get($request)->sub(new DateInterval('P10W'));
	}
}
