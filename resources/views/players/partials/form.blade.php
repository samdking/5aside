<td class="form">
	<table class="form-table">
		<tr>
			@foreach($matches as $match)
				<td>
					@if ($team = $match->teamPlayedIn($player))
						{!! link_to_route('matches.show', substr($team->result(), 0, 1), $match->id, [
							'title' => $match->overviewForTeam($team),
							'class' => 'match ' . strtolower($team->result())
						]) !!}
					@else
						<span class="match absense"></span>
					@endif
				</td>
			@endforeach
		</tr>
	</table>
</td>
