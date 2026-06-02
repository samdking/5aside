<?php

namespace App;

class PlayerStreak
{
	public $player;
	public $id;
	protected $streaks = [];
	protected $currentStreak = [];

	public function __construct($player)
	{
		$this->player = $player;
		$this->id = is_string($player) ? $player : $player->id;
		$this->currentStreak = [];
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

	public function topStreaksByType()
	{
		return collect($this->streaks)->map(function($streaks, $type) {
			return collect($streaks)->groupBy('count')->sortKeys()->last();
		});
	}

	public function currentStreaks()
	{
		return $this->currentStreak;
	}

	protected function hit($type, $match)
	{
		$this->currentStreak($type, $match)->extend($match->date);
	}

	protected function miss($type, $match)
	{
		unset($this->currentStreak[$type]);
	}

	protected function currentStreak($type, $match)
	{
		if (array_key_exists($type, $this->currentStreak)) {
			return $this->currentStreak[$type];
		}

		return tap(new Streak($match->date), function($streak) use ($type) {
			$this->currentStreak[$type] = $streak;
			$this->streaks[$type][] = $streak;
		});
	}
}
