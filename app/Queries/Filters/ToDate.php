<?php

namespace App\Queries\Filters;

use DateTime;

class ToDate
{
	function get($request)
	{
		$to = new DateTime($request->to);

		$year = $request->year ?: $request->season;

		if ($year && $to->format('Y') > $year) {
			$to->setDate($year, 12, 31);
		}

		return $to->format('Y-m-d');
	}
}
