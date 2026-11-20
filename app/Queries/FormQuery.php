<?php

namespace App\Queries;

use Carbon\Carbon;
use Illuminate\Support\Fluent;

class FormQuery
{
	protected $request;
	protected $query = null;
	protected $limit;
	protected $cancelled;

	public function __construct($request)
	{
		$params = new Fluent($request->all());

		$params['form_matches'] = $request->get('form_matches', 6);
		$params['order'] = 'desc';
		$params['hide_teams'] = false;

		$this->request = $request;
		$this->limit = $params['form_matches'];
		$this->matches = new MatchQuery($params);
		$this->cancelled = new CancelledMatchQuery($params);
	}

	/**
	 * The most recent matches, including cancelled matches. MatchQuery already
	 * returns the latest N real matches, so the latest N of the combined list
	 * is correct.
	 */
	protected function matches()
	{
		$cancelled = $this->cancelled->get()->map(function($match) {
			return (object)[
				'date' => $match->date,
				'cancelled' => $match->players,
			];
		});

		return $this->matches->get()->concat($cancelled)->sortByDesc('date')->take($this->limit);
	}

	public function getForPlayer($player)
	{
		$sort = $this->useShortForm() ? 'sortByDesc' : 'sortBy';

		return $this->matches()->$sort('date')->map(function($match) use ($player) {
			if (isset($match->cancelled)) {
				if (!$match->cancelled->contains($player->id)) return $this->useShortForm() ? '' : null;

				if ($this->useShortForm()) return 'Cancelled';

				return (object)[
					'result' => 'Cancelled',
					'cancelled' => true,
					'date' => new Carbon($match->date),
					'teammates' => collect(),
					'opponents' => collect(),
					'team_a_scored' => null,
					'team_b_scored' => null,
				];
			}

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
