<?php

namespace App\Queries;

class SinglePlayerQuery extends PlayerQuery
{
	public function __construct($request)
	{
		$this->matches = new MatchQuery($request);
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

		unset($player->rank);

		if ($this->request->show_matches) {
			$player->_matches = $this->matches->get();
		}

		$finishes = $this->finishes->get();

		if ($this->request->year) {
			$player->rank = $finishes[$this->request->year];
		} else {
			$player->finishes = $finishes;
		}

		return $player;
	}
}