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
			'players' => $players->get(),
			'matches' => $matches->get(['order' => 'desc', 'limit' => 10])->each(function($m) {
				$m->date = new Carbon($m->date);
			})->sortBy('date'),
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

		$from = (new FromDate)->get($request);
		$to = (new ToDate)->get($request);

		$matchesForForm = MatchResult::with('teams.players')->where('date', '>=', $from)->where('date', '<=', $to)->orderBy('date', 'desc')->take(10);

		$player = (new SinglePlayerQuery($request))->get();

		$teammates = DB::select("SELECT
  teammates.*,
  COUNT(player.team_id) AS `apps`,
  MAX(player.date) AS `last_app`,
  SUM(player.winners) AS `wins`,
  SUM(player.draw) AS `draws`,
  SUM(player.winners) * 3 + SUM(player.draw) AS pts,
  SUM(player.lose) AS `losses`,
  SUM(player.goals_for) AS `goals_for`,
  SUM(player.goals_against) AS `goals_against`,
  SUM(player.goals_for) - SUM(player.goals_against) AS `diff`,
  ROUND(SUM(player.winners) / COUNT(player.team_id) * 100, 2) AS `win_percentage`,
  SUM(IF(player.winners AND player.handicap, 1, 0)) AS handicap_wins,
  SUM(IF(player.lose AND player.handicap, 1, 0)) AS handicap_losses,
  SUM(IF(player.handicap, 1, 0)) AS handicap_apps
FROM players AS teammates
JOIN player_team AS player_teammates ON player_teammates.player_id = teammates.id
INNER JOIN (SELECT
    players.id,
    team_id,
    teams.winners,
    opps.winners AS lose,
    teams.draw,
    teams.scored AS goals_for,
    opps.scored AS goals_against,
    teams.handicap,
    matches.date
  FROM player_team
  JOIN players ON players.id = player_team.player_id
  JOIN teams ON teams.id = player_team.team_id
  JOIN teams AS opps on opps.match_id = teams.match_id AND opps.id != teams.id
  JOIN matches ON matches.id = teams.match_id
  WHERE players.id = ? AND matches.date >= ? AND matches.date <= ? AND matches.is_void = 0 AND injured = 0) AS player ON player.team_id = player_teammates.team_id
WHERE teammates.id != player.id and injured = 0
GROUP BY teammates.id
ORDER BY `pts` DESC, `diff` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `losses` ASC, `last_app` DESC, teammates.last_name ASC", [$player->id, $from, $to]);

		$opponents = DB::select("SELECT
  opponents.id,
  opponents.first_name,
  opponents.last_name,
  COUNT(*) AS apps,
  MAX(matches.date) AS `last_app`,
  SUM(teams.winners) AS wins,
  SUM(teams.draw) AS draws,
  SUM(opp_teams.winners) AS losses,
  SUM(teams.scored) AS `goals_for`,
  SUM(opp_teams.scored) AS `goals_against`,
  SUM(teams.scored) - SUM(opp_teams.scored) AS diff,
  SUM(teams.winners) * 3 + SUM(teams.draw) AS pts,
  ROUND((SUM(IF(teams.winners, 1, 0)) / COUNT(*) * 100), 1) AS win_percentage,
  SUM(IF(teams.winners AND teams.handicap, 1, 0)) AS handicap_wins,
  SUM(IF(teams.draw = 0 AND teams.winners = 0 AND teams.handicap, 1, 0)) AS handicap_losses,
  SUM(IF(teams.handicap, 1, 0)) AS handicap_apps
FROM teams
JOIN player_team ON teams.id = player_team.team_id
JOIN matches ON matches.id = teams.match_id
JOIN teams AS opp_teams ON opp_teams.match_id = teams.match_id AND opp_teams.id != teams.id
JOIN player_team opp_player_team ON opp_player_team.team_id = opp_teams.id
JOIN players opponents ON opponents.id = opp_player_team.player_id
WHERE player_team.player_id = ? AND matches.date >= ? AND matches.date <= ? AND opp_player_team.injured = 0 AND player_team.injured = 0 AND is_void = 0
GROUP BY opponents.id
ORDER BY `pts` DESC, `diff` DESC, `win_percentage` DESC, `handicap_wins` DESC, `apps` DESC, `losses` ASC, `last_app` DESC, opponents.last_name ASC", [$player->id, $from, $to]);

		$stats = (new PlayedWithAgainst($request))->get()->reject(function($p) use ($player) {
            return ($p->against + $p->with) < $player->results->count() / 4;
        });

		return view('players.show')->with([
			'player' => $player,
			'playerObj' => Player::find($request->player),
			'teammates' => $teammates,
			'opponents' => $opponents,
			'matchesForForm' => $matchesForForm->get()->sortBy('date')->sortBy('id'),
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
