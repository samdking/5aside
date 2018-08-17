<?php

namespace App\Queries;

use DateTime;
use Illuminate\Http\Request;

class SeasonQuery
{
	protected $players;
	protected $matches;
	protected $year;

	public function __construct(Request $request, $year)
	{
		$request->year = $year;
		$this->year = $year;
		$this->players = new PlayerQuery($request);
		$this->matches = new MatchQuery($request);
	}

	public function get()
	{
		$players = $this->players->get();

		return [
			'year' => $this->year,
			'start_date' => $players->min('first_appearance'),
			'end_date' => $this->seasonHasEnded() ? $players->max('last_appearance') : null,
			'total_matches' => $this->matchCount(),
			'matches' => $this->matches->get(),
			'leaderboard' => $players
		];
	}

	protected function seasonHasEnded()
	{
		return $this->year < (new DateTime)->format('Y');
	}

	protected function matchCount()
	{
		return \DB::selectOne("SELECT COUNT(id) AS count FROM matches where YEAR(date) = ?", [
			$this->year
		])->count;
	}
}
