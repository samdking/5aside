<td class="form">
	<table class="form-table">
		<tr>
			@foreach($player->form as $match)
				<td>
					@if ($match)
						{!! link_to_route('matches.show', substr($match->result, 0, 1), $match->id, [
							'title' => $match->date->format('j F Y') . ' ' . $match->team_a_scored . ' - ' . $match->team_b_scored,
							'class' => 'match ' . strtolower($match->result)
						]) !!}
					@else
						<span class="match absense"></span>
					@endif
				</td>
			@endforeach
		</tr>
	</table>
</td>
