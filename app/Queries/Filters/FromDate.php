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
			return (new DateTime)->sub(new DateInterval('P' . $request->last));
		}

		if ($request->year) {
			return (new DateTime)->setDate($request->year, 1, 1);
		}

		return "2015-01-01";
	}
}
