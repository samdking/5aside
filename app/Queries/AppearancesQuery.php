<?php

namespace App\Queries;

use App\MatchResult;

class AppearancesQuery
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

		$this->query = MatchResult::whereRaw('date >= ? AND date <= ?', $placeholders)
			->orderBy('date')->get()
			->map(function($m) {
				return (object)[
					'id' => $m->id,
					'date' => $m->date->format('Y-m-d'),
				];
			});

		return $this->query;
	}
}
