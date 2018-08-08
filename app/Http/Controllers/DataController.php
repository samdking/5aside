<?php

namespace App\Http\Controllers;

use App\Queries\MatchQuery;
use App\Queries\PlayerQuery;
use App\Queries\VenueQuery;
use App\Match;
use App\Combination;
use Illuminate\Http\Request;

class DataController extends Controller
{
	public function all(Request $request)
	{
		return response()->json([
			'players' => (new PlayerQuery($request))->get(),
			'matches' => $this->{'v' . $request->get('v', '2') . 'matchData'}(),
			'venues' => (new VenueQuery)->get(['name']),
		]);
	}

	public function players(Request $request)
	{
		return response()->json([
			'players' => (new PlayerQuery($request))->get($request)
		]);
	}

	public function matches()
	{
		return response()->json([
			'matches' => $this->v2MatchData()
		]);
	}

	public function venues()
	{
		return response()->json([
			"venues" => (new VenueQuery)->get(['name'])
		]);
	}

	protected function v1MatchData()
	{
		$matches = Match::with('teams.players', 'venue')->latest('date')->get()->keyBy('id');

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

	protected function v2MatchData()
	{
		$matches = (new MatchQuery)->get();
		$teams = \App\Team::all()->groupBy('match_id');

		return $matches->each(function($match) use ($teams) {
			foreach($teams[$match->id] as $i => $team) {
				$t = 'team_' . ['a', 'b'][$i];
				$match->$t = $team->playerData();
			}
			unset($match->id);
		});
	}
}
