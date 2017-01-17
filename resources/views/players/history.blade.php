@extends('layouts.default')

@section('content')

<h2>Player Results History</h2>

<table class="leaderboard history">
	<thead>
		<tr>
			<th class="number">#</th>
			<th>Name</th>
			<th>Apps</th>
			<th>Record ({{ $matches->count() }} matches)</th>
		</tr>
	</thead>
	<tbody>
		@foreach(array_values($players->all()) as $i => $player)
		<tr>
			<td class="number">{{ $i+1 }}</td>
			<td class="name">
				<a href="{{ route('players.show', $player->id) }}">
					{{ $player->first_name . ' ' . $player->last_name }}
				</a>
			</td>
			<td>{{ $player->teams->count() }}</td>
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
		</tr>
		@endforeach
	</tbody>
</table>

@stop
