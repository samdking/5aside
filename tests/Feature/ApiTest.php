<?php

namespace Tests\Feature;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

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

    private function seedMatch(): array
    {
        $venueId = DB::table('venues')->insertGetId(['name' => 'Test Venue']);

        $matchId = DB::table('matches')->insertGetId([
            'date' => Carbon::now()->subWeek()->format('Y-m-d'),
            'venue_id' => $venueId,
            'is_short' => false,
            'is_void' => false,
        ]);

        $winningTeamId = DB::table('teams')->insertGetId([
            'match_id' => $matchId,
            'scored' => 3,
            'winners' => 1,
            'draw' => 0,
            'handicap' => 0,
        ]);

        $losingTeamId = DB::table('teams')->insertGetId([
            'match_id' => $matchId,
            'scored' => 1,
            'winners' => 0,
            'draw' => 0,
            'handicap' => 0,
        ]);

        $winnerId = DB::table('players')->insertGetId(['first_name' => 'Winning', 'last_name' => 'Winner']);
        $loserId = DB::table('players')->insertGetId(['first_name' => 'Losing', 'last_name' => 'Loser']);

        DB::table('player_team')->insert([
            ['player_id' => $winnerId, 'team_id' => $winningTeamId, 'injured' => 0],
            ['player_id' => $loserId, 'team_id' => $losingTeamId, 'injured' => 0],
        ]);

        return [$winnerId, $loserId];
    }
}
