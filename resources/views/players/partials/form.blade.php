<td class="form">
	<table class="form-table">
		<tr>
			@foreach($matches as $match)
				<td>
					@if ($result = $player->form->pop())
						{!! link_to_route('matches.show', substr($result, 0, 1), $match->id, [
							'title' => $match->date->format('j F Y') . ' ' . $match->team_a_scored . ' - ' . $match->team_b_scored,
							'class' => 'match ' . strtolower($result)
						]) !!}
					@else
						<span class="match absense"></span>
					@endif
				</td>
			@endforeach
		</tr>
	</table>
</td>
