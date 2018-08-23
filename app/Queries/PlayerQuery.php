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
		) team_a ON team_a.id = player_team.team_id
		INNER JOIN (
			SELECT id, match_id, winners AS losses, scored AS conceded, handicap AS advantage
			FROM teams
		) team_b ON team_b.match_id = team_a.match_id AND team_a.id != team_b.id
		WHERE date >= ? AND date <= ?
		GROUP BY players.id
		HAVING last_appearance >= ? AND matches >= ?
		ORDER BY points desc, matches ASC, gd DESC, scored DESC
SQL;

		$placeholders = [$this->fromDate(), $this->toDate(), $this->inactiveDate(), $this->minMatches()];

		$this->matches = $this->matches();

		return collect(\DB::select($query, $placeholders))->each(function($p) {
			$p->handicap = $p->advantage = $p->per_game = [];
			$p->form = $this->formData($p);
			foreach($p as $k => $v) {
				if (is_numeric($v)) {
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

	protected function formData($player)
	{
		return $this->matches->map(function($match) use ($player) {
			$team = collect(['a', 'b'])->first(function($i, $letter) use ($match, $player) {
				return collect($match->{'team_' . $letter})->pluck('id')->contains($player->id);
			});

			if (!$team)
				return '';
			elseif ($match->winner == strtoupper($team))
				return 'W';
			elseif ($match->winner)
				return 'L';
			else
				return 'D';
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

	protected function matches()
	{
		$matchRequest = clone $this->request;
		$matchRequest->show = 6;
		$matchRequest->descending = true;

		return (new MatchQuery($matchRequest))->get();
	}

	protected function inactiveDate()
	{
		return $this->request->show_inactive ? '2015-01-01' : $this->toDate()->sub(new DateInterval('P10W'));
	}

	protected function minMatches()
	{
		return $this->request->get("min_matches", 1);
	}
}
