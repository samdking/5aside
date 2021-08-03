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
			$this->query = $this->query();
		}

		if (is_null($forYear)) return $this->query->take($this->limit());

		if (is_null($this->groupByYear)) {
			$this->groupByYear = $this->query->groupBy('year');
		}

		if ( ! $this->groupByYear->has($forYear)) {
			$this->groupByYear[$forYear] = $this->groupByYear->get($forYear)->take($this->limit());
		}

		return $this->groupByYear[$forYear];
	}

	protected function query()
	{
		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$query = <<<SQL
		select matches.date, IF(is_void, 'Void', IF(winners, 'Win', IF(draw, 'Draw', 'Loss'))) as result, player_id
		from matches
		join teams on teams.match_id = matches.id
		join player_team pt on pt.team_id = teams.id
		WHERE date >= ? AND date <= ?
		group by matches.date, team_id, player_id
		order by date desc
SQL;

		return collect(\DB::select($query, $placeholders))->groupBy('date')->map(function($players, $date) {
			return (object)[
				'players' => $players->keyBy('player_id')->map->result,
				'year' => substr($date, 0, 4),
			];
		})->values();
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
