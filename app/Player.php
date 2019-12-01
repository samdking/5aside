<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
	protected $fillable = [
		'first_name', 'last_name'
	];

	public $timestamps = false;

	public function shortName()
	{
		return substr($this->first_name, 0, 1) . '. ' . $this->last_name;
	}

	public function scopeSelectWinPercentage($query)
	{
		$query->selectRaw('ROUND(SUM(teams.winners) / COUNT(teams.id) * 100, 1) AS win_percentage')
			->orderBy('win_percentage', 'DESC');
	}

	public function scopeSelectWins($query)
	{
		$query->selectRaw('SUM(teams.winners) AS wins');
	}

	public function scopeSelectMatches($query)
	{
		$query->selectRaw("COUNT(teams.match_id) AS match_count");
	}

	public function scopeJoinTeams($query)
	{
		$query->select('players.*')
			->join('player_team', 'player_team.player_id', '=', 'players.id')
			->join('teams', 'player_team.team_id', '=', 'teams.id')
			->groupBy('players.id');
	}

	public function teams()
	{
		return $this->belongsToMany('App\Team');
	}

	public function matches()
	{
		return $this->hasManyThrough('App\Match', 'App\Team');
	}

	public function winPercentage()
	{
		return round($this->wins() / $this->teams->count() * 100, 2);
	}

	public function wins()
	{
		return $this->teams->sum('winners');
	}

	public function draws()
	{
		return $this->teams->sum('draw');
	}

	public function losses()
	{
		return $this->teams->sum(function($team) {
			return $team->draw || $team->winners ? 0 : 1;
		});
	}

	public function scored()
	{
		return $this->teams->sum('scored');
	}

	public function conceded()
	{
		return $this->opponents()->sum('scored');
	}

	public function totalPoints()
	{
		return $this->teams->sum(function($team) {
			if ($team->winners) return 3;
			if ($team->draw) return 1;
			return 0;
		});
	}


	public function opponents()
	{
		return $this->teams->map(function($team) {
			return $team->match->teams->first(function($i, $team2) use ($team) {
				return $team2->id !== $team->id;
			});
		});
	}

	public function winningRecord()
	{
		return \DB::select("SELECT opponents.id, opponents.last_name, COUNT(teams.id) AS games, SUM(teams.winners) AS wins
		FROM teams
		JOIN player_team ON teams.id = player_team.team_id
		JOIN teams AS opp_teams ON opp_teams.match_id = teams.match_id AND opp_teams.id != teams.id
		JOIN player_team opp_player_team ON opp_player_team.team_id = opp_teams.id
		JOIN players opponents ON opponents.id = opp_player_team.player_id
		WHERE player_team.player_id = ?
		GROUP BY opponents.id", [$this->id]);
	}

	/**
	 * Get team player played in (if present) in given $match
	 *
	 * @param  App\Match  $match
	 * @return App\Team|null
	 */
	public function playedIn(Match $match)
	{
		return $this->teams->first(function($team) use ($match) {
			return $team->match_id === $match->id;
		});
	}

	public function playedAgainstCount($opposition)
	{
		$oppsByMatch = $opposition->teams->keyBy('match_id');

		return $this->teams->filter(function($team) use ($oppsByMatch) {
			$opp = $oppsByMatch->get($team->match_id);
			return $opp && $opp->id != $team->id;
		})->count();
	}

	public function playedWithCount($teammate)
	{
		return $this->teams->intersect($teammate->teams)->count();
	}
}
