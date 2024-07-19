<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Queries\SingleSeasonQuery;
use App\Queries\MatchQuery;

use Carbon\Carbon;

class SeasonController extends Controller
{
	public function show(Request $request)
	{
		$request['show_inactive'] = true;
		$request['form_matches'] = 10;
		$request['hide_teams'] = true;

		$heading[] = 'Player Leaderboard for ' . $request->season;

		$season = new SingleSeasonQuery($request);
		$matchCount = $season->matchCount();

		$heading[] = sprintf("(%d %s)", $matchCount, \Str::plural('match', $matchCount));

		return view('players.leaderboard')->with([
			'year' => $request->season,
			'heading' => implode(' ', $heading),
			'players' => $season->get()->get('leaderboard'),
		]);
	}
}
