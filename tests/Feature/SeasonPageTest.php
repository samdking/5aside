<?php

namespace Tests\Feature;

use App\Player;
use App\Venue;
use Tests\TestCase;
use Tests\Concerns\SeedsMatches;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeasonPageTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMatches;

    public function test_seasons_index_renders_with_no_data()
    {
        $this->get('/seasons')->assertOk()->assertSee('Seasons');
    }

    public function test_seasons_index_lists_each_season_with_its_own_champion()
    {
        $venue = Venue::factory()->create();
        $alice = Player::factory()->create(['first_name' => 'Alice', 'last_name' => 'Anderson']);
        $bob   = Player::factory()->create(['first_name' => 'Bob',   'last_name' => 'Brown']);

        // 2023: Alice wins twice. 2024: Bob wins once.
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-03-01', 'a_scored' => 3, 'b_scored' => 1]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-09-01', 'a_scored' => 2, 'b_scored' => 0]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2024-02-01', 'a_scored' => 0, 'b_scored' => 4]);

        $response = $this->get('/seasons')->assertOk();

        $response->assertSeeInOrder([
            route('seasons.show', 2024), 'B Brown', '3 pts',
            route('seasons.show', 2023), 'A Anderson', '6 pts',
            'All time', '3',
        ], false);
    }

    public function test_seasons_index_counts_distinct_named_players_per_season()
    {
        $venue = Venue::factory()->create();
        $alice = Player::factory()->create(['last_name' => 'Anderson']);
        $bob   = Player::factory()->create(['last_name' => 'Brown']);
        $carol = Player::factory()->create(['last_name' => 'Clark']);
        $anon  = Player::factory()->create(['last_name' => '(anon)']);

        // 2023: Alice and Bob twice - 2 distinct players, not 4 appearances.
        // 2024: Carol plus an anonymous player, who isn't counted.
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-03-01', 'a_scored' => 3, 'b_scored' => 1]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-09-01', 'a_scored' => 2, 'b_scored' => 0]);
        $this->createMatch($venue, [$carol], [$anon], ['date' => '2024-02-01', 'a_scored' => 1, 'b_scored' => 0]);

        $this->get('/seasons')->assertOk()->assertViewHas('playerCounts', function($counts) {
            return $counts->all() === [2023 => 2, 2024 => 1, 'all' => 3];
        });
    }
}
