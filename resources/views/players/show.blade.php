@extends('layouts.default')

@section('content')
<h2>
	<a href="{{ route('players.index') }}">Players</a>
	&gt;
	{{ $player->first_initial }} {{ $player->last_name }}
</h2>

@php
	$medals = [
		1 => ['label' => '1st', 'class' => 'gold'],
		2 => ['label' => '2nd', 'class' => 'silver'],
		3 => ['label' => '3rd', 'class' => 'bronze'],
	];

	// Only count finishes from completed seasons (exclude the current, in-progress year).
	$podiums = $player->seasons->filter(fn($s, $year) => $year < now()->year && $s->ranking >= 1 && $s->ranking <= 3);
@endphp

@if ($podiums->isNotEmpty())
	<div class="badges">
		@foreach ($medals as $rank => $medal)
			@foreach ($podiums->filter(fn($s) => $s->ranking == $rank)->sortKeys() as $year => $season)
				<span class="badge badge--{{ $medal['class'] }}" title="{{ $medal['label'] }} place in {{ $year }}">
					{{ $medal['label'] }} <span class="badge__year">{{ $year }}</span>
				</span>
			@endforeach
		@endforeach
	</div>
@endif

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
					{{ $player->first_initial . ' ' . $player->last_name }}
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
			<td>{{ $player->handicap['matches'] }}</td>
			<td>{{ $player->handicap['wins'] }}</td>
			<td>{{ $player->handicap['losses'] }}</td>
			<td>{{ $player->advantage['matches'] }}</td>
			<td>{{ $player->advantage['wins'] }}</td>
			<td>{{ $player->advantage['losses'] }}</td>
			<td>{{ round($player->per_game['points'], 2) }}
			<td>{{ round($player->per_game['scored'], 1) }}</td>
			<td>{{ round($player->per_game['conceded'], 1) }}</td>
			<td>
				<a href="{{ route('matches.show', $player->last_app_id) }}">
					{{ $player->last_appearance }}
				</a>
			</td>
			@include('players/partials/form', ['player' => $player])
		</tr>
	</tbody>
</table>

<h3>Teammates</h3>

@include('players.partials.leaderboard', ['method' => 'teammates', 'players' => $teammates])

<h3>Opponents</h3>

@include('players.partials.leaderboard', ['method' => 'opponents', 'players' => $opponents])

<h3>Appearances ({{ $player->results->count() }})</h3>

<ol class="matches">
@foreach($player->results as $match)
	<li>
		<a href="{{ route('matches.show', $match->id) }}">{{ DateTime::createFromFormat('Y-m-d', $match->date)->format('jS F Y') }}</a>
		@if ($match->voided)
			- Void
		@else
			- {{ $match->result }}
			@if ($match->scored)
				({{ $match->scored }}-{{ $match->conceded }})
			@endif
		@endif
	</li>
@endforeach
</ol>

<h3 id="stats">Played with / against (minimum <strong>{{ round($player->results->count() / 4) }}</strong> matches)</h3>

<div class="stats">
@foreach($stats as $player)
	<div class="player">
		<span class="info" style="text-align: center; width: 100%">
			<a href="{{ route('players.show', [$player->id] + Request::only('from', 'to', 'year')) }}#stats" style="padding: 2px 4px; color: #FFF; background: rgba(0, 0, 0, 0.6); font-size: 14px">{{ $player->first_name }} {{ $player->last_name }}</a>
		</span>
		<span class="bar with" style="width: {{ $player->percentage }}%">{{ $player->with }}</span>
		<span class="bar against" style="width: {{ 100 - $player->percentage }}%">{{ $player->against }}</span>
	</div>
@endforeach
</div>

@stop
