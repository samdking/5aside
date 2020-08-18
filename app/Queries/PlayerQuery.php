<?php

namespace App\Queries;

class PlayerQuery
{
	public function __construct($request)
	{
		$this->request = $request;
		$this->form = new FormQuery($request);
	}

	public function getSeasons()
	{
		return $this->get(true)->keyBy('year');
	}

	public function get($groupByYear = false)
	{
		if ($groupByYear) {
			$yearField = "YEAR(date)";
			$group = "year";
			$order = "year";
		} else {
			$yearField = "null";
			$group = "players.id";
			$order = "points desc, gd DESC, matches ASC, scored DESC";
		}

		$where = $this->request->player ? "WHERE players.id = ?" : "";

$query = <<<SQL
		SELECT
			players.id,
			SUBSTR(players.first_name, 1, 1) AS first_name, #deprecated
			SUBSTR(players.first_name, 1, 1) AS first_initial,
			{$yearField} AS year,
			players.last_name,
			SUM(matches) AS matches,
			SUM(wins) AS wins,
			SUM(losses) AS losses,
			SUM(draws) AS draws,
			SUM(scored) AS scored,
			SUM(conceded) AS conceded,
			SUM(scored) - SUM(conceded) AS gd,
			SUM(wins) * 3 + SUM(draws) AS points,
			MIN(date) AS first_appearance,
			MAX(date) AS last_appearance,
			SUM(void_matches) AS void_matches,
			SUM(handicap) AS handicap_matches,
			SUM(handicap AND wins) AS handicap_wins,
			SUM(handicap AND draws) AS handicap_draws,
			SUM(handicap AND losses) AS handicap_losses,
			SUM(IF(handicap, scored, 0)) - SUM(IF(handicap, conceded, 0)) AS handicap_gd,
			SUM(advantage) AS advantage_matches,
			SUM(advantage AND wins) AS advantage_wins,
			SUM(advantage AND draws) AS advantage_draws,
			SUM(advantage AND losses) AS advantage_losses,
			SUM(IF(advantage, scored, 0)) - SUM(IF(advantage, conceded, 0)) AS advantage_gd,
			ROUND((SUM(wins) * 3 + SUM(draws)) / (SUM(matches) - SUM(void_matches)), 2) AS per_game_points,
			ROUND(AVG(scored), 2) AS per_game_scored,
			ROUND(AVG(conceded), 2) AS per_game_conceded,
			ROUND(SUM(wins) / (SUM(matches) - SUM(void_matches)) * 100, 2) AS win_percentage
		FROM players
		INNER JOIN player_team ON player_team.player_id = players.id
		INNER JOIN (
			SELECT
				matches.date,
				teams.id,
				teams.match_id,
				1 AS matches,
				is_void AS void_matches,
				draw AS draws,
				winners AS wins,
				IF(matches.is_void, 0, scored) AS scored,
				IF(matches.is_void, 0, handicap) AS handicap
			FROM teams
			INNER JOIN matches on matches.id = teams.match_id
			WHERE date >= ? AND date <= ?
			ORDER BY matches.date desc
			LIMIT ?
		) team_a ON team_a.id = player_team.team_id
		INNER JOIN (
			SELECT
				teams.id,
				teams.match_id,
				winners AS losses,
				IF(matches.is_void, 0, scored) AS conceded,
				IF(matches.is_void, 0, handicap) AS advantage
			FROM teams
			INNER JOIN matches on matches.id = teams.match_id
		) team_b ON team_b.match_id = team_a.match_id AND team_a.id != team_b.id
		{$where}
		GROUP BY {$group}
		HAVING last_appearance >= ? AND matches >= ?
		ORDER BY {$order}
SQL;

		$placeholders = array_values(array_filter([
			(new Filters\FromDate)->get($this->request),
			($toDate = new Filters\ToDate)->get($this->request),
			$this->matchLimit(),
			$this->request->player,
			(new Filters\InactiveDate($toDate))->get($this->request),
			$this->minMatches()
		]));

		return collect(\DB::select($query, $placeholders))->each(function($p) use ($groupByYear) {
			$p->handicap = $p->advantage = $p->per_game = [];
			$p->form = $this->form->get($p->year)->map(function($players) use ($p) {
				return $players->get($p->id, "");
			});
			if ( ! $groupByYear) unset($p->year);
			foreach($p as $k => $v) {
				if (is_numeric($v) && substr($k, 0, 6) != 'first_') {
					$p->$k = $v = strpos($v, '.') === false ? (int)$v : (float)$v;
				}
				foreach(['handicap', 'advantage', 'per_game'] as $t) {
					if (strpos($k, $t . '_') === 0) {
						unset($p->$k);
						$p->$t[substr($k, strlen($t . '_'))] = $v;
					}
				}
			}
		});
	}

	protected function matchLimit()
	{
		if ($this->request->match_limit) {
			return $this->request->match_limit * 2;
		} else {
			return 999;
		}
	}

	protected function minMatches()
	{
		return $this->request->get("min_matches", 1);
	}
}
