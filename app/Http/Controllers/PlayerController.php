<?php namespace App\Http\Controllers;

use DB;
use DateTime;

use App\Player;
use App\MatchResult;
use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Carbon\Carbon;

use App\Http\Controllers\Controller;

use App\Queries\PlayedWithAgainst;
use App\Queries\PlayerQuery;
use App\Queries\MatchQuery;
use App\Queries\SinglePlayerQuery;
use App\Queries\FormQuery;
use App\Queries\TeammatesQuery;
use App\Queries\OpponentsQuery;
use App\Queries\Filters\FromDate;
use App\Queries\Filters\ToDate;

class PlayerController extends Controller
{
	public function summary()
	{
		$total_matches = MatchResult::count();

		$highest_total_players = Team::join('player_team', 'team_id', '=', 'teams.id')
			->join('players', 'player_id', '=', 'players.id')
			->selectRaw('COUNT(player_team.player_id) as total_players')
			->where('players.last_name', '!=', '(anon)')
			->orderBy('total_players', 'DESC')
			->groupBy('teams.match_id')
			->value('total_players');

		$highest_attendance = MatchResult::select('date')
			->join('teams', 'teams.match_id', '=', 'matches.id')
			->join('player_team', 'team_id', '=', 'teams.id')
			->selectRaw('COUNT(player_team.player_id) as total_players')
			->having('total_players', '=', $highest_total_players)
			->groupBy('matches.id')
			->orderBy('date', 'ASC')
			->get('date');

		$most_appearances = Player::joinTeams()
			->selectRaw('COUNT(teams.id) AS apps')
			->orderBy('apps', 'DESC')
			->get(['first_name', 'last_name', 'apps'])
			->groupBy('apps')
			->first();

		$most_wins = Player::joinTeams()
			->join('matches', 'teams.match_id', '=', 'matches.id')
			->selectWins()
			->selectMatches()
			->orderBy('wins', 'DESC')
			->get('first_name', 'last_name', 'wins')
			->groupBy('wins')
			->first();

		$highest_win_percentage = Player::joinTeams()
			->join('matches', 'teams.match_id', '=', 'matches.id')
			->selectWinPercentage()
			->selectRaw('COUNT(teams.id) AS matches')
			->havingRaw('COUNT(teams.id) > ?', [$total_matches / 4])
			->first();

		$stats = (object)[
			'total_matches' => $total_matches,
			'highest_attendance' => $highest_attendance,
			'most_appearances' => $most_appearances,
			'most_wins' => $most_wins,
			'highest_win_percentage' => $highest_win_percentage,
			'average_attendance' => FLOOR($total_matches / 4)
		];

		return view('players.summary')->withStats($stats);
	}

	public function index(Request $request)
	{
		$request['show_inactive'] = true;
		$request['form_matches'] = 10;
		$request['order'] = 'desc';
		$request['hide_teams'] = true;

		$players = new PlayerQuery($request);
		$matches = new MatchQuery($request);

		$heading[] = 'Player Leaderboard';

		if ($request->has('from')) {
			$heading[] = 'from ' . (new DateTime($request->from))->format('jS M Y');
		}

		if ($request->has('to')) {
			$heading[] = 'to ' . (new DateTime($request->to))->format('jS M Y');
		}

		$heading[] = sprintf("(%d %s)", $matches->count(), Str::plural('match', $matches->count()));

		return view('players.leaderboard')->with([
			'heading' => implode(' ', $heading),
			'players' => $players->get()
		]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function show(Request $request)
	{
		$request['form_matches'] = 10;

		$form = new FormQuery($request);

		$player = (new SinglePlayerQuery($request, $form))->get();
		$teammates = (new TeammatesQuery($request, $form))->get();
		$opponents = (new OpponentsQuery($request, $form))->get();

		$stats = (new PlayedWithAgainst($request))->get()->reject(function($p) use ($player) {
			return ($p->against + $p->with) < $player->results->count() / 4;
		});

		return view('players.show')->with([
			'player' => $player,
			'teammates' => $teammates,
			'opponents' => $opponents,
			'stats' => $stats,
		]);
	}

	public function history()
	{
		$players = Player::with('teams')->get()->sortByDesc(function($player) {
			return $player->teams->count();
		});
		$matches = MatchResult::with('teams.players')->orderBy('date', 'desc')->get()->sortBy('date');

		return view('players.history')->with(compact('players', 'matches'));
	}

	public function matrix()
	{
		$players = Player::join('player_team', 'players.id', '=', 'player_id')
			->select('players.*')
			->groupBy('players.id')
			->orderBy(\DB::raw('COUNT(team_id)'), 'DESC')->get();

		return view('players.matrix')->withPlayers($players);
	}

}
