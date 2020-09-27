<?php

namespace App\Queries;

use DateTime;
use App\MatchStats;
use Illuminate\Http\Request;

class AllSeasonsQuery
{
	protected $request;
	protected $matches;
	protected $matchesByYear;

	public function __construct(Request $request)
	{
        $this->request = $request;
		$this->matches = new MatchQuery($request);
	}

	public function get()
	{
$query = <<<SQL
		SELECT
		  YEAR(date) AS year,
		  MIN(date) AS start_date,
		  MAX(date) AS end_date,
		  COUNT(id) AS total_matches,
		  AVG(player_count) AS average_players,
		  SUM(player_count) AS total_players,
		  SUM(anon_player_count) AS total_anon_players
		FROM matches
		INNER JOIN (
		  SELECT
		    CAST(SUM(players.last_name != '(anon)') AS SIGNED) AS player_count,
		    CAST(SUM(players.last_name = '(anon)') AS SIGNED) AS anon_player_count,
		    match_id
		  from player_team
		  JOIN players on players.id = player_team.player_id
		  JOIN teams on teams.id = player_team.team_id
		  group by match_id
		) pt ON pt.match_id = matches.id
        GROUP BY year
SQL;

		return collect(\DB::select($query))->map(function($season) {
            return collect($season)->map(function($value) {
				return is_numeric($value) ? (int)$value : $value;
			})
			->merge($this->statsForYear($season->year))
			->merge($this->matchesForYear($season->year))
            ->merge($this->endDate($season));
        })->keyBy('year');
	}

	protected function statsForYear($year)
	{
		return (new MatchStats($this->matches()->get($year)))->get();
	}

	protected function matchesForYear($year)
	{
        if ($this->request->hide_matches) return [];

		return [
			'matches' => $this->matches()->get($year)
		];
    }

	protected function endDate($season)
	{
		if ($this->seasonHasEnded($season)) return [];

		return [
			'end_date' => null
		];
	}

	protected function seasonHasEnded($season)
	{
		return $season->year && $season->year < (new DateTime)->format('Y');
    }

    private function matches()
    {
        if (is_null($this->matchesByYear)) {
            $this->matchesByYear = $this->matches->get()->groupBy('year');
        }

        return $this->matchesByYear;
    }
}
