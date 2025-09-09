<?php

namespace App\Queries\Filters;

use DateTime;

class ToDate
{
	function get($request)
	{
		$to = new DateTime($request->to);

		if ($request->year && $to->format('Y') > $request->year) {
			$to->setDate($request->year, 12, 31);
		}

		return $to;
	}
}
