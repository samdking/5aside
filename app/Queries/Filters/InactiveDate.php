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
		$toDate = $this->toDate->get($request);

		return $request->show_inactive ? '2015-01-01' : $toDate->sub(new DateInterval('P10W'));
	}
}
