<?php

namespace App\Queries\Filters;

use DateTime;
use DateInterval;

class FromDate
{
	function get($request)
	{
		if ($request->since) {
			return $request->since;
		}

		if ($request->from) {
			return $request->from;
		}

		if ($request->last) {
			return (new DateTime)->sub(new DateInterval('P' . $request->last))->format('Y-m-d');
		}

		if ($request->year || $request->season) {
			$year = $request->year ?: $request->season;
			return (new DateTime)->setDate($year, 1, 1)->format('Y-m-d');
		}

		return "2015-01-01";
	}
}
