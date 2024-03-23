<?php

namespace App\Queries;

use DateTime;
use App\MatchStats;
use Illuminate\Http\Request;

class SingleSeasonQuery
{
	protected $request;
	protected $players;
	protected $matches;
	protected $query = [];

	public function __construct(Request $request, $matches = null)
	{
		$this->request = $request;
		$this->players = new PlayerQuery($request);
		$this->matches = $matches ?: new MatchQuery($request);
	}

	public function get($year = null)
	{
		$year = $year ?: $this->request->year;

		$result = isset($this->query[!!$year]) ? $this->query[!!$year] : $this->query($year);

		return collect($year ? $result->get($year) : $result)
			->merge($this->year($year))
			->map(function($value) {
				return is_numeric($value) ? (str_contains($value, '.') ? (float)$value : (int)$value) : $value;
			})
			->merge($this->endDate($year))
			->merge($this->stats($year))
			->merge($this->leaderboard())
			->merge($this->matches($year));
	}

	protected function query($year)
	{
		$groupBy = $year ? 'GROUP BY year' : '';

$query = <<<SQL
		SELECT
		  YEAR(date) AS year,
		  MIN(date) AS start_date,
		  MAX(date) AS end_date,
		  COUNT(id) AS total_matches,
		  ROUND(AVG(player_count), 2) AS average_players,
		  SUM(player_count) AS total_players,
		  SUM(anon_player_count) AS total_anon_players
		FROM matches
		INNER JOIN (
		  SELECT
		    CAST(SUM(players.last_name != '(anon)') AS SIGNED) AS player_count,
		    CAST(SUM(players.last_name = '(anon)') AS SIGNED) AS anon_player_count,
		    match_id
		  from player_team
		  JOIN players on players.id = player_team.player_id
		  JOIN teams on teams.id = player_team.team_id
		  group by match_id
		) pt ON pt.match_id = matches.id
		WHERE date BETWEEN ? AND ?
		{$groupBy}
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		if ($year) {
			return $this->query[true] = collect(\DB::select($query, $placeholders))->keyBy('year');
		} else {
			return $this->query[false] = \DB::selectOne($query, $placeholders);
		}
	}

	protected function year($year)
	{
		if ($year) return [];

		return [
			'year' => 'all'
		];
	}

	protected function endDate($year)
	{
		if ($this->seasonHasEnded($year)) return [];

		return [
			'end_date' => null
		];
	}

	protected function seasonHasEnded($year)
	{
		return $year && $year < (new DateTime)->format('Y');
	}

	protected function stats($year)
	{
		$matches = $year ? $this->matches->getForYear($year) : $this->matches->get();

		return $matches ? (new MatchStats($matches))->get() : [];
	}

	protected function leaderboard()
	{
		if ($this->request->hide_leaderboard) return [];

		return [
			'leaderboard' => $this->players->get()
		];
	}

	protected function matches($year)
	{
		if ($this->request->hide_matches) return [];

		return [
			'matches' => $year ? $this->matches->getForYear($year) : $this->matches->get()
		];
	}
}
