<?php

namespace App\Queries;

class SinglePlayerQuery extends PlayerQuery
{
	protected $rank;
	protected $streaks;

	public function __construct($request)
	{
		$this->rank = new RankQuery($request);
		$this->streaks = new PlayerStreakQuery($request);
		$this->results = new PlayerResultQuery($request);

		$request->show_inactive = true;

		parent::__construct($request);
	}

	public function get()
	{
		$player = parent::get()->first();

		if ( ! $player) return null;

		unset($player->first_name);

		$player->ranking = $this->rank->get()->map(function($standings, $year) {
			$player = $standings->first(function($player) {
				return $player->id == $this->request->player;
			});

			return $player ? $player->rank : null;
		});

		$player->streaks = $this->streaks->get()->map(function($streaksForYear, $year) {
			return $streaksForYear->first()->topStreaksByType();
		});

		$player->results = $this->results->get();

		return $player;
	}
}
