<?php

namespace App\Queries;

class CancelledMatchQuery
{
	protected $request;
	protected $query;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function get()
	{
		if (is_null($this->query)) {
			$this->query = $this->query();
		}

		return $this->query;
	}

	public function forPlayer($id, $year = null)
	{
		return $this->get()->filter(function($match) use ($id, $year) {
			return $match->players->contains($id) && (is_null($year) || $match->year == $year);
		})->values();
	}

	/**
	 * Cancelled matches with the players who put their name down as
	 * [id, name] pairs, sorted by name, in place of plain ids.
	 */
	public function withPlayerNames()
	{
		$players = $this->playerNames($this->get()->pluck('players')->flatten()->unique());

		return $this->get()->map(function($match) use ($players) {
			return (object)array_merge((array)$match, [
				'cancelled' => true,
				'players' => $players->whereIn('id', $match->players)->values(),
			]);
		});
	}

	protected function playerNames($ids)
	{
		if ($ids->isEmpty()) return collect();

		$placeholders = $ids->map(fn() => '?')->implode(', ');

		$query = <<<SQL
		SELECT
		  players.id,
		  CONCAT(LEFT(COALESCE(players.first_name, ''), 1), '. ', COALESCE(players.last_name, '')) AS name
		FROM players
		WHERE players.id IN ({$placeholders})
		ORDER BY players.last_name, players.first_name
SQL;

		return collect(\DB::select($query, $ids->values()->all()))
			->map(fn($p) => ['id' => $p->id, 'name' => $p->name]);
	}

	protected function query()
	{
		$query = <<<SQL
		SELECT
		  cancelled_matches.id,
		  cancelled_matches.date,
		  YEAR(cancelled_matches.date) AS year,
		  GROUP_CONCAT(cmp.player_id) AS players
		FROM cancelled_matches
		LEFT JOIN cancelled_match_player cmp ON cmp.cancelled_match_id = cancelled_matches.id
		WHERE date >= ? AND date <= ?
		GROUP BY cancelled_matches.id
		ORDER BY cancelled_matches.date, cancelled_matches.id
SQL;

		$placeholders = [
			(new Filters\FromDate)->get($this->request),
			(new Filters\ToDate)->get($this->request)
		];

		return collect(\DB::select($query, $placeholders))->each(function($match) {
			$match->year = (int)$match->year;
			$match->players = collect(array_filter(explode(',', (string)$match->players)))->map(fn($id) => (int)$id);
		});
	}
}
