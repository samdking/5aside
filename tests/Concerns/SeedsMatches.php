<?php

namespace Tests\Concerns;

use App\MatchResult;
use App\MissedMatch;
use App\Team;
use App\Venue;

trait SeedsMatches
{
    /**
     * Persist a match between two teams of players.
     *
     * @param  \App\Player[]  $teamA
     * @param  \App\Player[]  $teamB
     * @param  array  $opts  date, a_scored, b_scored
     */
    private function createMatch(Venue $venue, array $teamA, array $teamB, array $opts): void
    {
        $aScored = $opts['a_scored'];
        $bScored = $opts['b_scored'];
        $draw    = $aScored === $bScored;

        $match = MatchResult::factory()->create([
            'date'     => $opts['date'],
            'venue_id' => $venue->id,
        ]);

        $teamAModel = Team::factory()->create([
            'match_id' => $match->id,
            'scored'   => $aScored,
            'winners'  => $aScored > $bScored ? 1 : 0,
            'draw'     => $draw ? 1 : 0,
        ]);

        $teamBModel = Team::factory()->create([
            'match_id' => $match->id,
            'scored'   => $bScored,
            'winners'  => $bScored > $aScored ? 1 : 0,
            'draw'     => $draw ? 1 : 0,
        ]);

        $teamAModel->players()->attach(
            collect($teamA)->mapWithKeys(fn($p) => [$p->id => ['injured' => 0]])
        );
        $teamBModel->players()->attach(
            collect($teamB)->mapWithKeys(fn($p) => [$p->id => ['injured' => 0]])
        );
    }

    /**
     * Persist a missed match with the players who put their name down.
     *
     * @param  \App\Player[]  $players
     */
    private function createMissedMatch(array $players, string $date): MissedMatch
    {
        $missed = MissedMatch::factory()->create(['date' => $date]);

        $missed->players()->attach(collect($players)->pluck('id'));

        return $missed;
    }
}
