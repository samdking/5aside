<?php

namespace App\Queries;

use Illuminate\Http\Request;

class VenueQuery
{
	protected $request;
	protected $matches;

	public function __construct(Request $request)
	{
		$this->matches = new MatchQuery($request);
	}

	public function get($fields = ['*'])
	{
		$fields = collect($fields)->map(function($field) { return 'venues.' . $field; })->implode(', ');

$query = <<<EOT
		SELECT {$fields}, COUNT(matches.id) as match_count, MIN(date) AS first_match
		FROM venues
		INNER JOIN matches ON matches.venue_id = venues.id
		GROUP BY venues.id

EOT;

		$matches = $this->matches->get()->groupBy('venue');

		return collect(\DB::select($query))->each(function($venue) use ($matches) {
			$venue->matches = $matches->get($venue->name);
		});
	}
}
