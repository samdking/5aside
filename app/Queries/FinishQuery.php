<?php

namespace App\Queries;

class FinishQuery
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
		  year(date) AS year,
		  players.*,
		  SUM(teams.winners) * 3 + SUM(teams.draw) AS pts,
		  SUM(teams.scored) - SUM(opps.scored) AS gd,
		  COUNT(teams.id) as apps
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN teams opps ON opps.match_id = matches.id AND teams.id != opps.id
		INNER JOIN player_team pt ON pt.team_id = teams.id
		INNER JOIN players ON players.id = pt.player_id
		WHERE is_void = 0 AND date >= ? AND YEAR(date) < YEAR(?)
		GROUP BY year(date), players.id
		ORDER BY year, pts DESC, gd DESC, apps ASC
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		$allStandings = collect(\DB::select($query, $placeholders))->groupBy('year')->each(function($standings, $year) {
			$standings->each(function($player, $index) {
				$player->rank = $index + 1;
				unset($player->year);
			});
		});

		if ( ! $this->request->player) {
			return $allStandings;
		}

		return $allStandings->map(function($standings, $year) {
			$player = $standings->first(function($player) {
				return $player->id == $this->request->player;
			});

			return $player ? $player->rank : null;
		});
	}
}