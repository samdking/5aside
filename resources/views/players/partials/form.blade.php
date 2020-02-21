<td class="form">
	<table class="form-table">
		<tr>
			@foreach($matches as $match)
				<td>
					@if ($team = $match->teamPlayedIn($player))
						{!! link_to_route('matches.show', substr($match->resultForTeam($team), 0, 1), $match->id, [
							'title' => $match->overviewForTeam($team),
							'class' => 'match ' . strtolower($match->resultForTeam($team))
						]) !!}
					@else
						<span class="match absense"></span>
					@endif
				</td>
			@endforeach
		</tr>
	</table>
</td>
