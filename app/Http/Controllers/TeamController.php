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
			->join('teams AS opps', function($join) {
				$join->on('opps.match_id', '=', 'teams.match_id')
				     ->on('opps.id', '!=', 'teams.id');
			})
			->select('teams.*')
			->selectRaw('(SELECT COUNT(players.id) FROM players JOIN player_team ON player_team.player_id = players.id WHERE team_id = teams.id) AS player_count')
			->selectRaw('COUNT(DISTINCT matches.date) AS `apps`')
			->selectRaw('MAX(matches.date) AS `last_app`')
			->selectRaw('(SELECT SUM(winners) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `wins`')
			->selectRaw('(SELECT SUM(draw) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `draws`')
 			->selectRaw('(SELECT SUM(handicap) FROM teams AS alt WHERE alt.unique_hash = teams.unique_hash) AS `handicap_apps`')
			->selectRaw('SUM(teams.winners) * 3 + SUM(teams.draw) AS `pts`')
			->selectRaw('SUM(teams.scored) - SUM(opps.scored) AS diff')
			->orderBy('pts', 'DESC')
			->orderBy('diff', 'DESC')
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
