@extends('layouts.default')

@section('content')
<h2>
	{!! link_to_route('players.index', 'Players') !!}
	&gt;
	{{ $player->first_name }} {{ $player->last_name }}
</h2>

<table class="leaderboard">
	<thead>
		<tr>
			<th colspan="10"></th>
			<th colspan="3" class="handicap">Handicap</th>
			<th colspan="3" class="handicap">Advantage</th>
			<th colspan="3" class="handicap">Per Game</th>
			<th colspan="2"></th>
		</tr>
		<tr>
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
		<tr>
			<td class="name">
				<a href="{{ route('players.show', $player->id) }}">
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
			@include('players/partials/form', ['matches' => $matchesForForm, 'player' => $player])
		</tr>
	</tbody>
</table>


<h3>Teammates</h3>

@include('players.partials.leaderboard', ['method' => 'teamPlayedWith', 'mainPlayer' => $player, 'players' => App\Player::hydrate($teammates)])

<h3>Opponents</h3>

@include('players.partials.leaderboard', ['method' => 'teamPlayedAgainst', 'mainPlayer' => $player, 'players' => App\Player::hydrate($opponents)])

<h3>Appearances ({{ $matches->count() }})</h3>

<ol class="matches">
@foreach($matches as $team)
	<li>
		{!! link_to_route('matches.show', $team->match->date->format('jS F Y'), $team->match_id) !!}
		@if ($team->match->is_void)
			- Void
		@else
			- {{ $team->result() }}
			@if ($team->scored)
				({{ $team->scored }}-{{ $team->match->getOpposition($team)->scored }})
			@endif
		@endif

		@if ($team->match->is_short)
			*
		@endif
	</li>
@endforeach
</ol>

<h3 id="stats">Played with / against (minimum <strong>{{ round($matches->count() / 4) }}</strong> matches)</h3>

<div class="stats">
@foreach($stats as $player)
	<div class="player">
		<span class="info" style="left: 5px">{{ $player->with }}</span>
		<span class="info" style="right: 5px">{{ $player->against }}</span>
		<span class="info" style="text-align: center; width: 100%">
			<a href="{{ route('players.show', [$player->id] + Request::all()) }}#stats" style="padding: 2px 4px; color: #FFF; background: rgba(0, 0, 0, 0.6); font-size: 14px">{{ $player->player }}</a>
		</span>
		<span class="bar" style="width: {{ $player->percentage }}%; background: green"></span>
		<span class="bar" style="width: {{ 100 - $player->percentage }}%; background: red"></span>
	</div>
@endforeach
</div>

@stop
