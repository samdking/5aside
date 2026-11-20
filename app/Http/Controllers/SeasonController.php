<?php namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Queries\AllSeasonsQuery;
use App\Queries\PlayerQuery;
use App\Queries\SingleSeasonQuery;
use App\Queries\MatchQuery;

use Carbon\Carbon;

class SeasonController extends Controller
{
	public function index(Request $request)
	{
		$request['show_inactive'] = true;
		$request['hide_leaderboard'] = true;
		$request['hide_matches'] = true;

		$seasons = (new AllSeasonsQuery($request))->get();

		// One row per player per year, sorted like each season's leaderboard,
		// so the first row of each year is that season's champion.
		$champions = (new PlayerQuery($request))
			->get('YEAR(date)', 'players.id, year', 'year, ' . PlayerQuery::DEFAULT_ORDER)
			->groupBy('year')
			->map->first();

		return view('seasons.index')->with([
			'seasons' => $seasons->except('all')->sortKeysDesc(),
			'allTime' => $seasons->get('all'),
			'champions' => $champions,
			'currentYear' => (int)date('Y'),
		]);
	}

	public function show(Request $request)
	{
		$request['show_inactive'] = true;
		$request['form_matches'] = 10;
		$request['year'] = $request->season;
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
