<?php

namespace App\Queries;

use Illuminate\Http\Request;

class DistinctPlayersQuery
{
	protected $request;
	protected $counts;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Distinct named people who played in each season, keyed by year, plus
	 * an "all" total. Anonymous players are excluded.
	 */
	public function get()
	{
		if ($this->counts) return $this->counts;

		$query = <<<SQL
		SELECT YEAR(matches.date) AS year, COUNT(DISTINCT players.id) AS players
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN player_team ON player_team.team_id = teams.id
		INNER JOIN players ON players.id = player_team.player_id
		WHERE players.last_name != '(anon)' AND matches.date BETWEEN ? AND ?
		GROUP BY year WITH ROLLUP
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return $this->counts = collect(\DB::select($query, $placeholders))->mapWithKeys(function($row) {
			return [$row->year ?? 'all' => (int)$row->players];
		});
	}

	public function forYear($year = null)
	{
		return $this->get()->get($year ?: 'all', 0);
	}
}
