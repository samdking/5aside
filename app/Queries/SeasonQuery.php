<?php

namespace App\Queries;

use DateTime;
use Illuminate\Http\Request;

class SeasonQuery
{
	protected $request;
	protected $players;
	protected $matches;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->players = new PlayerQuery($request);
		$this->matches = new MatchQuery($request);
	}

	public function get()
	{
$query = <<<SQL
		SELECT
		  ? AS year,
		  MIN(date) AS start_date,
		  MAX(date) AS end_date,
		  COUNT(id) AS total_matches,
		  AVG(player_count) AS average_players,
		  CAST(ROUND(SUM(total_age) / SUM(player_count_with_age)) AS SIGNED) AS average_age,
		  MIN(min_age) AS min_age,
		  MAX(max_age) AS max_age,
		  CAST(SUM(player_count) AS SIGNED) AS total_players,
		  CAST(SUM(anon_player_count) AS SIGNED) AS total_anon_players
		FROM matches
		INNER JOIN (
		  SELECT
		    SUM(players.last_name != '(anon)') AS player_count,
		    SUM(players.last_name = '(anon)') AS anon_player_count,
		    COUNT(players.birth_year) AS player_count_with_age,
		    MIN(TIMESTAMPDIFF(YEAR, CONCAT(players.birth_year, "-12-31"), matches.date)) AS min_age,
		    MAX(TIMESTAMPDIFF(YEAR, CONCAT(players.birth_year, "-12-31"), matches.date)) AS max_age,
		    SUM(TIMESTAMPDIFF(YEAR, CONCAT(players.birth_year, "-12-31"), matches.date)) AS total_age,
		    match_id
		  from player_team
		  JOIN players on players.id = player_team.player_id
		  JOIN teams on teams.id = player_team.team_id
		  JOIN matches on matches.id = teams.match_id
		  group by match_id
		) pt ON pt.match_id = matches.id
		WHERE date BETWEEN ? AND ?
SQL;

		$placeholders = [
			$this->request->year ?: "all",
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::selectOne($query, $placeholders))
			->merge(['leaderboard' => $this->players->get()])
			->merge($this->matches())
			->merge($this->endDate());
	}

	protected function matches()
	{
		if ($this->request->hide_matches) return [];

		return [
			'matches' => $this->matches->get()
		];
	}

	protected function endDate()
	{
		if ($this->seasonHasEnded()) return [];

		return [
			'end_date' => null
		];
	}

	protected function seasonHasEnded()
	{
		return $this->request->year && $this->request->year < (new DateTime)->format('Y');
	}
}
