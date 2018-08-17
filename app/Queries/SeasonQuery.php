<?php

namespace App\Queries;

use DateTime;
use Illuminate\Http\Request;

class SeasonQuery
{
	public function __construct(Request $request, $year)
	{
		$request->year = $year;
		$this->request = $request;
	}

	public function get()
	{
		$players = (new PlayerQuery($this->request))->get();

		return [
			'year' => $this->request->year,
			'start_date' => $players->min('first_appearance'),
			'end_date' => $this->seasonHasEnded() ? $players->max('last_appearance') : null,
			'total_matches' => $this->matchCount(),
			'matches' => (new MatchQuery($this->request))->get(),
			'leaderboard' => $players
		];
	}

	protected function seasonHasEnded()
	{
		return $this->request->year < (new DateTime)->format('Y');
	}

	protected function matchCount()
	{
		return \DB::selectOne("SELECT COUNT(id) AS count FROM matches where YEAR(date) = ?", [
			$this->request->year
		])->count;
	}
}
