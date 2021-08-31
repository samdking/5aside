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
		$query->selectRaw('ROUND(SUM(teams.winners) / SUM(!is_void) * 100, 1) AS win_percentage')
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

	public function scopeMostRecentlyPlayed($query)
	{
		$query->joinTeams()
			->join('matches', 'matches.id', '=', 'teams.match_id')
			->selectRaw('players.*, max(matches.date) AS last_played')
			->orderByDesc('last_played');
	}

	public function teams()
	{
		return $this->belongsToMany('App\Team');
	}

	public function matches()
	{
		return $this->hasManyThrough('App\Match', 'App\Team');
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
		return $match->teams->first(function($team) {
			return $team->players->contains($this);
		});
	}

	public function teamPlayedWith(Match $match, Player $player)
	{
		return $match->teams->first(function($team) use ($player) {
			return $team->players->contains($player) && $team->players->contains($this);
		});
	}

	public function teamPlayedAgainst(Match $match, Player $player)
	{
		return $match->teams->every(function($team) use ($player) {
			return $team->players->contains($this) xor $team->players->contains($player);
		}) ? $this->playedIn($match) : null;
	}
}
