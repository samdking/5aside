@extends('layouts.default')

@section('content')

<h1>
	{!! link_to_route('matches.index', 'Matches') !!}
	>
	{{ $match->date->format('jS F Y') }}
</h1>

@foreach($match->teams as $i => $team)
	<div class="team {{ $team->winners ? 'winners' : ''}}">
		<ul>
		@foreach($team->players as $player)
			<li>
				{!! link_to_route('players.show', $player->last_name, $player->id) !!}<!--
				@if ($player->first_name)
					-->, {{ $player->first_name }}<!--
				@endif-->
			</li>
		@endforeach
		</ul>
	</div>
	@if ($i === 0)
	<span class="vs">vs.</span>
	@endif
@endforeach

<h2>First Appearances</h2>

<ul>
	@foreach($match->firstAppearances() as $player)
		<li>
			{!! link_to_route('players.show', $player->last_name, $player->id) !!}<!--
			@if ($player->first_name)
				-->, {{ $player->first_name }}<!--
			@endif-->
		</li>
	@endforeach
</ul>

@stop