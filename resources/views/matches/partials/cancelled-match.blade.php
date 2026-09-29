<div class="match cancelled">
	<span class="date">
		{{ DateTime::createFromFormat('Y-m-d', $match->date)->format('D jS F Y') }}
		- Cancelled (not enough players)
	</span>

	<div class="signups">
		<ul>
			@foreach($match->players as $player)
				<li><a href="{{ route('players.show', $player['id']) }}">{{ $player['name'] }}</a></li>
			@endforeach
		</ul>
	</div>
</div>
