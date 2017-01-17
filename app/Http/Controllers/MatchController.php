<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Match;
use App\Player;

use Illuminate\Http\Request;

class MatchController extends Controller {

	public function index(Request $request)
	{
		$players = Player::all();
		$matches = Match::with([
			'teams.players' => function($q) {
				$q->orderBy('last_name');
			},
			'teams.players.teams'
		])
			->orderBy('date', 'DESC')->orderBy('matches.id', 'desc')->get();

		$teammates = $request->get('teammates', []);

		$matches = $matches->filter(function($match) use ($teammates) {
			return $match->teams->filter(function($team) use ($teammates) {
				return count(array_intersect($team->players->lists('id')->all(), $teammates)) == count($teammates);
			})->count() > 0;
		});

		return view('matches.overview')->withMatches($matches)->withPlayers($players);
	}

	public function show(Match $match)
	{
		return view('matches.show')->withMatch($match);
	}
}
