<?php

namespace App\Queries;

use App\Match;

class FormQuery
{
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request),
		];

		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', $placeholders)
			->latest('date')->take($this->limit())->get();

		return $matches->each(function($match) {
			$match->players = $match->teams->mapWithKeys(function($team) {
				return $team->playerResults();
			});
		});
	}

	protected function limit()
	{
		return $this->request->get('form_matches', 6);
	}
}
