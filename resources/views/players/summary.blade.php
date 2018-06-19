@extends('layouts.default')

@section('content')

<dl class="stats">
	<dt>
		<strong>Total matches:</strong>
	</dt>
	<dd>
		{{ $stats->total_matches }}
	</dd>
	<dt>
		<strong>Highest attendance:</strong>
	</dt>
	<dd>
		{{ $stats->highest_attendance->first()->total_players }} ({{ $stats->highest_attendance->map(function($match) {
			return $match->date->format('j M Y');
		})->implode(', ') }})
	</dd>
	<dt>
		<strong>Most appearances:</strong>
	</dt>
	<dd>
		{{ $stats->most_appearances->map(function($player) {
			return $player->first_name . ' ' . $player->last_name . ' - ' . $player->apps;
		})->implode(', ') }}
	</dd>
	<dt>
		<strong>Most wins:</strong>
	</dt>
	<dd>
		{{ $stats->most_wins->map(function($player) {
			return $player->first_name . ' ' . $player->last_name . ' - ' . $player->wins . ' (' . $player->match_count .' apps)';
		})->implode(', ') }}
	</dd>
	<dt>
		<strong>Highest Win % (over {{ $stats->average_attendance }} apps):</strong>
	</dt>
	<dd>
		{{ $stats->highest_win_percentage->first_name }} {{ $stats->highest_win_percentage->last_name }} - {{ $stats->highest_win_percentage->win_percentage }}% ({{ $stats->highest_win_percentage->matches}} apps)
	</dd>
</dl>

@stop
