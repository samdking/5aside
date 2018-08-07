<?php

namespace App\Queries;

use DateTime, DateInterval;

class PlayerQuery
{
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
$query = <<<SQL
		SELECT
			players.id,
			players.first_name,
			players.last_name,
			SUM(matches) AS matches,
			SUM(wins) AS wins,
			SUM(losses) AS losses,
			SUM(draws) AS draws,
			SUM(scored) AS scored,
			SUM(conceded) AS conceded,
			SUM(scored) - SUM(conceded) AS gd,
			SUM(wins) * 3 + SUM(draws) AS points,
			SUM(wins) * 3 + SUM(draws) AS points,
			MIN(date) AS first_appearance,
			MAX(date) AS last_appearance
		FROM players
		INNER JOIN player_team ON player_team.player_id = players.id
		INNER JOIN (
			SELECT matches.date AS date, MAX(matches.date), teams.id, match_id, COUNT(matches.id) AS matches, sum(draw) AS draws, sum(winners) AS wins, SUM(scored) as scored
			FROM teams
			INNER JOIN matches on matches.id = teams.match_id
			WHERE matches.date >= ?
			GROUP BY teams.id
		) team_a ON team_a.id = player_team.team_id
		INNER JOIN (
			SELECT id, match_id, sum(winners) AS losses, SUM(scored) AS conceded
			FROM teams
			GROUP BY teams.id
		) team_b ON team_b.match_id = team_a.match_id AND team_a.id != team_b.id
		GROUP BY players.id
		HAVING last_appearance >= ? AND matches >= ?
		ORDER BY points desc, matches ASC, gd DESC, scored DESC
SQL;

		$placeholders = [$this->fromDate(), $this->inactiveDate(), $this->minMatches()];

		return collect(\DB::select($query, $placeholders))->each(function($p) {
			foreach($p as $k => $v) {
				$p->$k = is_numeric($v) ? (int)$v : $v;
			}
		});
	}

	protected function fromDate()
	{
		if ($this->request->since) {
			return $this->request->since;
		}

		if ($this->request->last) {
			return (new DateTime)->sub(new DateInterval('P' . $this->request->last));
		}

		if ($this->request->year) {
			return (new DateTime)->setDate($this->request->year, 1, 1);
		}

		return "2015-01-01";
	}

	protected function inactiveDate()
	{
		return $this->request->show_inactive ? '2015-01-01' : new DateTime('10 weeks ago');
	}

	protected function minMatches()
	{
		return $this->request->get("min_matches", 1);
	}
}
