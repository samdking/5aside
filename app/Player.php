<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model
{
	protected $fillable = [
		'first_name', 'last_name'
	];

	public $timestamps = false;

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
		return $this->teams->where('draw', 0)->where('winners', 0)->count();
	}

	public function scored()
	{
		return $this->teams->sum('scored');
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

	public function matchesPlayedAgainst($opposition)
	{
		return $this->teams->map(function($team) use ($opposition) {
			return $opposition->teams->keyBy('match_id')->get($team->match_id);
		})->filter(function($team) {
			return $team && ! $this->teams->contains($team);
		})->values('match');
	}

	public function matchesPlayedWith($teammate)
	{
		return $this->teams->intersect($teammate->teams)->values('match');
	}
}
