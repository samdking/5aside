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

		$request->show_inactive = true;

		parent::__construct($request);
	}

	public function get()
	{
		$player = parent::get()->first();

		if ( ! $player) {
			return null;
		}

		$player->ranking = $this->rank->get()->map(function($standings, $year) {
			$player = $standings->first(function($player) {
				return $player->id == $this->request->player;
			});

			return $player ? $player->rank : null;
		});

		$player->streaks = collect($this->streaks->get()->first()->streaks)->map(function($streaks, $type) {
			return collect($streaks)->groupBy('count')->sortKeys()->last();
		});

		return $player;
	}
}
