<?php

namespace App\Http\Controllers;

use App\Match;
use App\Player;

class DataController extends Controller
{
	public function json()
	{
		$matches = Match::with('teams.players')->get();
		$players = Player::with('teams')->get();

		return response()->json([
			'players' => $players->map(function($player) {
				return [
					'id' => $player->id,
					'first_name' => $player->first_name,
					'last_name' => $player->last_name,
					'matches' => $player->teams->count(),
					'wins' => $player->teams->filter(function($team) {
						return $team->winners;
					})->count(),
					'losses' => $player->teams->filter(function($team) {
						return ! $team->draw && !$team->winners;
					})->count(),
					'draws' => $player->teams->filter(function($team) {
						return $team->draw;
					})->count(),
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
							'players' => $team->players->pluck('id')
						];
					})
				];
			})
		]);
	}
}
