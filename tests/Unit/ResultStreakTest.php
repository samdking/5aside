<?php

namespace Tests\Unit;

use App\PlayerStreak;
use App\ResultStreak;
use PHPUnit\Framework\TestCase;

class ResultStreakTest extends TestCase
{
    private function match(array $winners = [], array $losers = [], array $draw = [], array $voided = [], array $missed = []): object
    {
        return (object)[
            'date'    => '2026-01-01',
            'year'    => 2026,
            'winners' => collect($winners),
            'losers'  => collect($losers),
            'draw'    => collect($draw),
            'voided'  => collect($voided),
            'missed'  => collect($missed),
        ];
    }

    public function test_winner_is_routed_to_win()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(winners: ['alice'])))->updateStreakFor($playerStreak);

        $this->assertArrayHasKey('wins', $playerStreak->currentStreaks());
    }

    public function test_loser_is_routed_to_lose()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(losers: ['alice'])))->updateStreakFor($playerStreak);

        $this->assertArrayHasKey('defeats', $playerStreak->currentStreaks());
    }

    public function test_draw_is_routed_to_draw()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(draw: ['alice'])))->updateStreakFor($playerStreak);

        $current = $playerStreak->currentStreaks();
        $this->assertArrayHasKey('undefeated', $current);
        $this->assertArrayHasKey('winless', $current);
        $this->assertArrayNotHasKey('wins', $current);
        $this->assertArrayNotHasKey('defeats', $current);
    }

    public function test_void_is_routed_to_void()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(voided: ['alice'])))->updateStreakFor($playerStreak);

        $current = $playerStreak->currentStreaks();
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayNotHasKey('wins', $current);
        $this->assertArrayNotHasKey('defeats', $current);
        $this->assertArrayNotHasKey('undefeated', $current);
        $this->assertArrayNotHasKey('winless', $current);
    }

    public function test_absent_player_is_routed_to_no_show()
    {
        $playerStreak = new PlayerStreak('alice');
        $playerStreak->win((object)['date' => '2025-12-01']);

        (new ResultStreak($this->match()))->updateStreakFor($playerStreak);

        $this->assertArrayNotHasKey('apps', $playerStreak->currentStreaks());
    }

    public function test_void_is_checked_before_win()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(winners: ['alice'], voided: ['alice'])))->updateStreakFor($playerStreak);

        $current = $playerStreak->currentStreaks();
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayNotHasKey('wins', $current);
    }

    public function test_missed_match_is_neutral_for_players_who_signed_up()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(winners: ['alice'])))->updateStreakFor($playerStreak);
        (new ResultStreak($this->match(missed: ['alice'])))->updateStreakFor($playerStreak);

        $current = $playerStreak->currentStreaks();
        $this->assertEquals(1, $current['apps']->count);
        $this->assertEquals(1, $current['wins']->count);
        $this->assertEquals(1, $current['undefeated']->count);
    }

    public function test_missed_match_is_a_no_show_for_players_who_did_not_sign_up()
    {
        $playerStreak = new PlayerStreak('alice');
        (new ResultStreak($this->match(winners: ['alice'])))->updateStreakFor($playerStreak);
        (new ResultStreak($this->match(missed: ['bob'])))->updateStreakFor($playerStreak);

        $current = $playerStreak->currentStreaks();
        $this->assertArrayNotHasKey('apps', $current);
        $this->assertArrayHasKey('wins', $current);
    }
}
