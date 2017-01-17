@extends('layouts.default')

@section('content')

<h2>
	{!! link_to_route('matches.index', 'Matches') !!}
	>
	{{ $match->date->format('jS F Y') }}
	@if ($match->is_short)
		<small>(short)</small>
	@endif
</h2>

@foreach($match->teams()->with('players.teams')->get() as $i => $team)
	<div class="team {{ $team->winners ? 'winners' : ''}}">
		<h2 class="scored">{{ $team->scored }}</h2>
		<ul>
		@foreach($team->players as $player)
			<li>
				{!! link_to_route('players.show', $player->last_name, $player->id) !!}<!--
				@if ($player->first_name)
					-->, {{ $player->first_name }}<!--
				@endif-->
				<span class="win-percentage">({{ $player->wins() }} wins)</span>
			</li>
		@endforeach
		</ul>
	</div>
	@if ($i === 0)
	<span class="vs">vs.</span>
	@endif
@endforeach

@stop
