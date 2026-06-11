<?php

namespace Tests\Feature;

use App\MatchResult;
use App\Player;
use App\Team;
use App\Venue;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_players_endpoint_returns_empty_array_with_no_data()
    {
        $this->getJson('/api/players')
            ->assertOk()
            ->assertJson(['players' => []]);
    }

    public function test_matches_endpoint_returns_empty_array_with_no_data()
    {
        $this->getJson('/api/matches')
            ->assertOk()
            ->assertJson(['matches' => []]);
    }

    public function test_venues_endpoint_returns_empty_array_with_no_data()
    {
        $this->getJson('/api/venues')
            ->assertOk()
            ->assertJson(['venues' => []]);
    }

    public function test_players_endpoint_returns_player_with_correct_stats()
    {
        [$winnerId, $loserId] = $this->seedMatch();

        $response = $this->getJson('/api/players')->assertOk();
        $players = collect($response->json('players'));

        $this->assertCount(2, $players);

        $winner = $players->firstWhere('last_name', 'Winner');
        $this->assertEquals(1, $winner['wins']);
        $this->assertEquals(1, $winner['matches']);
        $this->assertEquals(0, $winner['losses']);

        $loser = $players->firstWhere('last_name', 'Loser');
        $this->assertEquals(0, $loser['wins']);
        $this->assertEquals(1, $loser['matches']);
        $this->assertEquals(1, $loser['losses']);
    }

    public function test_individual_player_endpoint_returns_player_with_correct_stats()
    {
        [$aliceId, $bobId, $pool] = $this->seedMatches();

        $alice      = $this->getJson("/api/players/{$aliceId}")->assertOk()->json('player');
        $bob        = $this->getJson("/api/players/{$bobId}")->assertOk()->json('player');
        $poolPlayer = $this->getJson("/api/players/{$pool->random()->id}")->assertOk()->json('player');

        // Alice: 2 wins, 1 draw, 1 loss across 4 matches
        $this->assertEquals('Alice', $alice['last_name']);
        $this->assertEquals(4, $alice['matches']);
        $this->assertEquals(2, $alice['wins']);
        $this->assertEquals(1, $alice['draws']);
        $this->assertEquals(1, $alice['losses']);

        // Bob: 1 win, 1 draw, 2 losses across 4 matches
        $this->assertEquals('Bob', $bob['last_name']);
        $this->assertEquals(4, $bob['matches']);
        $this->assertEquals(1, $bob['wins']);
        $this->assertEquals(1, $bob['draws']);
        $this->assertEquals(2, $bob['losses']);

        // pool player appears in all 4 matches
        $this->assertEquals(4, $poolPlayer['matches']);
    }

    public function test_individual_player_endpoint_returns_correct_goals()
    {
        [$aliceId, $bobId] = $this->seedMatches();

        $alice = $this->getJson("/api/players/{$aliceId}")->assertOk()->json('player');
        $bob   = $this->getJson("/api/players/{$bobId}")->assertOk()->json('player');

        // Alice scored: 3+2+1+1 = 7, conceded: 1+0+1+2 = 4
        $this->assertEquals(7, $alice['scored']);
        $this->assertEquals(4, $alice['conceded']);
        $this->assertEquals(3, $alice['gd']);

        // Bob scored: 1+0+1+2 = 4, conceded: 3+2+1+1 = 7
        $this->assertEquals(4, $bob['scored']);
        $this->assertEquals(7, $bob['conceded']);
        $this->assertEquals(-3, $bob['gd']);
    }

    public function test_individual_player_endpoint_returns_streaks()
    {
        $venue    = Venue::factory()->create();
        [$player, $opponent] = Player::factory()->count(2)->create();

        // 3 wins, then a loss, then a win
        $this->createMatch($venue, [$player], [$opponent], ['date' => Carbon::now()->subWeeks(5)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 1]);
        $this->createMatch($venue, [$player], [$opponent], ['date' => Carbon::now()->subWeeks(4)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 1]);
        $this->createMatch($venue, [$player], [$opponent], ['date' => Carbon::now()->subWeeks(3)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 1]);
        $this->createMatch($venue, [$player], [$opponent], ['date' => Carbon::now()->subWeeks(2)->format('Y-m-d'), 'a_scored' => 1, 'b_scored' => 2]);
        $this->createMatch($venue, [$player], [$opponent], ['date' => Carbon::now()->subWeeks(1)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 1]);

        $streaks = $this->getJson("/api/players/{$player->id}")->assertOk()->json('player.streaks');

        $this->assertEquals(3, $streaks['wins'][0]['count']);
        $this->assertEquals(1, $streaks['defeats'][0]['count']);
        $this->assertEquals(1, $streaks['current']['wins']['count']);
    }

    public function test_players_endpoint_reflects_variable_match_participation()
    {
        $venue      = Venue::factory()->create();
        [$frequent, $occasional, $oneOff, $opponent] = Player::factory()->count(4)->create();

        // frequent plays all 4, occasional plays 2, oneOff plays 1
        $this->createMatch($venue, [$frequent, $occasional, $oneOff], [$opponent], [
            'date' => Carbon::now()->subWeeks(1)->format('Y-m-d'), 'a_scored' => 3, 'b_scored' => 1,
        ]);
        $this->createMatch($venue, [$frequent, $occasional], [$opponent], [
            'date' => Carbon::now()->subWeeks(2)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 0,
        ]);
        $this->createMatch($venue, [$frequent], [$opponent], [
            'date' => Carbon::now()->subWeeks(3)->format('Y-m-d'), 'a_scored' => 1, 'b_scored' => 2,
        ]);
        $this->createMatch($venue, [$frequent], [$opponent], [
            'date' => Carbon::now()->subWeeks(4)->format('Y-m-d'), 'a_scored' => 1, 'b_scored' => 1,
        ]);

        $players = collect($this->getJson('/api/players')->assertOk()->json('players'))->keyBy('id');

        $frequentStats   = $players[$frequent->id];
        $occasionalStats = $players[$occasional->id];
        $oneOffStats     = $players[$oneOff->id];

        // frequent: 4 matches, 2W 1D 1L, scored 7, conceded 4, points 7
        $this->assertEquals(4, $frequentStats['matches']);
        $this->assertEquals(7, $frequentStats['scored']);
        $this->assertEquals(4, $frequentStats['conceded']);
        $this->assertEquals(7, $frequentStats['points']);

        // occasional: 2 matches, 2W, scored 5, conceded 1, points 6
        $this->assertEquals(2, $occasionalStats['matches']);
        $this->assertEquals(5, $occasionalStats['scored']);
        $this->assertEquals(1, $occasionalStats['conceded']);
        $this->assertEquals(6, $occasionalStats['points']);

        // oneOff: 1 match, 1W, scored 3, conceded 1, points 3
        $this->assertEquals(1, $oneOffStats['matches']);
        $this->assertEquals(3, $oneOffStats['scored']);
        $this->assertEquals(1, $oneOffStats['conceded']);
        $this->assertEquals(3, $oneOffStats['points']);
    }

    public function test_individual_player_endpoint_returns_null_for_nonexistent_player()
    {
        $this->getJson('/api/players/999')
            ->assertOk()
            ->assertJson(['player' => null]);
    }

    private function seedMatch(): array
    {
        $venue  = Venue::factory()->create();
        $winner = Player::factory()->create(['first_name' => 'Winning', 'last_name' => 'Winner']);
        $loser  = Player::factory()->create(['first_name' => 'Losing',  'last_name' => 'Loser']);

        $this->createMatch($venue, [$winner], [$loser], [
            'date'     => Carbon::now()->subWeek()->format('Y-m-d'),
            'a_scored' => 3, 'b_scored' => 1,
        ]);

        return [$winner->id, $loser->id];
    }

    // Seeds 4 matches between Alice and Bob with varied results:
    //   Alice: 2W 1D 1L  (7 scored, 4 conceded)
    //   Bob:   1W 1D 2L  (4 scored, 7 conceded)
    private function seedMatches(): array
    {
        $venue = Venue::factory()->create();
        $alice = Player::factory()->create(['first_name' => 'Alice', 'last_name' => 'Alice']);
        $bob   = Player::factory()->create(['first_name' => 'Bob',   'last_name' => 'Bob']);

        $pool = Player::factory()->count(8)->create();

        $fixtures = [
            ['date' => Carbon::now()->subWeeks(1)->format('Y-m-d'), 'a_scored' => 3, 'b_scored' => 1],
            ['date' => Carbon::now()->subWeeks(2)->format('Y-m-d'), 'a_scored' => 2, 'b_scored' => 0],
            ['date' => Carbon::now()->subWeeks(3)->format('Y-m-d'), 'a_scored' => 1, 'b_scored' => 1],
            ['date' => Carbon::now()->subWeeks(4)->format('Y-m-d'), 'a_scored' => 1, 'b_scored' => 2],
        ];

        foreach ($fixtures as $fixture) {
            $shuffled = $pool->shuffle();
            $teamA = collect([$alice])->merge($shuffled->take(4))->all();
            $teamB = collect([$bob])->merge($shuffled->skip(4))->all();
            $this->createMatch($venue, $teamA, $teamB, $fixture);
        }

        return [$alice->id, $bob->id, $pool];
    }

    private function createMatch(Venue $venue, array $teamA, array $teamB, array $opts): void
    {
        $aScored = $opts['a_scored'];
        $bScored = $opts['b_scored'];
        $draw    = $aScored === $bScored;
        $aWins   = $aScored > $bScored;
        $bWins   = $bScored > $aScored;

        $match = MatchResult::factory()->create([
            'date'     => $opts['date'],
            'venue_id' => $venue->id,
        ]);

        $teamAModel = Team::factory()->create([
            'match_id' => $match->id,
            'scored'   => $aScored,
            'winners'  => $aWins ? 1 : 0,
            'draw'     => $draw  ? 1 : 0,
        ]);

        $teamBModel = Team::factory()->create([
            'match_id' => $match->id,
            'scored'   => $bScored,
            'winners'  => $bWins ? 1 : 0,
            'draw'     => $draw  ? 1 : 0,
        ]);

        $teamAModel->players()->attach(
            collect($teamA)->mapWithKeys(fn($p) => [$p->id => ['injured' => 0]])
        );
        $teamBModel->players()->attach(
            collect($teamB)->mapWithKeys(fn($p) => [$p->id => ['injured' => 0]])
        );
    }
}
