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
		INNER JOIN (SELECT team_id, GROUP_CONCAT(player_id) AS player_ids FROM player_team group by team_id) players_a ON team_a.id = players_a.team_id
		INNER JOIN teams AS team_b ON team_b.match_id = matches.id and team_b.id != team_a.id
		INNER JOIN venues on venues.id = matches.venue_id



		GROUP BY matches.id
SQL;
		return collect(\DB::select($query));
	}
}

// $teams = $match->teams;
// 			return [
// 				'date' => $match->date->format('Y-m-d'),
// 				'short' => $match->is_short == 1,
// 				'winner' => $teams[0]->winners ? 'A' : ($teams[0]->draw ? null : 'B'),
// 				'handicap' => $teams[0]->handicap ? 'A' : ($teams[1]->handicap ? 'B' : null),
// 				'team_a_scored' => $teams[0]->scored,
// 				'team_b_scored' => $teams[1]->scored,
// 				'team_a' => $teams[0]->playerData(),
// 				'team_b' => $teams[1]->playerData(),
// 				'venue' => $match->venue->name
// 			];
