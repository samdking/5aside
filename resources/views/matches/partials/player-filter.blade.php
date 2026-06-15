<div class="player-filter-list">
@foreach($scopedPlayers as $player)
	<label>
		{!! html()->checkbox(
			$field . '[]',
			in_array($player->id, $selected),
			$player->id
		)->attribute('onclick', 'this.form.submit()') !!}
		{{ $player->shortName() }}
	</label>
@endforeach
</div>
