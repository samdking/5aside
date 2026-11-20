<?php

namespace App\Http\Controllers;

use App\Queries\MatchQuery;
use App\Queries\AllPlayersQuery;
use App\Queries\SinglePlayerQuery;
use App\Queries\VenueQuery;
use App\Queries\SeasonQuery;
use App\Queries\SingleSeasonQuery;
use App\Queries\AllSeasonsQuery;
use Illuminate\Http\Request;

class DataController extends Controller
{
	public function player(Request $request)
	{
		$request->short_form = true;

		return response()->json([
			'player' => (new SinglePlayerQuery($request))->get()
		]);
	}

	public function players(Request $request)
	{
		$request->short_form = true;

		return response()->json([
			'players' => (new AllPlayersQuery($request))->get()
		]);
	}

	public function matches(Request $request)
	{
		return response()->json([
			'matches' => (new MatchQuery($request))->get()
		]);
	}

	public function venues(Request $request)
	{
		return response()->json([
			'venues' => (new VenueQuery($request))->get(['name'])
		]);
	}

	public function allSeasons(Request $request)
	{
		$request->hide_teams = true;
		$request->hide_leaderboard = true;

		return response()->json([
			'seasons' => (new AllSeasonsQuery($request))->get()
		]);
	}

	public function seasons(Request $request, $year = null)
	{
		$request['show_inactive'] = true;
		$request['short_form'] = true;
		$request['year'] = $year == 'all' ? null : $year;

		if (str_contains($request->path(), '/v2/')) {
			$season = new SingleSeasonQuery($request);
		} else {
			$season = new SeasonQuery($request);
		}

		return response()->json([
			'season' => $season->get()
		]);
	}
}
