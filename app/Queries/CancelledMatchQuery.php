<?php

namespace App\Queries;

class CancelledMatchQuery
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
		  cancelled_matches.id,
		  cancelled_matches.date,
		  YEAR(cancelled_matches.date) AS year,
		  GROUP_CONCAT(cmp.player_id) AS players
		FROM cancelled_matches
		LEFT JOIN cancelled_match_player cmp ON cmp.cancelled_match_id = cancelled_matches.id
		WHERE date >= ? AND date <= ?
		GROUP BY cancelled_matches.id
		ORDER BY cancelled_matches.date, cancelled_matches.id
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
