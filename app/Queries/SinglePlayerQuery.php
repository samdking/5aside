<?php

namespace App\Queries;

class SinglePlayerQuery
{
	protected $rank;
	protected $streaks;

	public function __construct($request)
	{
		$this->player = new PlayerQuery($request);
		$this->rank = new RankQuery($request);
		$this->streaks = new PlayerStreakQuery($request);
		$this->results = new PlayerResultQuery($request);
	}

	public function get()
	{
		$player = $this->player->get()->first();

		if ( ! $player) return null;

		unset($player->first_name);

		$allStreaks = $this->streaks->getByYear('all');

		$player->results = $this->results->get();
		$player->streaks = $allStreaks->topStreaksByType()->union(['current' => $allStreaks->currentStreaks()]);

		$player->seasons = $this->player->getSeasons()->each(function($season, $year) {
			foreach(['id' ,'year', 'first_name', 'first_initial', 'last_name'] as $attr) {
				unset($season->$attr);
			}
			$season->ranking = $this->rank->getByYear($year)->rank;
			$season->results = $this->results->getByYear($year);
			$season->streaks = $this->streaks->getByYear($year)->topStreaksByType();
		});

		return $player;
	}
}
