<?php

namespace App\Queries;

use App\Match;

class FormQuery
{
	protected $request;
	protected $query;
	protected $forSeasons = [];

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function forSeason($year)
	{
		if (empty($this->forSeasons)) {
			$this->forSeasons = $this->query()->groupBy('year');
		}

		return $this->forSeasons->get($year)->take($this->limit());
	}

	public function get()
	{
		return $this->query()->take($this->limit());
	}

	protected function query()
	{
		if ( ! is_null($this->query)) return $this->query;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date');

		$this->query = $matches->get()->map(function($match) {
			return (object)[
				'players' => $match->playerResults(),
				'year' => $match->date->year,
			];
		});

		return $this->query;
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
