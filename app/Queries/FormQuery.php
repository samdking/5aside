<?php

namespace App\Queries;

use Carbon\Carbon;

class FormQuery
{
	protected $request;
	protected $query = null;

	public function __construct($request)
	{
		$request->form_matches = $request->get('form_matches', 6);
		$request->order = 'desc';
		$request->hide_teams = false;

		$this->request = $request;
		$this->matches = new MatchQuery($request);
	}

	public function getForPlayer($player)
	{
		return $this->matches->get()->sortBy('date')->map(function($match) use ($player) {
			$played = $match->team_a->merge($match->team_b)->map->id->contains($player->id);

			if (!$played) return;

			if ($match->voided) {
				$result = 'Void';
			} elseif (!$match->winner) {
				$result = 'Draw';
			} elseif ($match->winner == 'A') {
				$result = $match->team_a->map->id->contains($player->id) ? 'Win' : 'Loss';
			} elseif ($match->winner == 'B') {
				$result = $match->team_b->map->id->contains($player->id) ? 'Win' : 'Loss';
			}

			if ($this->request->short_form) return $result;

			return (object)[
				'result' => $result,
				'id' => $match->id,
				'date' => new Carbon($match->date),
				'team_a_scored' => $match->team_a_scored,
				'team_b_scored' => $match->team_b_scored,
			];
		})->values();
	}
}
