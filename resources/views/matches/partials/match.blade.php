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
