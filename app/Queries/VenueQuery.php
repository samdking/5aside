<?php

namespace App\Queries;

class VenueQuery
{
	public function get($fields = ['*'])
	{
		$fields = collect($fields)->map(function($field) { return 'venues.' . $field; })->implode(', ');

$query = <<<EOT
		SELECT {$fields}, COUNT(matches.id) as matches, MIN(date) AS first_match
		FROM venues
		INNER JOIN matches ON matches.venue_id = venues.id
		GROUP BY venues.id
EOT;
		return \DB::select($query);
	}
}
