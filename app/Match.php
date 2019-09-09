<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model
{
	protected $fillable = [
		'date', 'venue_id'
	];
	protected $dates = ['date'];

	public $timestamps = false;

	public function venue()
	{
		return $this->belongsTo('App\Venue');
	}

	public function teams()
	{
		return $this->hasMany('App\Team');
	}

	public function firstAppearances()
	{
		return Player::select('players.*')
			->join('player_team', 'player_team.player_id', '=', 'players.id')
			->join('teams', 'teams.id', '=', 'player_team.team_id')
			->join('matches', 'matches.id', '=', 'teams.match_id')
			->groupBy('player_id')
			->havingRaw('MIN(matches.date) = ?', [$this->date])
			->get();
	}

	public function getOpposition(Team $opposition)
	{
		foreach($this->teams as $team) {
			if ($team->id !== $opposition->id) {
				return $team;
			}
		}
	}

	public function teamPlayedIn(Player $player)
	{
		return $this->teams->first(function($key, $team) use ($player) {
			return $team->players->contains($player);
		});
	}

	public function overviewForTeam(Team $team)
	{
		if ( ! is_null($team->scored)) {
			$score = ' ' . $team->scored . ' - ' . $this->getOpposition($team)->scored;
		} else {
			$score = '';
		}

		return $this->date->format('j F Y') . $score;
	}
}
