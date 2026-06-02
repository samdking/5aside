<?php

namespace App\Queries;

use Carbon\Carbon;
use Illuminate\Support\Fluent;

class FormQuery
{
	protected $request;
	protected $query = null;

	public function __construct($request)
	{
		$params = new Fluent($request->all());

		$params['form_matches'] = $request->get('form_matches', 6);
		$params['order'] = 'desc';
		$params['hide_teams'] = false;

		$this->request = $request;
		$this->matches = new MatchQuery($params);
	}

	public function getForPlayer($player)
	{
		$sort = $this->useShortForm() ? 'sortByDesc' : 'sortBy';

		return $this->matches->get()->$sort('date')->map(function($match) use ($player) {
			$inTeamA = $match->team_a->map->id->contains($player->id);
			$inTeamB = $match->team_b->map->id->contains($player->id);
			$played = $inTeamA || $inTeamB;

			// For backwards compatibility reasons, we return an empty string rather
			// than null when using short form (used in API response)
			if (!$played) return $this->useShortForm() ? '' : null;

			if ($match->voided) {
				$result = 'Void';
			} elseif (!$match->winner) {
				$result = 'Draw';
			} elseif ($match->winner == 'A') {
				$result = $inTeamA ? 'Win' : 'Loss';
			} elseif ($match->winner == 'B') {
				$result = $inTeamB ? 'Win' : 'Loss';
			}

			if ($this->useShortForm()) return $result;

			return (object)[
				'result' => $result,
				'id' => $match->id,
				'date' => new Carbon($match->date),
				'teammates' => $inTeamA ? $match->team_a : $match->team_b,
				'opponents' => $inTeamA ? $match->team_b : $match->team_a,
				'team_a_scored' => $match->team_a_scored,
				'team_b_scored' => $match->team_b_scored,
			];
		})->values();
	}

	protected function useShortForm() {
		return $this->request->short_form;
	}
}
