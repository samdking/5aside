<?php namespace App\Http\Controllers;

use DB;

use App\Player;
use App\Match;

use App\Http\Controllers\Controller;

class PlayerController extends Controller {

	public function index()
	{
		$players = Player::with('teams')
			->select('players.*')
			->selectRaw('MAX(matches.date) AS `last_app`')
			->selectRaw('MAX(matches.id) AS `last_app_id`')
			->selectRaw('COUNT(teams.id) AS `played`')
			->selectRaw('SUM(teams.winners) AS `wins`')
			->selectRaw('SUM(teams.draw) AS `draws`')
			->selectRaw('COUNT(teams.id) - SUM(teams.winners) - SUM(teams.draw) AS `losses`')
			->selectRaw('SUM(teams.winners) * 3 + SUM(teams.draw) AS `pts`')
			->selectRaw('ROUND(SUM(teams.winners) / COUNT(teams.id) * 100, 2) AS `win_percentage`')
			->selectRaw('SUM(teams.handicap) AS `handicap_apps`')
			->selectRaw('SUM(IF(teams.winners AND teams.handicap, 1, 0)) AS `handicap_wins`')
			->join('player_team', 'player_team.player_id', '=', 'players.id')
			->join('teams', 'player_team.team_id', '=', 'teams.id')
			->join('matches', 'teams.match_id', '=', 'matches.id')
			->groupBy('players.id')
			->orderBy('pts', 'DESC')
			->orderBy('win_percentage', 'DESC')
			->orderBy('played', 'DESC')
			->orderBy('handicap_wins', 'DESC')
			->orderBy('handicap_apps', 'DESC')
			->orderBy('last_app', 'DESC')
			->orderBy('last_name')
			->get();

		$matches = Match::orderBy('date', 'desc')->take(10)->get()->sortBy('date');

		return view('players.leaderboard')->with([
			'players' => $players,
			'matches' => $matches
		]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show(Player $player)
	{
		/*$teams = $player->teams()->lists('team_id');

		$inTeams = function($q) use ($teams) {
			$q->whereIn('team_id', $teams);
		};

		$teammates = Player::whereHas('teams', $inTeams)
			->with(['teams' => $inTeams, 'teams.match'])
			->where('players.id', '!=', $player->id)
			->get()
			->sortBy(function($player) {
				return $player->wins();
			}, SORT_REGULAR, true);*/

		$teammates = DB::select("SELECT
  teammates.*,
  COUNT(player.team_id) AS `apps`,
  MAX(player.date) AS `last_app`,
  SUM(player.winners) AS `wins`,
  COUNT(player.team_id) - SUM(player.winners) - SUM(player.draw) AS `losses`,
  ROUND(SUM(player.winners) / COUNT(player.team_id) * 100, 2) AS `win_percentage`,
  SUM(IF(player.winners AND player.handicap, 1, 0)) AS handicap_wins,
  SUM(IF(player.handicap, 1, 0)) AS handicap_apps
FROM players AS teammates
JOIN player_team AS player_teammates ON player_teammates.player_id = teammates.id
INNER JOIN (SELECT
    players.id,
    team_id,
    winners,
    draw,
    handicap,
    matches.date
  FROM player_team
  JOIN players ON players.id = player_team.player_id
  JOIN teams ON teams.id = player_team.team_id
  JOIN matches ON matches.id = teams.match_id
  WHERE players.id = ?) AS player ON player.team_id = player_teammates.team_id
WHERE player.id IS NULL OR teammates.id != player.id
GROUP BY teammates.id
ORDER BY `wins` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `last_app` DESC, teammates.last_name ASC", [$player->id]);

		return view('players.show')->with([
			'player' => $player,
			'teammates' => $teammates,
			'matches' => $player->teams()->with('match')->get()
		]);
	}

}
