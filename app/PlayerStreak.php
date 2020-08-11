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
		$this->id = $player->id;
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

	public function sortedStreaksForType($type)
	{
		if (! array_key_exists($type, $this->streaks)) return collect();

		return collect($this->streaks[$type])->sortByDesc('count')->values();
	}

	public function active()
	{
		return array_key_exists("apps", $this->streaks);
	}

	protected function hit($type, $match)
	{
		$this->currentStreak($type, $match)->extend($match->date);
	}

	protected function miss($type, $match)
	{
		if ($this->onCurrentStreak($type))
			$this->clearCurrentStreak($type);
	}

	protected function currentStreak($type, $match)
	{
		if ($this->onCurrentStreak($type)) {
			return $this->currentStreak[$type];
		}

		return tap(new Streak($match->date), function($streak) use ($type) {
			$this->currentStreak[$type] = $streak;
			$this->streaks[$type][] = $streak;
		});
	}

	protected function onCurrentStreak($type)
	{
		return array_key_exists($type, $this->currentStreak);
	}

	protected function clearCurrentStreak($type)
	{
		unset($this->currentStreak[$type]);
	}
}
