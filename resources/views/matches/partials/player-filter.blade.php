<div class="player-filter-list">
@foreach($scopedPlayers as $player)
	<label>
		{!! Form::checkbox($field . '[]',
			$player->id,
			in_array($player->id, $selected),
			['onclick' => 'this.form.submit()']
		) !!}
		{{ $player->shortName() }}
	</label>
@endforeach
</div>
