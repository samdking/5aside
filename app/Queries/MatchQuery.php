<?php

namespace App\Queries;

use Illuminate\Http\Request;
use App\Team;

class MatchQuery
{
	protected $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$query = <<<SQL
		SELECT
		  matches.id,
		  YEAR(matches.date) AS year,
		  matches.date,
		  matches.is_short AS short,
		  matches.is_void AS voided,
		  IF(team_a.winners, "A", IF(team_b.winners, "B", null)) AS winner,
		  IF(team_a.handicap, "A", IF(team_b.handicap, "B", null)) AS handicap,
		  CAST(SUM(players.last_name != '(anon)') AS SIGNED) AS total_players,
		  team_a.scored AS team_a_scored,
		  team_b.scored AS team_b_scored,
		  venues.name AS venue
		FROM matches
		INNER JOIN teams AS team_a ON team_a.match_id = matches.id
		INNER JOIN teams AS team_b ON team_b.match_id = matches.id and team_b.id != team_a.id
		INNER JOIN venues on venues.id = matches.venue_id
		INNER JOIN player_team on player_team.team_id = team_a.id
		INNER JOIN players on players.id = player_team.player_id
		WHERE date >= ? AND date <= ?
		GROUP BY matches.id
		ORDER BY matches.date
SQL;
		$teams = Team::with('players')->get()->groupBy('match_id');

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::select($query, $placeholders))->each(function($match) use ($teams) {
			$match->short = (boolean)$match->short;
			$match->voided = (boolean)$match->voided;
			if ( ! $this->request->hide_teams) {
				$match->team_a = $teams[$match->id][0]->playerData();
				$match->team_b = $teams[$match->id][1]->playerData();
			}
			unset($match->id);
		});
	}
}
