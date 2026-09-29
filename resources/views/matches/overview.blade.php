@extends('layouts.default')

@section('content')

<h2>Matches ({{ $matchCount }}{{ $cancelledCount ? ", {$cancelledCount} cancelled" : '' }})</h2>

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
		@if (isset($match->cancelled))
			@include('matches.partials.cancelled-match', ['match' => $match])
		@else
			@include('matches.partials.match', ['match' => $match])
		@endif
	@endforeach
	</div>
</div>
@stop
