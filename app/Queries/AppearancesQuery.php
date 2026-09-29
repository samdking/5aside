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

		$matches = collect(\DB::select(
			'SELECT id, date FROM matches WHERE date >= ? AND date <= ? ORDER BY date',
			$placeholders
		));

		$cancelled = (new CancelledMatchQuery($this->request))->get()->map(function($m) {
			return (object)[
				'date' => $m->date,
				'cancelled' => true,
			];
		});

		$this->query = $matches->concat($cancelled)->sortBy('date')->values();

		return $this->query;
	}
}
