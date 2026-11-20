<div class="season-block {{ $modifier ?? '' }}">
	<a class="season-block__title" href="{{ $href }}">
		{{ $title }}
		@if($inProgress)
			<small>in progress</small>
		@endif
	</a>

	<dl class="season-block__stats">
		<div><dt>Matches</dt><dd>{{ $season['total_matches'] }}</dd></div>
		<div><dt>Players</dt><dd>{{ $season['distinct_players'] }}</dd></div>
		<div><dt>Avg players</dt><dd>{{ $season['average_players'] }}</dd></div>
		<div><dt>Goals / match</dt><dd>{{ $season['average_goals'] }}</dd></div>
		<div><dt>Highest score</dt><dd>{{ $season['highest_scoring_match'] }}</dd></div>
		<div><dt>Avg age</dt><dd>{{ $season['average_age'] }}</dd></div>
	</dl>

	@if($champion)
		<div class="season-block__champion">
			<span class="season-block__champion-label">{{ $inProgress ? 'Leading' : 'Champion' }}</span>
			<a href="{{ route('players.show', $champion->id) }}">{{ $champion->first_initial }} {{ $champion->last_name }}</a>
			<span class="season-block__points">{{ $champion->points }} pts</span>
		</div>
	@endif
</div>
