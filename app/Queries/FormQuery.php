<?php

namespace App\Queries;

use App\Match;

class FormQuery
{
	protected $request;
	protected $query;
	protected $groupByYear;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get($forYear = null)
	{
		if (is_null($this->query) && is_null($forYear)) {
			$this->query = $this->query($this->limit());
		}

		if (is_null($forYear)) return $this->query;

		if (is_null($this->groupByYear)) {
			$this->groupByYear = $this->query()->groupBy('year');
		}

		if ( ! $this->groupByYear->has($forYear)) {
			$this->groupByYear[$forYear] = $this->groupByYear->get($forYear)->take($this->limit());
		}

		return $this->groupByYear[$forYear];
	}

	protected function query($limit = null)
	{
		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$limit = $limit ? "LIMIT {$limit}" : '';

		$query = <<<SQL
		select matches.date, group_concat(player_id) AS players
		from matches
		join teams on teams.match_id = matches.id
		join player_team pt on pt.team_id = teams.id
		WHERE date >= ? AND date <= ?
		group by matches.id
		order by date desc
		{$limit}
SQL;

		return collect(\DB::select($query, $placeholders))->map(function($match) {
			return (object)[
				'players' => collect(explode(',', $match->players)),
				'year' => substr($match->date, 0, 4),
			];
		});
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
