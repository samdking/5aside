@extends('layouts.default')

@section('content')

<h1>Player Leaderboard</h1>

<table class="leaderboard">
	<thead>
		<tr>
			<th colspan="8"></th>
			<th colspan="2" class="handicap">Handicap</th>
			<th colspan="2"></th>
		</tr>
		<tr>
			<th class="number">#</th>
			<th>Name</th>
			<th>Apps</th>
			<th>Wins</th>
			<th>Draws</th>
			<th>Losses</th>
			<th>Pts</th>
			<th>Win %</th>
			<th>Apps</th>
			<th>Wins</th>
			<th>Last App</th>
			<th>Form</th>
		</tr>
	</thead>
	<tbody>
		@foreach($players as $i => $player)
		<tr>
			<td class="number">{{ $i+1 }}</td>
			<td class="name">
				<a href="{{ route('players.show', $player->id) }}">
					{{ $player->first_name . ' ' . $player->last_name }}
				</a>
			</td>
			<td>{{ $player->played }}</td>
			<td>{{ $player->wins }}</td>
			<td>{{ $player->draws }}</td>
			<td>{{ $player->losses }}</td>
			<td>{{ ($player->wins * 3) + $player->draws }}</td>
			<td>{{ $player->win_percentage }}%</td>
			<td>{{ $player->handicap_apps }}</td>
			<td>{{ $player->handicap_wins }}</td>
			<td>
				<a href="{{ route('matches.show', $player->last_app_id) }}">
					{{ $player->last_app }}
				</a>
			</td>
			<td class="form">
				<table class="form-table">
					<tr>
						@foreach($matches as $match)
							<td>
								@if ($team = $player->playedIn($match))
									{!! link_to_route('matches.show', substr($team->result(), 0, 1), $match->id, [
										'title' => $team->match->date->format('j F Y'),
										'class' => 'match ' . strtolower($team->result())
									]) !!}
								@else
									<span class="match absense"></span>
								@endif
							</td>
						@endforeach
					</tr>
				</table>
			</td>
		</tr>
		@endforeach
	</tbody>
</table>

@stop
