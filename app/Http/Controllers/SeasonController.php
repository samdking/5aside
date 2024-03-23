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
		$request['order'] = 'desc';
		$request['hide_teams'] = true;

		$heading[] = 'Player Leaderboard for ' . $request->season;

		$matches = new MatchQuery($request);
		$season = (new SingleSeasonQuery($request, $matches))->get();

		$heading[] = sprintf("(%d %s)", $matches->count(), \Str::plural('match', $matches->count()));

		return view('players.leaderboard')->with([
			'year' => $request->season,
			'heading' => implode(' ', $heading),
			'players' => $season->get('leaderboard'),
		]);
	}
}
