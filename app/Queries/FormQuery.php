<?php

namespace App\Queries;

use App\Match;

class FormQuery
{
	protected $request;
	protected $query = [];

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get($byYear = null)
	{
		if (array_key_exists($byYear, $this->query)) {
			return $this->query[$byYear];
		}

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date')->take($this->limit());

		if ($byYear) {
			$matches->whereRaw('YEAR(date) = ?', [$byYear]);
		}

		return $this->query[$byYear] = $matches->get()->map->playerResults();
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
