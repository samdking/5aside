<?php

namespace App\Queries\Filters;

use DateTime;
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

		$to = new DateTime($this->toDate->get($request));

		return $to->sub(new DateInterval('P10W'))->format('Y-m-d');
	}
}
