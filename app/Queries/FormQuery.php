<?php

namespace App\Queries;

use App\Match;

class FormQuery
{
	protected $request;
	protected $forSeasons = [];

	public function __construct($request)
	{
		$this->request = $request;
	}
	
	public function forSeason($year)
	{
		if (empty($this->forSeasons)) {
			$this->forSeasons = $this->get(true);
		}
		
		return $this->forSeasons->get($year)->take($this->limit());
	}

	public function get($groupByYear = false)
	{
		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date');
	
		$matches = $matches->get()->map(function($match) {
			return (object)[
				'players' => $match->playerResults(),
				'year' => $match->date->year,
			];
		});
		
		return $groupByYear ? $matches->groupBy('year') : $matches->take($this->limit());
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
