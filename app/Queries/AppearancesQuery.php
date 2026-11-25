<?php

namespace App\Queries;

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

		$this->query = collect(\DB::select(
			'SELECT id, date FROM matches WHERE date >= ? AND date <= ? ORDER BY date',
			$placeholders
		));

		return $this->query;
	}
}
