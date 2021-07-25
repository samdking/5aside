<?php

namespace App;

use Illuminate\Support\Collection;

class MatchStats
{
    protected $matches;

	public function __construct(Collection $matches)
	{
        $this->matches = $matches;
    }

    public function get()
    {
        $data = (object)[];

        $data->total_goals = $this->matches->sum->total_goals;
        $data->average_goals = round($this->matches->filter(function($match) {
            return ! is_null($match->team_a_scored);
        })->average->total_goals, 2);
        $data->total_attendance = $this->matches->sum->total_players;
        $data->average_attendance = round($this->matches->average->total_players, 2);
        $data->highest_attendance = $this->matches->max->total_players;
        $data->highest_scoring_match = $this->matches->max->total_goals;

        $data->lowest_scoring_match = $this->matches
            ->reject(function($match) {
                return is_null($match->team_a_scored);
            })->reject(function($match) {
                return $match->voided;
            })->min->total_goals;

        $data->highest_attendance_matches = $this->matches->filter(function($match) use ($data) {
            return $match->total_players == $data->highest_attendance;
        })->reverse()->values();

        $data->highest_scoring_matches = $this->matches->filter(function($match) use ($data) {
            return $match->total_goals == $data->highest_scoring_match;
        })->reverse()->values();

        $data->lowest_scoring_matches = $this->matches->filter(function($match) use ($data) {
            return $match->total_goals == $data->lowest_scoring_match;
        })->reverse()->values();

        return $data;
    }
}