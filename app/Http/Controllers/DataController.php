<?php

namespace App\Http\Controllers;

use App\Match;
use App\Player;

class DataController extends Controller
{
	public function json()
	{
		$matches = Match::with('teams.players')->get()->keyBy('id');
		$players = Player::with('teams')->get();

		return response()->json([
			'players' => $players->map(function($player) use ($matches) {
				return [
					'id' => $player->id,
					'first_name' => $player->first_name,
					'last_name' => $player->last_name,
					'matches' => $player->teams->count(),
					'wins' => $player->wins(),
					'losses' => $player->losses(),
					'draws' => $player->draws(),
					'first_appearance' => $matches[$player->teams->first()->match_id]->date->format('Y-m-d'),
					'last_appearance' => $matches[$player->teams->last()->match_id]->date->format('Y-m-d'),
				];
			}),
			'matches' => $matches->map(function($match) {
				return [
					'date' => $match->date->format('Y-m-d'),
					'short' => $match->is_short == 1,
					'handicap' => $match->teams->contains(function($i, $team) {
						return $team->handicap;
					}),
					'teams' => $match->teams->map(function($team) use ($match) {
						return [
							'won' => $team->winners == 1,
							'drew' => $team->draw == 1,
							'lost' => $team->draw == 0 && $team->winners = 0,
							'handicap' => $team->handicap == 1,
							'goals_for' => $team->scored,
							'goals_against' => $match->getOpposition($team)->scored,
							'player_ids' => $team->players->pluck('id'),
							'player_names' => $team->players->pluck('last_name')
						];
					})
				];
			})
		]);
	}
}
