<?php

namespace App\Queries;

use Illuminate\Http\Request;

class AllPlayersQuery
{
	protected $request;
	protected $streaks;

	public function __construct(Request $request)
	{
        $this->request = $request;
        $this->players = new PlayerQuery($request);
		$this->streaks = new PlayerStreakQuery($request);
	}

	public function get()
	{
		$streaks = $this->streaks->getByYear('all')->keyBy->id->map->currentStreaks();

        return $this->players->get()->each(function($player) use ($streaks) {
			$player->streaks = $streaks->get($player->id);
		});
	}
}
