<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill the matches that were cancelled due to a lack of players, along
 * with those who put their name down. Closures (COVID, bank holidays, venue
 * closures etc.) are deliberately not included.
 *
 * Players are listed by full name as first names alone are ambiguous over
 * this time period (eg. Tom Champion in 2022 vs Tom Morgan in 2025).
 */
class SeedCancelledMatches extends Migration
{
    const CANCELLED_MATCHES = [
        '2021-12-27' => ['Davs Howard', 'Alfie Stirling', 'Josh Brown', 'Dom Seymour', 'Steve Parker', 'Sam King'],
        '2022-04-11' => ['Sam King', 'Josh Brown', 'Tom Champion', 'Dom Seymour', 'Mike James', 'Dan Lock', 'Alfie Stirling'],
        '2022-09-26' => ['Dom Seymour', 'Josh Brown', 'Davs Howard', 'Aaron Keens', 'Dan Lock', 'Steve Parker', 'Rowan Colbeck'],
        '2022-12-05' => ['Dom Seymour', 'Sam King', 'Mike James', 'Rob Bridger', 'Dan Lock', 'Steve Parker'],
        '2023-11-20' => ['Dom Seymour', 'Davs Howard', 'Rupert Loman-West', 'Mike James', 'Dan Lock', 'Dave Morpeth'],
        '2023-12-18' => ['Sam King', 'Davs Howard', 'Dan Lock', 'Jan Edwards', 'Steve Parker'],
        '2024-01-22' => ['Sam King', 'Davs Howard', 'Rob Bridger', 'Roddy Clarke', 'Alfie Stirling', 'Mike James', 'Dan Lock', 'Jan Edwards'],
        '2024-02-26' => ['Davs Howard', 'Mike James', 'Mike Seerao', 'Keir Standing', 'Jamie Mckenna', 'Steve Parker'],
        '2024-12-23' => ['Sam King', 'Joe Hesketh', 'Steve Parker', 'Alfie Stirling', 'Davs Howard', 'Dan Lock'],
        '2025-02-17' => ['Sam King', 'Tom Morgan', 'Rupert Loman-West', 'Dan Lock'],
        '2025-04-21' => ['Tom Morgan', 'Rupert Loman-West', 'Davs Howard', 'Rowan Colbeck', 'Dan Lock', 'Mike James'],
        '2025-09-22' => ['Davs Howard', 'Sam King', 'Dan Lock', 'Dylan Evans', 'Will Vergano', 'Ethan Whalley', 'Steve Parker'],
        '2025-12-15' => ['Sam King', 'Dan Lock', 'Davs Howard', 'Steve Parker', 'Dylan Evans', 'Rowan Colbeck', 'Jon Hayes'],
        '2026-09-28' => ['Sam King', 'Ethan Whalley', 'Reece Flint', 'Tom Mayfield', 'Mike James', 'Dan Lock', 'James Burt', 'Davs Howard'],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nothing to backfill on a fresh database (eg. tests)
        if ( ! DB::table('players')->exists()) return;

        DB::transaction(function () {
            foreach (self::CANCELLED_MATCHES as $date => $names) {
                if (DB::table('cancelled_matches')->where('date', $date)->exists()) continue;

                $playerIds = array_map([$this, 'lookupPlayerId'], $names);

                $id = DB::table('cancelled_matches')->insertGetId(['date' => $date]);

                DB::table('cancelled_match_player')->insert(array_map(function ($playerId) use ($id) {
                    return ['cancelled_match_id' => $id, 'player_id' => $playerId];
                }, $playerIds));
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $ids = DB::table('cancelled_matches')
            ->whereIn('date', array_keys(self::CANCELLED_MATCHES))
            ->pluck('id');

        DB::table('cancelled_match_player')->whereIn('cancelled_match_id', $ids)->delete();
        DB::table('cancelled_matches')->whereIn('id', $ids)->delete();
    }

    private function lookupPlayerId($name)
    {
        [$firstName, $lastName] = explode(' ', $name, 2);

        $ids = DB::table('players')
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->pluck('id');

        if ($ids->count() !== 1) {
            throw new \Exception("Expected exactly 1 player named {$name}, found {$ids->count()}");
        }

        return $ids->first();
    }
}
