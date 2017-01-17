@extends('layouts.default')

@section('content')

<h1>Player Leaderboard <small>({!! link_to('players/history', 'history') !!})</small></h1>

<table class="matrix" border="1">
	<tr>
		<td style="background: #555"></td>
		@foreach($players as $player)
			<td class="player">{{ $player->last_name }}</td>
		@endforeach
	</tr>
	@foreach($players as $p)
		<tr>
			<td class="player">{{ $p->last_name }}</td>
			<?php $record = $p->winningRecord(); ?>
			@foreach($players as $opp)
				@if ($p->id === $opp->id)
					<td style="background: #555"></td>
				@else
					<td>
						@foreach($record as $r)
							@if ($r->id === $opp->id)
								{{ tally($r->wins) }}
							@endif
						@endforeach
					</td>
				@endif
			@endforeach
		</tr>
	@endforeach
</table>

@stop
