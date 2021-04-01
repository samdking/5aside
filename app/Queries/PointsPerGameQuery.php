<?php

namespace App\Queries;

use App\Player;

class PointsPerGameQuery
{
	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		$query = <<<SQL
		SELECT players.id, matches.date, (SELECT (sum(winners) * 3 + sum(draw)) / count(m.id)
		FROM teams
		INNER JOIN player_team pt ON pt.team_id = teams.id
		INNER JOIN matches m ON teams.match_id = m.id
		WHERE player_id = players.id AND m.date <= matches.date AND m.date >= ?
		) AS ppg
		FROM matches
		INNER JOIN players
		WHERE matches.date >= ? AND matches.date <= ?
		GROUP BY matches.id, players.id
		ORDER BY players.id, matches.id
SQL;
		$players = Player::all()->keyBy('id');

		$query = \DB::select($query, [
			$from = (new Filters\FromDate)->get($this->request),
			$from,
			(new Filters\ToDate)->get($this->request),
		]);

		return collect($query)->reject(function($matches) {
			return $matches->ppg == null;
		})->groupBy('id')->reject(function($matches) {
			return $matches->count() == 0;
		})->map(function($matches) use ($players) {
			return collect($players->get($matches->first()->id))->merge([
				'matches' => $matches->each(function($match) {
					unset($match->id);
				})
			]);
		})->values();
	}
}
