<?php

namespace App\Queries;

use App\MatchResult;

class FormQuery
{
	protected $request;
	protected $query = null;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		if (! is_null($this->query)) {
			return $this->query;
		}

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$matches = MatchResult::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date')->take($this->limit());

		return $this->query = $matches->get()->map->playerResults();
	}

	protected function limit()
	{
		return collect([
			$this->request->get('form_matches', 6),
			$this->request->get('match_limit'),
		])->min();
	}
}
