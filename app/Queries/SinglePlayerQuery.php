<?php

namespace App\Queries;

class SinglePlayerQuery extends PlayerQuery
{
	public function __construct($request)
	{
		$this->finishes = new FinishQuery($request);

		$request->show_inactive = true;

		parent::__construct($request);
	}

	public function get()
	{
		$player = parent::get()->first();

		if ( ! $player) {
			return null;
		}

		$finishes = $this->finishes->get()->map(function($standings, $year) {
			$player = $standings->first(function($player) {
				return $player->id == $this->request->player;
			});

			return $player ? $player->rank : null;
		});

		if ($this->request->year) {
			$player->rank = $finishes[$this->request->year];
		} else {
			$player->finishes = $finishes;
		}

		return $player;
	}
}
