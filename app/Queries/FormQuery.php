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
		if (is_null($this->query)) {
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

		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date');

		return $matches->get()->map(function($match) {
			return (object)[
				'players' => $match->playerResults(),
				'year' => $match->date->year,
			];
		});
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
