<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Queries\MatchQuery;
use App\MatchResult;
use App\Player;

use Illuminate\Http\Request;

class MatchController extends Controller {

	public function index(Request $request)
	{
		$sql = <<<SQL
			SELECT players.*, max(date) AS last_played, max(matches.date) >= CURDATE() - INTERVAL 1 YEAR as recent, COUNT(pt.id) >= 10 AS often
			FROM players
			JOIN player_team pt on pt.player_id = players.id
			JOIN teams on teams.id = pt.team_id
			JOIN matches on matches.id = teams.match_id
			WHERE last_name != '(anon)'
			group by players.id
			having COUNT(pt.id) > 1
			ORDER BY often desc, last_played DESC, players.last_name, players.first_name
SQL;
		$players = Player::fromQuery($sql);
		$teammates = $request->get('teammates', []);
		$opponents = $request->get('opponents', []);

		$request['order'] = 'desc';

		$matches = (new MatchQuery($request))->get();

		return view('matches.overview')
			->withMatches($matches)
			->withPlayers($players)
			->withTeammates($teammates)
			->withOpponents($opponents);
	}

	public function show(MatchResult $match)
	{
		return view('matches.show')->withMatch($match);
	}
}
