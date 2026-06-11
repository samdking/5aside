<?php

namespace Tests\Unit;

use App\PlayerStreak;
use PHPUnit\Framework\TestCase;

class PlayerStreakTest extends TestCase
{
    private function match(string $date = '2026-01-01'): object
    {
        return (object)['date' => $date];
    }

    public function test_win_starts_wins_apps_and_undefeated_current_streaks()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match());

        $current = $streak->currentStreaks();
        $this->assertArrayHasKey('wins', $current);
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayHasKey('undefeated', $current);
        $this->assertArrayNotHasKey('winless', $current);
        $this->assertArrayNotHasKey('defeats', $current);
    }

    public function test_loss_starts_defeats_apps_and_winless_current_streaks()
    {
        $streak = new PlayerStreak('p1');
        $streak->lose($this->match());

        $current = $streak->currentStreaks();
        $this->assertArrayHasKey('defeats', $current);
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayHasKey('winless', $current);
        $this->assertArrayNotHasKey('wins', $current);
        $this->assertArrayNotHasKey('undefeated', $current);
    }

    public function test_draw_starts_undefeated_winless_and_apps_current_streaks()
    {
        $streak = new PlayerStreak('p1');
        $streak->draw($this->match());

        $current = $streak->currentStreaks();
        $this->assertArrayHasKey('undefeated', $current);
        $this->assertArrayHasKey('winless', $current);
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayNotHasKey('wins', $current);
        $this->assertArrayNotHasKey('defeats', $current);
    }

    public function test_void_only_starts_apps_current_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->void($this->match());

        $current = $streak->currentStreaks();
        $this->assertArrayHasKey('apps', $current);
        $this->assertArrayNotHasKey('wins', $current);
        $this->assertArrayNotHasKey('defeats', $current);
        $this->assertArrayNotHasKey('undefeated', $current);
        $this->assertArrayNotHasKey('winless', $current);
    }

    public function test_consecutive_wins_extend_win_streak_count()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));
        $streak->win($this->match('2026-01-15'));

        $this->assertEquals(3, $streak->currentStreaks()['wins']->count);
    }

    public function test_loss_ends_current_win_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));
        $streak->lose($this->match('2026-01-15'));

        $this->assertArrayNotHasKey('wins', $streak->currentStreaks());
    }

    public function test_draw_ends_current_win_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));
        $streak->draw($this->match('2026-01-15'));

        $this->assertArrayNotHasKey('wins', $streak->currentStreaks());
    }

    public function test_win_ends_current_defeat_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->lose($this->match('2026-01-01'));
        $streak->lose($this->match('2026-01-08'));
        $streak->win($this->match('2026-01-15'));

        $this->assertArrayNotHasKey('defeats', $streak->currentStreaks());
    }

    public function test_no_show_ends_current_apps_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));
        $streak->noShow($this->match('2026-01-15'));

        $this->assertArrayNotHasKey('apps', $streak->currentStreaks());
    }

    public function test_top_streaks_returns_longest_win_streak()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));  // streak of 1
        $streak->lose($this->match('2026-01-08'));
        $streak->win($this->match('2026-01-15'));  // streak of 1
        $streak->win($this->match('2026-01-22'));  // streak of 2
        $streak->win($this->match('2026-01-29'));  // streak of 3

        $top = $streak->topStreaksByType()->get('wins');
        $this->assertCount(1, $top);
        $this->assertEquals(3, $top->first()->count);
    }

    public function test_top_streaks_returns_all_tied_streaks()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));  // streak of 2
        $streak->lose($this->match('2026-01-15'));
        $streak->win($this->match('2026-01-22'));
        $streak->win($this->match('2026-01-29'));  // streak of 2 again

        $top = $streak->topStreaksByType()->get('wins');
        $this->assertCount(2, $top);
        $this->assertEquals(2, $top->first()->count);
    }

    public function test_ended_streak_is_preserved_in_top_streaks()
    {
        $streak = new PlayerStreak('p1');
        $streak->win($this->match('2026-01-01'));
        $streak->win($this->match('2026-01-08'));
        $streak->win($this->match('2026-01-15'));  // streak of 3
        $streak->lose($this->match('2026-01-22'));

        $this->assertEquals(3, $streak->topStreaksByType()->get('wins')->first()->count);
    }
}
