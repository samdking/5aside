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
		SELECT {$fields}, COUNT(DISTINCT matches.id) as total_matches, MAX(date) as most_recent_match, MIN(date) AS first_match
		FROM venues
		INNER JOIN matches ON matches.venue_id = venues.id
		GROUP BY venues.id

EOT;

		$matches = $this->matches->get()->groupBy('venue');

		return collect(\DB::select($query))->each(function($venue) use ($matches) {
			$matches = $matches->get($venue->name);

			$venue->total_goals = $matches->sum->total_goals;
			$venue->average_goals = round($matches->average->total_goals, 2);
			$venue->total_attendance = $matches->sum->total_players;
			$venue->average_attendance = round($matches->average->total_players, 2);

			$venue->highest_attendance = $matches->max(function($match) {
				return $match->total_players;
			});

			$venue->highest_scoring_match = $matches->max(function($match) {
				return $match->total_goals;
			});

			$venue->lowest_scoring_match = $matches
				->filter(function($match) {
					return ! is_null($match->team_a_scored);
				})->min(function($match) {
					return $match->total_goals;
				});

			$venue->highest_attendance_matches = $matches->filter(function($match) use ($venue) {
				return $match->total_players == $venue->highest_attendance;
			})->values();

			$venue->highest_scoring_matches = $matches->filter(function($match) use ($venue) {
				return $match->total_goals == $venue->highest_scoring_match;
			})->values();

			$venue->lowest_scoring_matches = $matches->filter(function($match) use ($venue) {
				return $match->total_goals == $venue->lowest_scoring_match;
			})->values();

			$venue->matches = $matches;
		});
	}
}
