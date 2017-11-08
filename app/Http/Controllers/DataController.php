<?php

namespace App\Http\Controllers;

use App\Match;
use App\Player;

class DataController extends Controller
{
	public function json()
	{
		$matches = Match::with('teams.players')->latest('date')->get()->keyBy('id');
		$players = Player::with('teams.match')->get();

		$version = request('v', '2');

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
					'scored' => $player->scored(),
					'conceded' => $matches->sum(function($match) use ($player) {
						$team = $match->teamPlayedIn($player);
						return $team ? $match->getOpposition($team)->scored : 0;
					}),
					'points' => $player->totalPoints(),
					'first_appearance' => $matches[$player->teams->first()->match_id]->date->format('Y-m-d'),
					'last_appearance' => $matches[$player->teams->last()->match_id]->date->format('Y-m-d'),
				];
			}),
			'matches' => $this->{'v' . $version . 'matchData'}($matches)
		]);
	}

	protected function v1MatchData($matches)
	{
		return $matches->map(function($match) {
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
						'player_names' => $team->players->map(function($p) { return $p->shortName(); })
					];
				})
			];
		});
	}

	protected function v2MatchData($matches)
	{
		return $matches->map(function($match) {
			$teams = $match->teams;
			return [
				'date' => $match->date->format('Y-m-d'),
				'short' => $match->is_short == 1,
				'winner' => $teams[0]->winners ? 'A' : ($teams[0]->draw ? null : 'B'),
				'handicap' => $teams[0]->handicap ? 'A' : ($teams[1]->handicap ? 'B' : null),
				'team_a_scored' => $teams[0]->scored,
				'team_b_scored' => $teams[1]->scored,
				'team_a' => $teams[0]->playerData(),
				'team_b' => $teams[1]->playerData(),
			];
		})->values();
	}
}
