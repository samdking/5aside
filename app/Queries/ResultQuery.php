<?php

namespace App\Queries;

use Illuminate\Http\Request;

class ResultQuery
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
		  YEAR(matches.date) AS year,
		  GROUP_CONCAT(IF(matches.is_void, pt.player_id, null)) AS voided,
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
			foreach(['voided', 'winners', 'losers', 'draw'] as $prop) {
				$match->{$prop} = collect(explode(',', $match->{$prop}));
			}
		});
	}
}
