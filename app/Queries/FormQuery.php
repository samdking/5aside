<?php

namespace App\Queries;

class FormQuery
{
	public function __construct($request)
	{
		$this->request = clone $request;
		$this->request->show = 6;
		$this->request->descending = true;

		$this->matches = (new MatchQuery($this->request))->get();
	}

	public function forPlayer($player)
	{
		return $this->matches->map(function($match) use ($player) {
			$team = collect(['a', 'b'])->first(function($i, $letter) use ($match, $player) {
				return collect($match->{'team_' . $letter})->pluck('id')->contains($player);
			});

			if (!$team)
				return '';
			elseif ($match->winner == strtoupper($team))
				return 'W';
			elseif ($match->winner)
				return 'L';
			else
				return 'D';
		});
	}
}
