<?php

namespace App\Queries;

class PlayedWithAgainst
{
    protected $request;

	public function __construct($request)
	{
		$this->request = $request;
	}

    public function get()
    {
		$query = <<<SQL
        select players.*, SUM(teammates.id = teams.id) AS `with`, SUM(teammates.id != teams.id) AS `against`
    from players
    join player_team pt on pt.player_id = players.id
    join teams on teams.id = pt.team_id
    join matches on matches.id = teams.match_id
    left join (
        select t.*
        from teams t
        join player_team pt on pt.team_id = t.id AND pt.player_id = ?
    ) teammates ON teammates.match_id = teams.match_id
    WHERE players.id != ? AND date >= ? AND date <= ?
    group by players.id
SQL;

        $placeholders = [
            $this->request->player,
            $this->request->player,
            (new Filters\FromDate)->get($this->request),
            (new Filters\ToDate)->get($this->request)
        ];

        return collect(\DB::select($query, $placeholders))->each(function($player) {
            $player->diff = $player->with - $player->against;
            $player->percentage = $player->with ? round($player->with / ($player->with + $player->against) * 100, 2) : 0;
        })->sortByDesc('percentage');
    }
}
