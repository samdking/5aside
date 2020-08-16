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
		if ($request->show_inactive || $request->player) return '2015-01-01';

		return $this->toDate->get($request)->sub(new DateInterval('P10W'));
	}
}
