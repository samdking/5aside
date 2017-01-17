@extends('layouts.default')

@section('content')

<ul>
	<li>
		<strong>Total matches:</strong>
		{{ $stats->total_matches }}
	</li>
	<li>
		<strong>Highest attendance:</strong>
		{{ $stats->highest_attendance->total_players }} ({{ $stats->highest_attendance->date->format('jS F Y') }})
	<li>
		<strong>Most appearances:</strong>
		{{ $stats->most_appearances->first_name }} {{ $stats->most_appearances->last_name }} - {{ $stats->most_appearances->apps }}
	</li>
	<li>
		<strong>Most wins:</strong>
		{{ $stats->most_wins->first_name }} {{ $stats->most_wins->last_name }} - {{ $stats->most_wins->wins }}
	</li>
	<li>
		<strong>Highest Win % (over {{ $stats->average_attendance }} apps):</strong>
		{{ $stats->highest_win_percentage->first_name }} {{ $stats->highest_win_percentage->last_name }} - {{ $stats->highest_win_percentage->win_percentage }}%
	</li>
</ul>

@stop