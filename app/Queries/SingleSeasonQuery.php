<?php

namespace App\Queries;

use DateTime;
use App\MatchStats;
use Illuminate\Http\Request;

class SingleSeasonQuery
{
	protected $request;
	protected $players;
	protected $matches;

	public function __construct(Request $request)
	{
        $this->request = $request;
        $this->seasons = new SeasonQuery($request);
		$this->players = new PlayerQuery($request);
		$this->matches = new MatchQuery($request);
	}

	public function get($year = null)
	{
		return $this->seasons->get($year)
			->merge($this->year($year))
			->merge($this->stats($year))
			->merge($this->leaderboard())
			->merge($this->matches($year));
	}

	protected function year($year)
	{
		if ($year) return [];

		return [
			'year' => 'all'
		];
	}

	protected function leaderboard()
	{
		if ($this->request->hide_leaderboard) return [];

		return [
			'leaderboard' => $this->players->get()
		];
	}

	protected function stats($year)
	{
		$matches = $year ? $this->matches->getForYear($year) : $this->matches->get();

		return (new MatchStats($matches))->get();
	}

	protected function matches($year)
	{
		if ($this->request->hide_matches) return [];

		return [
			'matches' => $year ? $this->matches->getForYear($year) : $this->matches->get()
		];
	}
}
