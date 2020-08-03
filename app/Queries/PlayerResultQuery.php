<?php

namespace App\Queries;

use Illuminate\Http\Request;
use App\Team;

class PlayerResultQuery
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
		  matches.date,
		  matches.is_short AS short,
		  matches.is_void AS voided,
		  IF(teams.winners, "Win", IF(opps.winners, "Loss", IF (teams.draw, "Draw", ""))) AS result,
		  teams.scored as scored,
		  opps.scored as conceded,
		  venues.name AS venue,
		  teams.handicap,
		  opps.handicap AS advantage,
		  teams.id AS team_id,
		  opps.id AS opponent_id
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN teams AS opps ON opps.match_id = matches.id AND opps.id != teams.id
		INNER JOIN venues ON venues.id = matches.venue_id
		INNER JOIN player_team ON player_team.team_id = teams.id
		INNER JOIN players ON players.id = player_team.player_id
		WHERE date >= ? AND date <= ? AND players.id = ?
		GROUP BY matches.id
		ORDER BY matches.date, matches.id
SQL;
		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
			$this->request->player
		];

		$teams = Team::with('players')->get()->keyBy('id');

		return collect(\DB::select($query, $placeholders))->each(function($match) use ($teams) {
			foreach(['short', 'voided', 'handicap', 'advantage'] as $prop) {
				$match->$prop = (boolean)$match->$prop;
			}

			if ($this->request->full_player_data) {
				$match->teammates = $teams[$match->team_id]->playerData();
				$match->opponents = $teams[$match->opponent_id]->playerData();
			}

			unset($match->id, $match->team_id, $match->opponent_id);
		});
	}
}
