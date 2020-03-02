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

	public static function boot()
	{
		parent::boot();
		static::saving(function($team) {
			$team->createCombinations();
		});
	}

	public function combos()
	{
		return $this->hasMany('App\Combination');
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
					$combos[$i][$this->players[$j]->id] = $this->players[$j]->shortName();
				}
			}
		}

		return collect($combos);
	}

	public function createCombinations()
	{
		return $this->playerCombinations()->map(function($players) {
			$players = collect($players);
			$combo = $this->combos()->create([
				'string' => $players->implode(' '),
				'size' => $players->count(),
				'scored' => $this->scored,
				'complete_team' => $this->players->count() == $players->count()
			]);
			$combo->players()->attach($players->keys()->toArray());
			return $combo;
		});

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

	public function playerData()
	{
		return $this->players->map(function($p) {
			return array_filter([
				'id' => $p->id,
				'name' => $p->shortName(),
				'injured' => (boolean)$p->pivot->injured,
			]);
		});
	}
}
