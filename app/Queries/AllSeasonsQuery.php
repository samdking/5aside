<?php

namespace App\Queries;

use Illuminate\Http\Request;

class AllSeasonsQuery
{
	protected $request;

	public function __construct(Request $request)
	{
        $this->request = $request;
        $this->seasons = new SingleSeasonQuery($request);
	}

	public function get()
	{
        $query = <<<SQL
		SELECT YEAR(date) year FROM matches GROUP BY year
SQL;

        $seasons = collect(\DB::select($query))->prepend((object)['year' => null]);

        return $seasons->map(function($season) {
            return $this->seasons->get($season->year);
        })->keyBy('year');
	}
}
