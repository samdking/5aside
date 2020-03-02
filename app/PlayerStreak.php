<?php

namespace App;

use Illuminate\Eloquent\Model;

class PlayerStreak extends Model
{
	public $streaks;
	public $player;
	protected $currentStreak = [];

	public function __construct($player)
	{
		$this->player = $player;
	}

	public function win($match)
	{
		$this->hit('apps', $match);
		$this->hit('wins', $match);
		$this->hit('undefeated', $match);
		$this->miss('winless', $match);
		$this->miss('defeats', $match);
	}

	public function lose($match)
	{
		$this->hit('apps', $match);
		$this->hit('defeats', $match);
		$this->hit('winless', $match);
		$this->miss('wins', $match);
		$this->miss('undefeated', $match);
	}

	public function draw($match)
	{
		$this->hit('apps', $match);
		$this->hit('undefeated', $match);
		$this->hit('winless', $match);
		$this->miss('wins', $match);
		$this->miss('defeats', $match);
	}

	public function void($match)
	{
		$this->hit('apps', $match);
	}

	public function noShow($match)
	{
		$this->miss('apps', $match);
	}

	protected function hit($type, $match)
	{
		$this->currentStreak($type, $match)->increment();
	}

	protected function miss($type, $match)
	{
		if ($this->currentStreak[$type])
			$this->clearCurrentStreak($type, $match);
	}

	protected function currentStreak($type, $match = null)
	{
		if ($this->currentStreak[$type]) return $this->currentStreak[$type];

		$this->currentStreak[$type] = (new Streak($match->date))->tap(function($streak) use($type) {
			if (!$this->streaks[$type]) $this->streaks[$type] = []
			$this->streaks[$type][] = $streak
		});

		return $this->currentStreak[$type];
	}

	protected function clearCurrentStreak($type, $match)
	{
		$this->currentStreak[$type]->finish($match->date);
		$this->currentStreak[$type] = null;
	}
}
