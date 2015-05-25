@extends('layouts.default')

@section('content')

<h1>Matches</h1>

<div class="matches">
@foreach($matches as $match)
	<div class="match">
		<a class="date" href="{{ route('matches.show', $match->id) }}">
			{{ $match->date->format('jS F Y') }}
		</a><!--
		@foreach($match->teams as $i => $team)
			--><div class="team{{ $team->winners ? ' winners' : '' }}">
				<ul>
				@foreach($team->players as $player)
					<li>
						<a href="{{ route('players.show', $player->id) }}">{{ $player->last_name }}</a>
						<span class="win-percentage">({{ $player->wins() }} wins)</span>
					</li>
				@endforeach
				</ul>
			</div><!--
			@if ($i === 0)
				--><div class="vs">vs.</div><!--
			@endif
		@endforeach
	--></div>
@endforeach
</li>

@stop