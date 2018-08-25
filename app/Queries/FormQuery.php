<?php

namespace App\Queries;

use DateTime;
use App\Match;

class FormQuery
{
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$matches = Match::with('teams.players')
			->whereRaw('date >= ? AND date <= ?', [$this->fromDate(), $this->toDate()])
			->latest('date')->take(6)->get();

		return $matches->each(function($match) {
			$match->players = $match->teams->mapWithKeys(function($team) {
				return $team->playerResults();
			});
		});
	}

	protected function fromDate()
	{
		if ($this->request->year) {
			return (new DateTime)->setDate($this->request->year, 1, 1);
		}

		return "2015-01-01";
	}

	protected function toDate()
	{
		if ( ! $this->request->year) {
			return new DateTime($this->request->to);
		}

		$to = new DateTime;

		if ($to->format('Y') > $this->request->year) {
			$to->setDate($this->request->year, 12, 31);
		}

		return $to;

	}
}
