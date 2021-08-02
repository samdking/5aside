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
		  YEAR(matches.date) AS year,
		  matches.is_short AS short,
		  matches.is_void AS voided,
		  teams.winners as winner,
		  teams.handicap,
		  CAST(SUM(players.last_name != '(anon)') AS SIGNED) AS total_players,
		  teams.scored AS scored,
		  venues.name AS venue
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN venues on venues.id = matches.venue_id
		INNER JOIN player_team on player_team.team_id = teams.id
		INNER JOIN players on players.id = player_team.player_id
		WHERE date >= ? AND date <= ?
		GROUP BY matches.id, teams.id
		ORDER BY matches.date, teams.id
SQL;
		$teams = Team::with('players')->get()->groupBy('match_id');

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::select($query, $placeholders))->groupBy('id')->map(function($t) use ($teams) {
			$matchId = $t[0]->id;

			$match = (object)[
				'id' => $matchId,
				'year' => $t[0]->year,
				'date' => $t[0]->date,
				'short' => (boolean)$t[0]->short,
				'voided' => (boolean)$t[0]->voided,
				'winner' => $t[0]->winner ? 'A' : ($t[1]->winner ? 'B' : null),
				'handicap' => $t[0]->handicap ? 'A' : ($t[1]->handicap ? 'B' : null),
				'total_players' => $t->sum('total_players'),
				'team_a_scored' => $t[0]->scored,
				'team_b_scored' => $t[1]->scored,
				'total_goals' => is_null($t[0]->scored) ? null : $t->sum->scored,
				'venue' => $t[0]->venue,
			];


			if ( ! $this->request->hide_teams) {
				$match->team_a = $teams[$matchId][0]->playerData();
				$match->team_b = $teams[$matchId][1]->playerData();
			}

			return $match;
		})->values();
	}
}
