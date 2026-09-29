<td class="form">
	<table class="form-table">
		<tr>
			@foreach($player->form as $match)
				<td>
					@if ($match && $match->result == 'Missed')
						<span title="{{ $match->date->format('j F Y') }} - Missed (not enough players)" class="match missed">M</span>
					@elseif ($match)
						<a href="{{ route('matches.show', $match->id) }}" title="{{ $match->date->format('j F Y') . ' ' . $match->team_a_scored . ' - ' . $match->team_b_scored }}" class="match {{ strtolower($match->result) }}">{{ substr($match->result, 0, 1) }}</a>
					@else
						<span class="match absense"></span>
					@endif
				</td>
			@endforeach
		</tr>
	</table>
</td>
