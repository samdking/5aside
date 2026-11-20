<?php

namespace App\Queries;

class SeasonPlayerCountQuery
{
	/**
	 * Distinct named (non-anonymous) players per season, keyed by year,
	 * plus an "all" entry for the all-time total.
	 */
	public function get()
	{
		$query = <<<SQL
		SELECT YEAR(matches.date) AS year, COUNT(DISTINCT players.id) AS players
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN player_team ON player_team.team_id = teams.id
		INNER JOIN players ON players.id = player_team.player_id
		WHERE players.last_name != '(anon)'
		GROUP BY year WITH ROLLUP
SQL;

		return collect(\DB::select($query))->mapWithKeys(function($row) {
			return [$row->year ?? 'all' => (int)$row->players];
		});
	}
}
