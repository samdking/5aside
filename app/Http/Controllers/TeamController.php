<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;

use App\Team;

use Illuminate\Http\Request;

class TeamController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$teams = Team::groupBy('teams.unique_hash')
			->with(['players' => function($q) {
				$q->orderBy('last_name');
			}])
			->join('matches', 'matches.id', '=', 'teams.match_id')
			->join('player_team', 'player_team.team_id', '=', 'teams.id')
			->join('players', 'players.id', '=', 'player_team.player_id')
			->select('teams.*')
			->selectRaw('COUNT(players.id) AS player_count')
			->selectRaw('COUNT(DISTINCT matches.date) AS `apps`')
			->selectRaw('MAX(matches.date) AS `last_app`')
			->selectRaw('(SELECT SUM(winners) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `wins`')
			->selectRaw('(SELECT SUM(draw) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `draws`')
 			->selectRaw('(SELECT SUM(handicap) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `handicap_apps`')
			->orderBy('wins', 'DESC')
			->orderBy('draws', 'DESC')
			->orderBy('handicap_apps', 'DESC')
			->orderBy('player_count')
			->orderBy('last_app', 'DESC');

		return view('teams.leaderboard')->withTeams($teams->get());
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}
}
