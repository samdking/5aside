<?php

namespace App\Queries;

use Illuminate\Http\Request;
use App\Team;
use DateTime;

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
		  matches.date,
		  matches.is_short AS short,
		  IF(team_a.winners, "A", IF(team_b.winners, "B", null)) AS winner,
		  IF(team_a.handicap, "A", IF(team_b.handicap, "B", null)) AS handicap,
		  COUNT(player_team.id) AS total_players,
		  team_a.scored AS team_a_scored,
		  team_b.scored AS team_b_scored,
		  venues.name AS venue
		FROM matches
		INNER JOIN teams AS team_a ON team_a.match_id = matches.id
		INNER JOIN teams AS team_b ON team_b.match_id = matches.id and team_b.id != team_a.id
		INNER JOIN venues on venues.id = matches.venue_id
		INNER JOIN player_team on player_team.team_id = team_a.id
		WHERE date >= ? AND date <= ?
		GROUP BY matches.id
		ORDER BY matches.date
SQL;
		$teams = Team::with('players')->get()->groupBy('match_id');

		$placeholders = [$this->fromDate(), $this->toDate()];

		return collect(\DB::select($query, $placeholders))->each(function($match) use ($teams) {
			$match->short = (boolean)$match->short;

			foreach($teams[$match->id] as $i => $team) {
				$t = 'team_' . ['a', 'b'][$i];
				$match->$t = $team->playerData();
			}
			unset($match->id);
		});
	}

	protected function fromDate()
	{
		if ($this->request->year) {
			return (new DateTime)->setDate($this->request->year, 1, 1);
		}

		return "2015-01-01";
	}

	protected function toDate()
	{
		if ( ! $this->request->year) {
			return new DateTime($this->request->to);
		}

		$to = new DateTime;

		if ($to->format('Y') > $this->request->year) {
			$to->setDate($this->request->year, 12, 31);
		}

		return $to;

	}
}
