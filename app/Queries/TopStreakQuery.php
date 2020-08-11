<?php

namespace App\Queries;

use Illuminate\Http\Request;

class TopStreakQuery
{
	protected $request;
	protected $streaks;

	public function __construct(Request $request)
	{
		$this->request = $request;
		$this->streaks = new PlayerStreakQuery($request);
	}

	public function get()
	{
		return $this->streaks->get()->get('all')->map(function($ps) {
			return $ps->sortedStreaksForType($this->type())->each(function($streak) use ($ps) {
				$streak->player = $ps->player->toArray();
			});
		})->flatten()->groupBy('count')->sortKeys()->reverse()->slice(0, $this->limit())->flatten()
			->reduce(function($rankings, $streak) {
				$equalToPrevious = count($rankings) && $streak->count == last($rankings)->count;

				if (count($rankings) < $this->limit() || $equalToPrevious) {
					$streak->rank = $equalToPrevious ? last($rankings)->rank : count($rankings) + 1;
					$rankings[] = $streak;
				}

				return $rankings;
			}, []);
	}

	public function type()
	{
		return $this->request->get('type', 'apps');
	}

	protected function limit()
	{
		return $this->request->get('limit', 5);
	}
}