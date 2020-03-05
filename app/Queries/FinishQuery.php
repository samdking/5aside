<?php

namespace App\Queries;

class FinishQuery
{
	protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$query = <<<SQL
		SELECT 
		  year(date) as year,
		  players.*,
		  sum(teams.winners) * 3 + sum(teams.draw) as pts,
		  sum(teams.scored) - sum(opps.scored) as gd
		from matches
		join teams on teams.match_id = matches.id
		join teams opps on opps.match_id = matches.id AND teams.id != opps.id 
		join player_team pt on pt.team_id = teams.id
		join players on players.id = pt.player_id
		where is_void = 0 AND YEAR(date) < YEAR(CURDATE())
		group by year(date), players.id
		order by year, pts desc, gd desc
SQL;

		$allStandings = collect(\DB::select($query))->groupBy('year')->each(function($standings, $year) {
			$standings->each(function($player, $index) {
				$player->rank = $index + 1;
				unset($player->year);
			});
		});

		if ( ! $this->request->player) {
			return $allStandings;
		}

		return $allStandings->map(function($standings, $year) {
			$player = $standings->first(function($player) {
				return $player->id == $this->request->player;
			});

			return $player ? $player->rank : null;
		});
	}
}