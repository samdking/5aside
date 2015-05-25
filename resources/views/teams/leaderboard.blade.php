@extends('layouts.default')

@section('content')

<h1>Teams Leaderboard</h1>

<table class="leaderboard">
	<tr>
		<th class="number">#</th>
		<th>Team</th>
		<th>Players</th>
		<th>Apps</th>
		<th>Wins</th>
		<th>Draws</th>
		<th>Handicap Apps</th>
		<th>Last App</th>
	</tr>
	@foreach($teams as $i => $team)
	<tr>
		<td class="number">{{ $i+1 }}
		<td class="name">
			{!! implode(', ', $team->players->map(function($player) {
				return link_to_route('players.show', $player->last_name, $player->id);
			})->all()) !!}
		</td>
		<td>{{ $team->player_count }}</td>
		<td>{{ $team->apps }}</td>
		<td>{{ $team->wins }}</td>
		<td>{{ $team->draws }}</td>
		<td>{{ App\Team::whereUniqueHash($team->unique_hash)->sum('handicap') }}</td>
		<td>{{ $team->last_app }}</td>
	</tr>
	@endforeach
</table>

@stop