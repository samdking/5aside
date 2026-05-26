<div class="team{{ $winners ? ' winners' : '' }}">
    <h2 class="scored">{{ $scored }}</h2>
    <ul>
        @foreach($players as $player)
            @php
                $cls = in_array($player['id'], $highlightTeammates ?? []) ? 'player--highlight-teammate'
                     : (in_array($player['id'], $highlightOpponents ?? []) ? 'player--highlight-opponent' : '');
            @endphp
            <li class="{{ $cls }}">
                <a href="{{ route('players.show', $player['id']) }}">{{ $player['name'] }}</a>
                @if (@$player['injured'])
                    <span class="player--injured">(injured)</span>
                @endif
            </li>
        @endforeach
    </ul>
</div>