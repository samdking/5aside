<?php

namespace Tests\Feature;

use App\MatchCreator;
use App\CancelledMatch;
use App\Player;
use App\Venue;
use Tests\TestCase;
use Tests\Concerns\SeedsMatches;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CancelledMatchTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMatches;

    private function weeksAgo(int $weeks): string
    {
        return Carbon::now()->subWeeks($weeks)->format('Y-m-d');
    }

    // Two wins for $signup and $absentee, with a cancelled match between them
    // that only $signup put their name down for.
    private function seedWithCancelledMatch(): array
    {
        $venue = Venue::factory()->create();
        [$signup, $absentee, $opponent] = Player::factory()->count(3)->create();

        $this->createMatch($venue, [$signup, $absentee], [$opponent], ['date' => $this->weeksAgo(3), 'a_scored' => 2, 'b_scored' => 1]);
        $this->createCancelledMatch([$signup], $this->weeksAgo(2));
        $this->createMatch($venue, [$signup, $absentee], [$opponent], ['date' => $this->weeksAgo(1), 'a_scored' => 2, 'b_scored' => 1]);

        return [$signup, $absentee, $opponent];
    }

    public function test_cancelled_match_appears_in_player_results_and_form()
    {
        [$signup] = $this->seedWithCancelledMatch();

        $player = $this->getJson("/api/players/{$signup->id}")->assertOk()->json('player');

        $this->assertEquals(2, $player['matches']);
        $this->assertEquals(2, $player['wins']);
        $this->assertEquals(['Win', 'Cancelled', 'Win'], collect($player['results'])->pluck('result')->all());
        $this->assertTrue($player['results'][1]['cancelled']);
        $this->assertEquals(['Win', 'Cancelled', 'Win'], collect($player['form'])->reverse()->values()->all());
    }

    public function test_non_signup_sees_a_blank_form_entry()
    {
        [, $absentee] = $this->seedWithCancelledMatch();

        $player = $this->getJson("/api/players/{$absentee->id}")->assertOk()->json('player');

        $this->assertEquals(['Win', 'Win'], collect($player['results'])->pluck('result')->all());
        $this->assertEquals(['Win', '', 'Win'], collect($player['form'])->reverse()->values()->all());
    }

    public function test_cancelled_match_does_not_affect_streaks_of_signups()
    {
        [$signup] = $this->seedWithCancelledMatch();

        $streaks = $this->getJson("/api/players/{$signup->id}")->assertOk()->json('player.streaks');

        $this->assertEquals(2, $streaks['current']['apps']['count']);
        $this->assertEquals(2, $streaks['current']['wins']['count']);
    }

    public function test_cancelled_match_breaks_apps_streak_of_non_signups()
    {
        [, $absentee] = $this->seedWithCancelledMatch();

        $streaks = $this->getJson("/api/players/{$absentee->id}")->assertOk()->json('player.streaks');

        $this->assertEquals(1, $streaks['current']['apps']['count']);
        $this->assertEquals(2, $streaks['current']['wins']['count']);
    }

    public function test_cancelled_match_counts_towards_appearance_percentage()
    {
        [$signup, $absentee] = $this->seedWithCancelledMatch();

        $players = collect($this->getJson('/api/players')->assertOk()->json('players'))->keyBy('id');

        $this->assertEquals(2, $players[$signup->id]['matches']);
        $this->assertEquals(100, $players[$signup->id]['appearance_percentage']);
        $this->assertEquals(66.67, $players[$absentee->id]['appearance_percentage']);
    }

    public function test_signup_before_debut_extends_playing_window()
    {
        $venue = Venue::factory()->create();
        [$newbie, $opponent] = Player::factory()->count(2)->create();

        $this->createCancelledMatch([$newbie], $this->weeksAgo(2));
        $this->createMatch($venue, [$newbie], [$opponent], ['date' => $this->weeksAgo(1), 'a_scored' => 2, 'b_scored' => 1]);

        $player = collect($this->getJson('/api/players')->assertOk()->json('players'))->firstWhere('id', $newbie->id);

        $this->assertEquals(100, $player['appearance_percentage_since_debut']);
        $this->assertEquals(100, $player['appearance_percentage_during_playing_window']);
    }

    public function test_player_page_lists_cancelled_match()
    {
        [$signup] = $this->seedWithCancelledMatch();

        // The layout references assets relative to the public directory
        $cwd = getcwd();
        chdir(public_path());

        try {
            $this->get("/players/{$signup->id}")
                ->assertOk()
                ->assertSee('Cancelled (not enough players)');
        } finally {
            chdir($cwd);
        }
    }

    public function test_match_creator_parses_cancelled_match()
    {
        $cancelled = (new MatchCreator)->parse('2026-09-01: Alice Smith, Bob Jones <CANCELLED>');

        $this->assertInstanceOf(CancelledMatch::class, $cancelled);
        $this->assertEquals('2026-09-01', $cancelled->date->format('Y-m-d'));
        $this->assertEquals(['Alice', 'Bob'], $cancelled->players()->pluck('first_name')->sort()->values()->all());
    }

    public function test_match_creator_rejects_duplicate_cancelled_match_players()
    {
        $this->expectExceptionMessage('Alice Smith already appears in a team');

        (new MatchCreator)->parse('2026-09-01: Alice Smith, Alice Smith <CANCELLED>');
    }
}
