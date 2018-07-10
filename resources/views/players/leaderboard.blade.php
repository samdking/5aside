@extends('layouts.default')

@section('content')

<h2>
	{{ $heading or 'Player Leaderboard' }}
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
		<tr class="{{ $matches->first()->date->greaterThan($player->last_app) ? "js-inactive" : "" }}">
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
			<td class="js-expanded">{{ $player->handicap_apps }}</td>
			<td class="js-expanded">{{ $player->handicap_wins }}</td>
			<td class="js-expanded">{{ $player->handicap_losses }}</td>
			<td class="js-expanded">{{ $player->advantage_apps }}</td>
			<td class="js-expanded">{{ $player->advantage_wins }}</td>
			<td class="js-expanded">{{ $player->advantage_losses }}</td>
			<td class="js-expanded">{{ round((($player->wins * 3) + $player->draws) / $player->played, 2) }}
			<td class="js-expanded">{{ round($player->gspg, 1) }}</td>
			<td class="js-expanded">{{ round($player->gcpg, 1) }}</td>
			<td>
				<a href="{{ route('matches.show', $player->last_app_id) }}">
					{{ $player->last_app }}
				</a>
			</td>
			@include('players/partials/form', ['matches' => $matches, 'player' => $player])
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
