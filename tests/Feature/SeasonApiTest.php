<?php

namespace Tests\Feature;

use App\Player;
use App\Queries\AllSeasonsQuery;
use App\Venue;
use Tests\TestCase;
use Tests\Concerns\SeedsMatches;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SeasonApiTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMatches;

    public function test_v2_seasons_endpoint_returns_empty_season_with_no_data()
    {
        $seasons = $this->getJson('/api/v2/seasons')->assertOk()->json('seasons');

        $this->assertArrayHasKey('all', $seasons);
        $this->assertEquals(0, $seasons['all']['total_matches']);
        $this->assertEquals(0, $seasons['all']['total_goals']);
        $this->assertSame([], $seasons['all']['matches']);
    }

    public function test_v2_seasons_endpoint_lists_each_distinct_season()
    {
        $this->seedTwoSeasons();

        $seasons = $this->getJson('/api/v2/seasons')->assertOk()->json('seasons');

        // An "all" rollup plus one entry per distinct year in the data.
        $this->assertEqualsCanonicalizing(['all', '2023', '2024'], array_keys($seasons));

        // 2023: two matches, goals 4 + 2 = 6, averaging 3 per game.
        $this->assertEquals(2, $seasons['2023']['total_matches']);
        $this->assertEquals(6, $seasons['2023']['total_goals']);
        $this->assertEquals(3, $seasons['2023']['average_goals']);

        // 2024: a single match.
        $this->assertEquals(1, $seasons['2024']['total_matches']);

        // The all-time rollup spans both seasons.
        $this->assertEquals(3, $seasons['all']['total_matches']);
        $this->assertEquals(8, $seasons['all']['total_goals']);

        // The listing omits the (expensive) per-player leaderboard.
        $this->getJson('/api/v2/seasons')->assertJsonMissingPath('seasons.all.leaderboard');
    }

    public function test_v2_single_season_endpoint_returns_stats_and_leaderboard()
    {
        [$alice, $bob] = $this->seedTwoSeasons();

        $season = $this->getJson('/api/v2/seasons/2023')->assertOk()->json('season');

        $this->assertEquals(2023, $season['year']);
        $this->assertEquals(2, $season['total_matches']);
        $this->assertEquals(6, $season['total_goals']);
        $this->assertEquals(3, $season['average_goals']);

        // Within 2023 Alice won both games (6 pts), Bob lost both (0 pts).
        $leaderboard = collect($season['leaderboard']);
        $this->assertCount(2, $leaderboard);
        $this->assertEquals(6, $leaderboard->firstWhere('id', $alice->id)['points']);
        $this->assertEquals(2, $leaderboard->firstWhere('id', $alice->id)['wins']);
        $this->assertEquals(0, $leaderboard->firstWhere('id', $bob->id)['points']);
    }

    public function test_v2_single_season_endpoint_excludes_other_years()
    {
        $this->seedTwoSeasons();

        $season = $this->getJson('/api/v2/seasons/2024')->assertOk()->json('season');

        // Only the single 2024 match is in scope; the two 2023 matches are excluded.
        $this->assertEquals(2024, $season['year']);
        $this->assertEquals(1, $season['total_matches']);

        $matches = collect($season['matches']);
        $this->assertCount(1, $matches);
        $this->assertTrue($matches->every(fn($m) => $m['year'] === 2024));
    }

    public function test_seasons_count_distinct_named_players()
    {
        $venue = Venue::factory()->create();
        $alice = Player::factory()->create(['last_name' => 'Anderson']);
        $bob   = Player::factory()->create(['last_name' => 'Brown']);
        $carol = Player::factory()->create(['last_name' => 'Clark']);
        $anon  = Player::factory()->create(['last_name' => '(anon)']);

        // 2023: Alice and Bob twice - 2 distinct players from 4 appearances.
        // 2024: Carol plus an anonymous player, who isn't counted.
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-03-01', 'a_scored' => 3, 'b_scored' => 1]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-09-01', 'a_scored' => 2, 'b_scored' => 0]);
        $this->createMatch($venue, [$carol], [$anon], ['date' => '2024-02-01', 'a_scored' => 1, 'b_scored' => 0]);

        $seasons = $this->getJson('/api/v2/seasons')->assertOk()->json('seasons');

        $this->assertSame(2, $seasons['2023']['distinct_players']);
        $this->assertSame(4, $seasons['2023']['total_players']);
        $this->assertSame(1, $seasons['2024']['distinct_players']);
        $this->assertSame(3, $seasons['all']['distinct_players']);

        $this->getJson('/api/v2/seasons/2023')->assertOk()->assertJsonPath('season.distinct_players', 2);
        $this->getJson('/api/v2/seasons/2024')->assertOk()->assertJsonPath('season.distinct_players', 1);
    }

    public function test_all_seasons_query_scopes_each_leaderboard_to_its_own_year()
    {
        [$alice, $bob] = $this->seedTwoSeasons();

        $request = Request::create('/');
        $request['show_inactive'] = true;

        $seasons = (new AllSeasonsQuery($request))->get();

        // 2023: Alice won both. 2024: a draw, so both have a point.
        // All time: Alice 7, Bob 1. Each season must use its own points.
        $points = fn($year, $player) => $seasons[$year]['leaderboard']->firstWhere('id', $player->id)->points;

        $this->assertEquals(6, $points(2023, $alice));
        $this->assertEquals(0, $points(2023, $bob));
        $this->assertEquals(1, $points(2024, $alice));
        $this->assertEquals(1, $points(2024, $bob));
        $this->assertEquals(7, $points('all', $alice));
        $this->assertEquals(1, $points('all', $bob));
    }

    // Seeds three matches across two seasons between Alice and Bob:
    //   2023-03-01  Alice 3 - 1 Bob   (Alice win)
    //   2023-09-01  Alice 2 - 0 Bob   (Alice win)
    //   2024-02-01  Alice 1 - 1 Bob   (draw)
    // => all-time: Alice 7 pts, Bob 1 pt; 2023: Alice 6 pts, Bob 0 pts.
    private function seedTwoSeasons(): array
    {
        $venue = Venue::factory()->create();
        $alice = Player::factory()->create(['first_name' => 'Alice', 'last_name' => 'Alice']);
        $bob   = Player::factory()->create(['first_name' => 'Bob',   'last_name' => 'Bob']);

        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-03-01', 'a_scored' => 3, 'b_scored' => 1]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2023-09-01', 'a_scored' => 2, 'b_scored' => 0]);
        $this->createMatch($venue, [$alice], [$bob], ['date' => '2024-02-01', 'a_scored' => 1, 'b_scored' => 1]);

        return [$alice, $bob];
    }
}
