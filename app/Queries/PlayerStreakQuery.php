<?php

namespace App\Queries;

use App\Player;
use App\PlayerStreak;
use App\ResultStreak;

class PlayerStreakQuery
{
	protected $request;
	protected $results;

	public function __construct($request)
	{
		$this->request = $request;
		$this->results = new ResultQuery($request);
	}

	public function get()
	{
		$players = $this->request->player ? Player::find([$this->request->player]) : Player::all();

		$results = $this->results->get()->map(function($result) {
			return new ResultStreak($result);
		});

		$resultsByYear = collect(["all" => $results])->union($results->groupBy('year'));

		return $resultsByYear->map(function($results) use ($players) {
			return $results->reduce(function($playerStreaks, $match) {
				return $playerStreaks->each(function($ps) use ($match) {
					$match->updateStreakFor($ps);
				});
			}, $players->map(function($player) {
				return new PlayerStreak($player);
			}))->filter(function($playerStreak) {
				return $playerStreak->active();
			})->values();
		});
	}
}