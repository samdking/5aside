<?php namespace App\Http\ViewComposers;

use Illuminate\View\View;

class TeamComposer
{
    public function compose(View $view)
    {
        $data = $view->getData();
        $highlightTeammates = $data['teammates'] ?? [];
        $highlightOpponents = $data['opponents'] ?? [];

        $players = collect($data['players'])->map(function($player) use ($highlightTeammates, $highlightOpponents) {
            $player['class'] = in_array($player['id'], $highlightTeammates) ? 'player--highlight-teammate'
                : (in_array($player['id'], $highlightOpponents) ? 'player--highlight-opponent' : '');
            return $player;
        });

        $view->with('players', $players);
    }
}
