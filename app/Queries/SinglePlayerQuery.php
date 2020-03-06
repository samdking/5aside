<?php

namespace App\Queries;

class SinglePlayerQuery extends PlayerQuery
{
	public function __construct($request)
	{
		$this->rank = new RankQuery($request);

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

		return $player;
	}
}
