<?php

namespace App\Queries;

use Illuminate\Http\Request;
use App\MatchStats;

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
		SELECT {$fields}, COUNT(DISTINCT matches.id) as total_matches, MAX(date) as most_recent_match, MIN(date) AS first_match
		FROM venues
		INNER JOIN matches ON matches.venue_id = venues.id
		GROUP BY venues.id

EOT;

		$matches = $this->matches->get()->groupBy('venue');

		return collect(\DB::select($query))->map(function($venue) use ($matches) {
			$matches = $matches->get($venue->name);

			return collect($venue)->merge(
				(new MatchStats($matches))->get()
			)->merge([
				'matches' => $matches,
			]);
		});
	}
}
