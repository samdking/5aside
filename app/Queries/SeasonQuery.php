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
		  SUM(player_count) AS total_players
		FROM matches
		INNER JOIN (
		  SELECT COUNT(player_team.id) AS player_count, match_id
		  from player_team
		  JOIN teams on teams.id = player_team.teaM_id
		  group by match_id
		) pt ON pt.match_id = matches.id
		WHERE date BETWEEN ? AND ?
SQL;

		$placeholders = [$this->request->year ?: "all", $this->fromDate(), $this->toDate()];

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

	protected function fromDate()
	{
		if ($this->request->year) {
			return (new DateTime)->setDate($this->request->year, 1, 1);
		}

		return "2015-01-01";
	}

	protected function toDate()
	{
		if ( ! $this->request->year) {
			return new DateTime;
		}

		$to = new DateTime;

		if ($this->seasonHasEnded()) {
			$to->setDate($this->request->year, 12, 31);
		}

		return $to;
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
