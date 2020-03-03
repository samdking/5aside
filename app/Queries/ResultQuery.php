<?php

namespace App\Queries;

use Illuminate\Http\Request;

class MatchQuery
{
	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$query = <<<SQL
		SELECT
		  matches.date,
		  matches.is_void AS void,
		  GROUP_CONCAT(IF(teams.winners, pt.player_id, null)) AS winners,
		  GROUP_CONCAT(IF(teams.winners = 0 and teams.draw = 0, pt.player_id, null)) AS losers,
		  GROUP_CONCAT(IF(teams.draw, pt.player_id, null)) AS draw
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN player_team pt ON pt.team_id = teams.id
		WHERE date >= ? AND date <= ?
		GROUP BY matches.id
		ORDER BY matches.date, matches.id
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::select($query, $placeholders))->each(function($match) {
			foreach(['winners', 'losers', 'draw'] as $prop) {
				$match->{$prop} = explode(',', $match->{$prop});
			}
		});
	}
}
