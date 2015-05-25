<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Match;

use Illuminate\Http\Request;

class MatchController extends Controller {

	public function index()
	{
		$matches = Match::with([
			'teams',
			'teams.players' => function($q) {
				$q->orderBy('last_name');
			},
			'teams.players.teams'
		])
			->orderBy('date', 'DESC')->get();

		return view('matches.overview')->withMatches($matches);
	}

	public function show(Match $match)
	{
		return view('matches.show')->withMatch($match);
	}
}
