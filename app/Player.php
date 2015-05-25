<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Player extends Model {

	public function teams()
	{
		return $this->belongsToMany('App\Team');
	}

	public function winPercentage()
	{
		return round($this->wins() / $this->teams->count() * 100, 2);
	}

	public function wins()
	{
		return $this->teams->sum('winners');
	}

	/**
	 * Get team player played in (if present) in given $match
	 *
	 * @param  App\Match  $match
	 * @return App\Team|null
	 */
	public function playedIn(Match $match)
	{
		foreach($this->teams as $team) {
			if ($team->match_id === $match->id) {
				return $team;
			}
		}
	}
}
