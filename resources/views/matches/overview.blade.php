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
			@unless($loop->last)
				<br><br>
			@endunless
		@endforeach
	</fieldset>
</form>

<div class="matches">
@foreach($matches as $match)
	<div class="match{{ $match->voided ? ' void' : '' }}">
		<a class="date" href="{{ route('matches.show', $match->id) }}">
			{{ DateTime::createFromFormat('Y-m-d', $match->date)->format('D jS F Y') }}
			({{ $match->venue }})
		</a>

		@include('matches.partials.team', [
			'scored' => $match->voided ? 'V' : $match->team_a_scored,
			'winners' => $match->winner == 'A',
			'players' => $match->team_a,
		])

		<div class="vs">vs.</div>

		@include('matches.partials.team', [
			'scored' => $match->voided ? 'V' : $match->team_b_scored,
			'winners' => $match->winner == 'B',
			'players' => $match->team_b,
		])
	</div>
@endforeach
</li>

@stop
