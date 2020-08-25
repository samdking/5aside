@extends('layouts.default')

@section('content')

<h2>Matches ({{ $matches->count() }})</h2>

<form>
	<fieldset style="margin: 1em 0">
		<legend>Teammates</legend>
		@foreach($players->groupBy('recent') as $players)
			@foreach($players as $player)
				<label style="margin-right: 8px">
					{!! Form::checkbox('teammates[]',
						$player->id,
						in_array($player->id, Request::get('teammates', [])),
						['onclick' => 'this.form.submit()']
					) !!}
					{{ $player->shortName() }}
				</label>
			@endforeach
			@unless($loop.last)
				<br><br>
			@endunless
		@endforeach
	</fieldset>
</form>

<div class="matches">
@foreach($matches as $match)
	<div class="match{{ $match->is_void ? ' void' : '' }}">
		<a class="date" href="{{ route('matches.show', $match->id) }}">
			{{ $match->date->format('D jS F Y') }}
			@if ($match->venue)
				({{ $match->venue->name }})
			@endif

			@if ($match->is_short)
				<small>SHORT</small>
			@endif
		</a><!--
		@foreach($match->teams as $i => $team)
			--><div class="team{{ $team->winners ? ' winners' : '' }}">
				<h2 class="scored">{{ $match->is_void ? "V" : $team->scored }}</h2>
				<ul>
				@foreach($team->players as $player)
					<li>
						<a href="{{ route('players.show', $player->id) }}">{{ $player->last_name ?: $player->first_name }}</a>
						@if ($player->pivot->injured)
							<span class="player--injured">(injured)</span>
						@endif
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
