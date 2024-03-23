@extends('layouts.default')

@section('content')

<h2>
	{{ $heading ?? 'Player Leaderboard' }}
</h2>

<table class="leaderboard">
	<thead>
		<tr class="js-expanded-row">
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
			<th class="js-expanded">Pld</th>
			<th class="js-expanded">W</th>
			<th class="js-expanded">L</th>
			<th class="js-expanded">Pld</th>
			<th class="js-expanded">W</th>
			<th class="js-expanded">L</th>
			<th class="js-expanded">Pts</th>
			<th class="js-expanded">F</th>
			<th class="js-expanded">A</th>
			<th>Last App</th>
			<th>Form</th>
		</tr>
	</thead>
	<tbody>
		@foreach($players as $i => $player)
		<tr class="{{ $player->form->filter()->isEmpty() || $player->last_name == '(anon)' ? "js-inactive" : "" }}">
			<td class="number">{{ $i+1 }}</td>
			<td class="name">
				<a href="{{ route('players.show', [$player->id] + Request::only('from', 'to', 'year')) }}">
					{{ $player->first_name . ' ' . $player->last_name }}
				</a>
			</td>
			<td>{{ $player->matches }}</td>
			<td>{{ $player->wins }}</td>
			<td>{{ $player->draws }}</td>
			<td>{{ $player->losses }}</td>
			<td>{{ $player->scored }}</td>
			<td>{{ $player->conceded }}</td>
			<td>{{ $player->gd }}</td>
			<td>{{ $player->points }}</td>
			<td>{{ $player->win_percentage }}%</td>
			<td class="js-expanded">{{ $player->handicap['matches'] }}</td>
			<td class="js-expanded">{{ $player->handicap['wins'] }}</td>
			<td class="js-expanded">{{ $player->handicap['losses'] }}</td>
			<td class="js-expanded">{{ $player->advantage['matches'] }}</td>
			<td class="js-expanded">{{ $player->advantage['wins'] }}</td>
			<td class="js-expanded">{{ $player->advantage['losses'] }}</td>
			<td class="js-expanded">{{ round($player->per_game['points'], 2) }}
			<td class="js-expanded">{{ round($player->per_game['scored'], 1) }}</td>
			<td class="js-expanded">{{ round($player->per_game['conceded'], 1) }}</td>
			<td>
				<a href="{{ route('matches.show', $player->last_app_id) }}">
					{{ $player->last_appearance }}
				</a>
			</td>
			@include('players/partials/form', ['player' => $player])
		</tr>
		@endforeach
	</tbody>
</table>

<div class="leaderboard-controls">
	<a onclick="document.querySelector('.leaderboard').classList.toggle('js-expanded-table')">Toggle expanded table</a>
	|
	<a onclick="document.querySelector('.leaderboard').classList.toggle('js-inactive-players-table')">Toggle active players</a>
</div>

@stop
