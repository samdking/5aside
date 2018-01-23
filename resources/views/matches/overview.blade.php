@extends('layouts.default')

@section('content')

<h2>Matches ({{ $matches->count() }})</h2>

<form>
	<fieldset style="margin: 1em 0">
		<legend>Teammates</legend>
		@foreach($players as $player)
			<label>
				{!! Form::checkbox('teammates[]',
					$player->id,
					in_array($player->id, Request::get('teammates', [])),
					['onclick' => 'this.form.submit()']
				) !!}
				{{ $player->last_name }}
			</label>
		@endforeach
	</fieldset>
</form>

<div class="matches">
@foreach($matches as $match)
	<div class="match">
		<a class="date" href="{{ route('matches.show', $match->id) }}">
			{{ $match->date->format('D jS F Y') }}
			@if ($match->venue)
				- {{ $match->venue->name }}
			@endif

			@if ($match->is_short)
				<small>SHORT</small>
			@endif
		</a><!--
		@foreach($match->teams as $i => $team)
			--><div class="team{{ $team->winners ? ' winners' : '' }}">
				<h2 class="scored">{{ $team->scored }}</h2>
				<ul>
				@foreach($team->players as $player)
					<li>
						<a href="{{ route('players.show', $player->id) }}">{{ $player->last_name }}</a>
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