<?php

namespace App\Queries;

class MatchQuery
{
	public function get()
	{
$query = <<<SQL
		SELECT
		  matches.id,
		  matches.date,
		  matches.is_short AS short,
		  IF(team_a.winners, "A", IF(team_b.winners, "B", null)) AS winner,
		  IF(team_a.handicap, "A", IF(team_b.handicap, "B", null)) AS handicap,
		  team_a.scored AS team_a_scored,
		  team_b.scored AS team_b_scored,
		  venues.name AS venue
		FROM matches
		INNER JOIN teams AS team_a ON team_a.match_id = matches.id
		INNER JOIN teams AS team_b ON team_b.match_id = matches.id and team_b.id != team_a.id
		INNER JOIN venues on venues.id = matches.venue_id
		GROUP BY matches.id
SQL;
		return collect(\DB::select($query));
	}
}
