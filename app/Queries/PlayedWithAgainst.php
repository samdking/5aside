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
        SELECT players.*, SUM(teammates.id = teams.id) AS `with`, SUM(teammates.id != teams.id) AS `against`
    FROM players
    INNER JOIN player_team pt ON pt.player_id = players.id
    INNER JOIN teams ON teams.id = pt.team_id
    INNER JOIN matches ON matches.id = teams.match_id
    INNER JOIN (
        SELECT t.*
        FROM teams t
        INNER JOIN player_team pt ON pt.team_id = t.id AND pt.player_id = ?
        WHERE pt.injured = 0
    ) teammates ON teammates.match_id = teams.match_id
    WHERE players.id != ? AND date >= ? AND date <= ? AND is_void = 0 AND injured = 0
    GROUP BY players.id
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
