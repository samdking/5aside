<?php

namespace App\Queries\Filters;

use DateTime;

class ToDate
{
	function get($request)
	{
		if ( ! $request->year) {
			return new DateTime($request->to);
		}

		$to = new DateTime;

		if ($to->format('Y') > $request->year) {
			$to->setDate($request->year, 12, 31);
		}

		return $to;
	}
}
