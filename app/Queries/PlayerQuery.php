<?php

namespace App\Queries;

class PlayerQuery
{
	public function __construct($request)
	{
		$this->request = $request;
		$this->form = new FormQuery($request);
	}

	public function get()
	{
$query = <<<SQL
		SELECT
			players.id,
			SUBSTR(players.first_name, 1, 1) AS first_name, #deprecated
			SUBSTR(players.first_name, 1, 1) AS first_initial,
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
			SUM(handicap) AS handicap_matches,
			SUM(handicap AND wins) AS handicap_wins,
			SUM(handicap AND draws) AS handicap_draws,
			SUM(handicap AND losses) AS handicap_losses,
			SUM(advantage) AS advantage_matches,
			SUM(advantage AND wins) AS advantage_wins,
			SUM(advantage AND draws) AS advantage_draws,
			SUM(advantage AND losses) AS advantage_losses,
			ROUND((SUM(wins) * 3 + SUM(draws)) / SUM(matches), 2) AS per_game_points,
			ROUND(SUM(scored) / SUM(matches), 2) AS per_game_scored,
			ROUND(SUM(conceded) / SUM(matches), 2) AS per_game_conceded
		FROM players
		INNER JOIN player_team ON player_team.player_id = players.id
		INNER JOIN (
			SELECT matches.date, teams.id, teams.match_id, 1 AS matches, draw AS draws, winners AS wins, scored, handicap
			FROM teams
			INNER JOIN matches on matches.id = teams.match_id
			WHERE date >= ? AND date <= ?
			ORDER BY matches.date desc
			LIMIT ?
		) team_a ON team_a.id = player_team.team_id
		INNER JOIN (
			SELECT id, match_id, winners AS losses, scored AS conceded, handicap AS advantage
			FROM teams
		) team_b ON team_b.match_id = team_a.match_id AND team_a.id != team_b.id
		GROUP BY players.id
		HAVING last_appearance >= ? AND matches >= ?
		ORDER BY points desc, gd DESC, scored DESC, matches ASC
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			($toDate = new Filters\ToDate)->get($this->request),
			$this->matchLimit(),
			(new Filters\InactiveDate($toDate))->get($this->request),
			$this->minMatches()
		];

		$form = $this->form->get();

		return collect(\DB::select($query, $placeholders))->each(function($p) use ($form) {
			$p->handicap = $p->advantage = $p->per_game = [];
			$p->form = $form->map(function($m) use ($p) {
				return $m->players->has($p->id) ? $m->players[$p->id] : "";
			});
			foreach($p as $k => $v) {
				if (is_numeric($v) && substr($k, 0, 6) != 'first_') {
					$p->$k = strpos($v, '.') === false ? (int)$v : (float)$v;
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
