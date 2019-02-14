<?php

namespace App\Http\Controllers;

use App\Queries\MatchQuery;
use App\Queries\PlayerQuery;
use App\Queries\VenueQuery;
use App\Queries\SeasonQuery;
use App\Queries\PointsPerGameQuery;
use App\Match;
use Illuminate\Http\Request;

class DataController extends Controller
{
	public function all(Request $request)
	{
		return response()->json([
			'players' => (new PlayerQuery($request))->get(),
			'matches' => $this->{'v' . $request->get('v', '2') . 'matchData'}($request),
			'venues' => (new VenueQuery($request))->get(['name']),
		]);
	}

	public function players(Request $request)
	{
		return response()->json([
			'players' => (new PlayerQuery($request))->get()
		]);
	}

	public function matches(Request $request)
	{
		return response()->json([
			'matches' => $this->v2MatchData($request)
		]);
	}

	public function venues(Request $request)
	{
		return response()->json([
			'venues' => (new VenueQuery($request))->get(['name'])
		]);
	}

	public function seasons(Request $request, $year = null)
	{
		$request->show_inactive = true;
		$request->year = $year;

		return response()->json([
			'season' => (new SeasonQuery($request))->get()
		]);
	}

	public function ppg(Request $request)
	{
		return response()->json([
			'players' => (new PointsPerGameQuery($request))->get()
		]);
	}

	protected function v1MatchData($request)
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

	protected function v2MatchData($request)
	{
		return (new MatchQuery($request))->get();
	}
}
