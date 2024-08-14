<table class="leaderboard" id="leaderboard-{{ md5($method) }}">
	<thead>
		<tr>
			<th colspan="10"></th>
			<th colspan="3" class="handicap">Handicap</th>
			<th colspan="2"></th>
		</tr>
		<tr>
			<th>Player</th>
			<th>Pld</th>
			<th>W</th>
			<th>D</th>
			<th>L</th>
			<th>F</th>
			<th>A</th>
			<th>GD</th>
			<th>Pts</th>
			<th>Win %</th>
			<th>Pld</th>
			<th>W</th>
			<th>L</th>
			<th>Last App</th>
			<th>Form</th>
		</tr>
	</thead>
	@foreach ($players as $player)
	<tr class="{{ $matchesForForm->first()->date->greaterThan($player->last_app) ? "js-inactive" : "" }}">
		<td class="name">
			{!! link_to_route('players.show', $player->first_name . ' ' . $player->last_name, [$player->id] + Request::only('from', 'to', 'year', 'last')) !!}
		</td>
		<td>{{ $player->apps }}</td>
		<td>{{ $player->wins }}</td>
		<td>{{ $player->draws }}</td>
		<td>{{ $player->losses }}</td>
		<td>{{ $player->goals_for }}</td>
		<td>{{ $player->goals_against }}</td>
		<td>{{ $player->diff }}</td>
		<td>{{ $player->wins * 3 + $player->draws}}</td>
		<td>{{ round($player->win_percentage, 1) }}%</td>
		<td>{{ $player->handicap_apps }}</td>
		<td>{{ $player->handicap_wins }}</td>
		<td>{{ $player->handicap_losses }}</td>
		<td>{{ $player->last_app }}</td>

		<td class="form">
			<table class="form-table">
				<tr>
					@foreach($matchesForForm as $match)
						<td>
							@if ($team = $mainPlayer->$method($match, $player))
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
	</tr>
	@endforeach
</table>
<div class="leaderboard-controls">
	<a onclick="document.getElementById('leaderboard-{{ md5($method) }}').classList.toggle('js-inactive-players-table')">Toggle active players</a>
</div>
