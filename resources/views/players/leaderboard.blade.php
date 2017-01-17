@extends('layouts.default')

@section('content')

<h2>
	{{ $heading or 'Player Leaderboard' }}
</h2>

<table class="leaderboard">
	<thead>
		<tr>
			<th colspan="11"></th>
			<th colspan="3" class="handicap">Handicap</th>
			<th colspan="3" class="handicap">Advantage</th>
			<th colspan="3" class="handicap">Per Game</th>
			<th colspan="2"></th>
		</tr>
		<tr>
			<th class="number">#</th>
			<th>Name</th>
			<th>Pld</th>
			<th>W</th>
			<th>D</th>
			<th>L</th>
			<th>F</th>
			<th>A</th>
			<th>GD</th>
			<th>Pts</th>
			<th>Win %</th>
			<th>Pld</th>
			<th>W</th>
			<th>L</th>
			<th>Pld</th>
			<th>W</th>
			<th>L</th>
			<th>Pts</th>
			<th>F</th>
			<th>A</th>
			<th>Last App</th>
			<th>Form</th>
		</tr>
	</thead>
	<tbody>
		@foreach($players as $i => $player)
		<tr>
			<td class="number">{{ $i+1 }}</td>
			<td class="name">
				<a href="{{ route('players.show', [$player->id] + Request::all()) }}">
					{{ $player->first_name . ' ' . $player->last_name }}
				</a>
			</td>
			<td>{{ $player->played }}</td>
			<td>{{ $player->wins }}</td>
			<td>{{ $player->draws }}</td>
			<td>{{ $player->losses }}</td>
			<td>{{ $player->goals_for }}</td>
			<td>{{ $player->goals_against }}</td>
			<td>{{ $player->diff }}</td>
			<td>{{ ($player->wins * 3) + $player->draws }}</td>
			<td>{{ $player->win_percentage }}%</td>
			<td>{{ $player->handicap_apps }}</td>
			<td>{{ $player->handicap_wins }}</td>
			<td>{{ $player->handicap_losses }}</td>
			<td>{{ $player->advantage_apps }}</td>
			<td>{{ $player->advantage_wins }}</td>
			<td>{{ $player->advantage_losses }}</td>
			<td>{{ round((($player->wins * 3) + $player->draws) / $player->played, 2) }}
			<td>{{ round($player->gspg, 1) }}</td>
			<td>{{ round($player->gcpg, 1) }}</td>
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
								@if ($team = $match->teamPlayedIn($player))
									{!! link_to_route('matches.show', substr($team->result(), 0, 1), $match->id, [
										'title' => $match->overviewForTeam($team),
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
