<?php

namespace App;

class PlayerStreakCruncher
{
	public $counts = [];
	public $fromDates = [];
	protected $toDates = [];
	public $maxCounts = [];
	public $streaks = [];

	public function __construct($players)
	{
		$this->players = $players;
	}

	public function crunch($matches)
	{
		foreach($matches as $match) {
			$this->match($match);
		}

		foreach($this->counts as $playerId => $count) {
			$this->log($playerId);
		}

		arsort($this->maxCounts);
	}

	public function match($match)
	{
		$participants = $match->participants()->pluck('id');

		foreach($participants as $participant) {
			$this->appearance($participant, $match);
		}

		foreach($this->players as $id => $player) {
			if (! $participants->contains($id)) {
				$this->miss($id);
			}
		}
	}

	public function appearance($playerId, $match)
	{
		if (array_key_exists($playerId, $this->counts)) {
			$this->counts[$playerId]++;
			$this->toDates[$playerId] = $match->date;
		} else {
			$this->counts[$playerId] = 1;
			$this->fromDates[$playerId] = $match->date;
		}
	}

	public function miss($playerId)
	{
		if ( ! array_key_exists($playerId, $this->counts)) return;

		$this->log($playerId);

		unset($this->counts[$playerId]);
	}

	protected function log($playerId)
	{
		$this->logStreak($playerId);
		$this->logMaxCount($playerId);
	}

	protected function logStreak($playerId)
	{
		if ($this->counts[$playerId] < max($this->counts)) return;

		$this->streaks[] = [
			'player' => $this->players[$playerId]->fullName(),
			'count' => $this->counts[$playerId],
			'from' => $this->fromDates[$playerId],
			'to' => $this->toDates[$playerId],
		];
	}

	protected function logMaxCount($playerId)
	{
		$this->maxCounts[$playerId] = max($this->maxCountForPlayer($playerId), $this->counts[$playerId]);
	}

	protected function maxCountForPlayer($playerId)
	{
		if (array_key_exists($playerId, $this->maxCounts)) return $this->maxCounts[$playerId];
	}
}
