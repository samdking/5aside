<?php

namespace App\Queries;

class MissedMatchQuery
{
	protected $request;
	protected $query;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		if (is_null($this->query)) {
			$this->query = $this->query();
		}

		return $this->query;
	}

	public function forPlayer($id, $year = null)
	{
		return $this->get()->filter(function($match) use ($id, $year) {
			return $match->players->contains($id) && (is_null($year) || $match->year == $year);
		})->values();
	}

	protected function query()
	{
		$query = <<<SQL
		SELECT
		  missed_matches.id,
		  missed_matches.date,
		  YEAR(missed_matches.date) AS year,
		  GROUP_CONCAT(mmp.player_id) AS players
		FROM missed_matches
		LEFT JOIN missed_match_player mmp ON mmp.missed_match_id = missed_matches.id
		WHERE date >= ? AND date <= ?
		GROUP BY missed_matches.id
		ORDER BY missed_matches.date, missed_matches.id
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::select($query, $placeholders))->each(function($match) {
			$match->year = (int)$match->year;
			$match->players = collect(array_filter(explode(',', (string)$match->players)))->map(fn($id) => (int)$id);
		});
	}
}
