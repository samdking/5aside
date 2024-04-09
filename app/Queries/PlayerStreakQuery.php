<?php

namespace App\Queries;

use App\Player;
use App\PlayerStreak;
use App\ResultStreak;

class PlayerStreakQuery
{
	protected $request;
	protected $results;
	protected $query;

	public function __construct($request)
	{
		$this->request = $request;
		$this->results = new ResultQuery($request);
	}

	public function getByYear($year)
	{
		return $this->get()->get($year);
	}

	public function getAll()
	{
		return $this->getByYear('all');
	}

	public function get()
	{
		if (is_null($this->query)) {
			$this->query = $this->query();
		}

		return $this->query;
	}

	protected function query()
	{
		$players = $this->request->player ? collect([$this->request->player]) : Player::all();

		$results = $this->results->get()->map(function($result) {
			return new ResultStreak($result);
		});

		$resultsByYear = collect(["all" => $results]);

		if ($this->request->player) {
			$resultsByYear = $resultsByYear->union($results->groupBy('year'));
		}

		$playerStreaks = $resultsByYear->map(function($results) use ($players) {
			return $results->reduce(function($playerStreaks, $match) {
				return $playerStreaks->each(function($ps) use ($match) {
					$match->updateStreakFor($ps);
				});
			}, $players->map(function($player) {
				return new PlayerStreak($player);
			}))->values();
		});

		if (is_null($this->request->player)) return $playerStreaks;

		return $playerStreaks->map->first();
	}
}
