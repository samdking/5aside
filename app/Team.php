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

	public function combos()
	{
		return $this->hasMany('App\Combo');
	}

	public function playerCombinations()
	{
		$num = count($this->players);

		//The total number of possible combinations
		$total = pow(2, $num);
		$combos = [];

		//Loop through each possible combination
		for ($i = 0; $i < $total; $i++) {
			$combo = [];
			//For each combination check if each bit is set
			for ($j = 0; $j < $num; $j++) {
				//Is bit $j set in $i?
				if (pow(2, $j) & $i) {
					$combos[$i][] = $this->players[$j]->shortName();
				}
			}
		}

		return collect($combos);
	}

	public function players()
	{
		return $this->belongsToMany('App\Player')->withPivot('injured');
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

	public function playerResults()
	{
		return $this->players->keyBy('id')->map(function($p) {
			return substr($this->result(), 0, 1);
		});
	}

	public function playerData()
	{
		return $this->players->map(function($p) {
			return [
				'id' => $p->id,
				'name' => $p->shortName(),
			];
		});
	}
}
