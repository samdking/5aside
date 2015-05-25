<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Match extends Model {

	protected $dates = ['date'];

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

}
