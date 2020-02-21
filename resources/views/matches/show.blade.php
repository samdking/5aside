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

<div class="match{{ $match->is_void ? ' void' : '' }}">
	@foreach($match->teams()->with('players.teams')->get() as $i => $team)
		<div class="team {{ $team->winners ? 'winners' : ''}}">
			<h2 class="scored">{{ $match->is_void ? "V" : $team->scored }}</h2>
			<ul>
			@foreach($team->players as $player)
				<li>
					{!! link_to_route('players.show', $player->last_name, $player->id) !!}<!--
					@if ($player->first_name)
						-->, {{ $player->first_name }}<!--
					@endif-->
					@if ($player->pivot->injured)
							<span class="player--injured">(injured)</span>
					@endif
				</li>
			@endforeach
			</ul>
		</div>
		@if ($i === 0)
		<span class="vs">vs.</span>
		@endif
	@endforeach
</div>

@stop
