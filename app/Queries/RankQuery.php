<?php

namespace App\Queries;

class RankQuery
{
	protected $request;
	protected $query;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getByYear($year)
	{
		return $this->get()->get($year);
	}

	public function get()
	{
		if (is_null($this->query)) {
			$this->query = $this->query();
		}

		return $this->query;
	}

	protected function query()
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
		WHERE is_void = 0 AND date >= ? AND date <= ?
		GROUP BY year(date), players.id
		ORDER BY year, pts DESC, gd DESC, apps ASC
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		$allPlayers = collect(\DB::select($query, $placeholders))->groupBy('year')->each(function($standings, $year) {
			$standings->each(function($player, $index) {
				$player->rank = $index + 1;
				unset($player->year);
			});
		});

		if (is_null($this->request->player)) return $allPlayers;

		return $allPlayers->map(function($players) {
			return $players->first(function($player) {
				return $this->request->player == $player->id;
			});
		});
	}
}
