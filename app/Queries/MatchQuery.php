<?php

namespace App\Queries;

use Illuminate\Http\Request;

class MatchQuery
{
	protected $request;
	protected $count;
	protected $query;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function getForYear($year)
	{
		return $this->get()->groupBy('year')->get($year);
	}

	public function get()
	{
		$direction = $this->request->order ?: 'ASC';

		$limit = collect([
			$this->request->match_limit,
			$this->request->form_matches
		])->filter()->min();

		if (is_null(@$this->query[$direction][$limit])) {
			$this->query[$direction][$limit] = $this->query($direction, $limit);
		}

		return $this->filterByPlayers($this->query[$direction][$limit]);
	}

	protected function filterByPlayers($matches)
	{
		$teammates = $this->request->get('teammates', []);
		$opponents = $this->request->get('opponents', []);

		if (empty($teammates) && empty($opponents)) {
			return $matches;
		}

		$hasAll = fn($team, $ids) => $team->pluck('id')->intersect($ids)->count() == count($ids);

		return $matches->filter(function($match) use ($teammates, $opponents, $hasAll) {
			$teams = collect([$match->team_a, $match->team_b]);

			if (!empty($teammates) && !empty($opponents)) {
				return ($hasAll($teams[0], $teammates) && $hasAll($teams[1], $opponents))
					|| ($hasAll($teams[1], $teammates) && $hasAll($teams[0], $opponents));
			}
			if (!empty($teammates)) {
				return $teams->contains(fn($team) => $hasAll($team, $teammates));
			}
			return $teams->contains(fn($team) => $hasAll($team, $opponents));
		});
	}

	protected function query($direction, $limit)
	{
		$limit = $limit ? 'LIMIT ' . $limit * 2 : '';

		if (empty($this->request->all())) {
			$where = '';
		} else {
			$where = 'WHERE date >= ? AND date <= ?';
		}

		$query = <<<SQL
		SELECT
		  matches.id,
		  teams.id AS team_id,
		  YEAR(matches.date) AS year,
		  matches.date,
		  matches.is_short AS short,
		  matches.is_void AS voided,
		  teams.winners as winner,
		  teams.handicap,
		  SUM(TIMESTAMPDIFF(YEAR, CONCAT(players.birth_year, "-12-31"), matches.date)) AS total_age,
		  CAST(SUM(players.last_name != '(anon)') AS SIGNED) AS total_players,
		  COUNT(players.birth_year) AS total_players_with_age,
		  teams.scored AS scored,
		  venues.name AS venue
		FROM matches
		INNER JOIN teams ON teams.match_id = matches.id
		INNER JOIN venues on venues.id = matches.venue_id
		INNER JOIN player_team on player_team.team_id = teams.id
		INNER JOIN players on players.id = player_team.player_id
		{$where}
		GROUP BY matches.id, teams.id
		ORDER BY matches.date {$direction}, teams.id
		{$limit}
SQL;

		$placeholders = empty($where) ? [] : $this->placeholders();

		$matches = collect(\DB::select($query, $placeholders));

		if ($this->request->hide_teams) {
			$playersByTeam = null;
		} else {
			$playersByTeam = $this->playersByTeam($matches);
		}

		return $matches->groupBy('id')->map(function($t, $matchId) use ($playersByTeam) {
			$match = (object)[
				'id' => $matchId,
				'year' => $t[0]->year,
				'date' => $t[0]->date,
				'short' => (boolean)$t[0]->short,
				'voided' => (boolean)$t[0]->voided,
				'winner' => $t[0]->winner ? 'A' : ($t[1]->winner ? 'B' : null),
				'handicap' => $t[0]->handicap ? 'A' : ($t[1]->handicap ? 'B' : null),
				'total_players' => $t->sum('total_players'),
				'team_a_scored' => $t[0]->scored,
				'team_b_scored' => $t[1]->scored,
				'total_goals' => is_null($t[0]->scored) ? null : $t->sum->scored,
				'venue' => $t[0]->venue,
				'team_a_avg_age' => $this->averageAge($t[0]),
				'team_b_avg_age' => $this->averageAge($t[1]),
			];

			if ( ! $this->request->hide_teams) {
				$match->team_a = $playersByTeam->get($t[0]->team_id, collect());
				$match->team_b = $playersByTeam->get($t[1]->team_id, collect());
			}

			return $match;
		})->values();
	}

	/**
	 * Fetch each team's players as plain rows (no Eloquent hydration) keyed by
	 * team_id. shortName() and the sort order are pushed into SQL so we never
	 * instantiate a Team or Player model for the overview. We join through to
	 * matches and use the date range to limit the players returned to only those
	 * who played in the matches.
	 */
	protected function playersByTeam($matches)
	{
		if ($matches->isEmpty()) return collect();

		$placeholders = [$matches->min('date'), $matches->max('date')];

		$query = <<<SQL
		SELECT
		  player_team.team_id,
		  players.id,
		  CONCAT(LEFT(COALESCE(players.first_name, ''), 1), '. ', COALESCE(players.last_name, '')) AS name,
		  player_team.injured
		FROM player_team
		INNER JOIN players ON players.id = player_team.player_id
		INNER JOIN teams ON teams.id = player_team.team_id
		INNER JOIN matches ON matches.id = teams.match_id
		WHERE date >= ? AND date <= ?
		ORDER BY players.last_name, players.first_name
SQL;

		return collect(\DB::select($query, $placeholders))
			->groupBy('team_id')
			->map(function($players) {
				return $players->map(fn($p) => array_filter([
					'id' => $p->id,
					'name' => $p->name,
					'injured' => (boolean)$p->injured,
				]))->values();
			});
	}

	public function count()
	{
		if (is_null($this->count)) {
			$query = <<<SQL
			SELECT count(*) as count
			FROM matches
			WHERE date >= ? AND date <= ?
	SQL;

			$this->count = collect([
				\DB::selectOne($query, $this->placeholders())->count,
				$this->request->match_limit,
			])->filter()->min();
		}

		return $this->count;
	}

	protected function placeholders()
	{
		return [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];
	}

	protected function averageAge($team)
	{
		return $team->total_players_with_age ? round($team->total_age / $team->total_players_with_age) : null;
	}
}
