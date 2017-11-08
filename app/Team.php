<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
	protected $fillable = [
		'scored',
		'winners',
		'draw',
	];

	public $timestamps = false;

	public function players()
	{
		return $this->belongsToMany('App\Player');
	}

	public function match()
	{
		return $this->belongsTo('App\Match');
	}

	public function result()
	{
		if ($this->winners) {
			return 'Win';
		}

		if ($this->draw) {
			return 'Draw';
		}

		return 'Loss';
	}

	public function opposition()
	{
		return $this
			->where('match_id', $this->match_id)
			->where('id', '!=', $this->id);
	}

	public function playerData()
	{
		return [
			'ids' => $this->players->pluck('id'),
			'names' => $this->players->map(function($p) {
				return $p->shortName();
			})
		];
	}
}
