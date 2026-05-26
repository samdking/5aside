@extends('layouts.default')

@section('content')

<h2>Matches ({{ $matches->count() }})</h2>

<form>
<div class="filter-fieldsets">
	<fieldset class="filter-fieldset filter-fieldset--teammate">
		<legend>Teammates</legend>
		@foreach($players->groupBy('recent') as $recent => $scopedPlayers)
			@if($recent)
				@include('matches.partials.player-filter', ['field' => 'teammates', 'selected' => $teammates])
			@else
				<details style="margin-top: 8px">
					<summary style="cursor: pointer; color: #666">Inactive players</summary>
					<div style="margin-top: 6px">
						@include('matches.partials.player-filter', ['field' => 'teammates', 'selected' => $teammates])
					</div>
				</details>
			@endif
		@endforeach
	</fieldset>
	<fieldset class="filter-fieldset filter-fieldset--opponent">
		<legend>Opponents</legend>
		@foreach($players->groupBy('recent') as $recent => $scopedPlayers)
			@if($recent)
				@include('matches.partials.player-filter', ['field' => 'opponents', 'selected' => $opponents])
			@else
				<details style="margin-top: 8px">
					<summary style="cursor: pointer; color: #666">Inactive players</summary>
					<div style="margin-top: 6px">
						@include('matches.partials.player-filter', ['field' => 'opponents', 'selected' => $opponents])
					</div>
				</details>
			@endif
		@endforeach
	</fieldset>
</div>
</form>

<div class="matches-wrapper">
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
				'teammates' => $teammates,
				'opponents' => $opponents,
			])

			<div class="vs">vs.</div>

			@include('matches.partials.team', [
				'scored' => $match->voided ? 'V' : $match->team_b_scored,
				'winners' => $match->winner == 'B',
				'players' => $match->team_b,
				'highlightTeammates' => $teammates,
				'highlightOpponents' => $opponents,
			])
		</div>
	@endforeach
	</div>
</div>
@stop
