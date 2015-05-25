@extends('layouts.default')

@section('content')
<h1>
	{!! link_to_route('players.index', 'Players') !!}
	>
	{{ $player->first_name }} {{ $player->last_name }}
</h1>

<h2>Teammates</h2>

<table class="leaderboard">
	<thead>
		<tr>
			<th colspan="5"></th>
			<th colspan="2" class="handicap">Handicap</th>
			<th></th>
		</tr>
		<tr>
			<th>Player</th>
			<th>Apps</th>
			<th>Wins</th>
			<th>Losses</th>
			<th>Win %</th>
			<th>Apps</th>
			<th>Wins</th>
			<th>Last App</th>
		</tr>
	</thead>
	@foreach ($teammates as $teammate)
	<tr>
		<td class="name">
			{!! link_to_route('players.show', $teammate->first_name . ' ' . $teammate->last_name, $teammate->id) !!}
		</td>
		<td>{{ $teammate->apps }}</td>
		<td>{{ $teammate->wins }}</td>
		<td>{{ $teammate->losses }}</td>
		<td>{{ round($teammate->win_percentage, 1) }}%</td>
		<td>{{ $teammate->handicap_apps }}</td>
		<td>{{ $teammate->handicap_wins }}</td>
		<td>{{ $teammate->last_app }}</td>
	</tr>
	@endforeach
</table>

<h2>Appearances ({{ $matches->count() }})</h2>

<ol class="matches">
@foreach($matches as $team)
	<li>
		{!! link_to_route('matches.show', $team->match->date->format('jS F Y'), $team->match_id) !!}
		- {{ $team->result() }}
	</li>
@endforeach
</ol>
@stop