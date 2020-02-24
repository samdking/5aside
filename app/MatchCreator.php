<?php

namespace App;

class MatchCreator
{
	protected $allPlayers = [];

	/**
	 * Parse a string into a Match instance. Example format:
	 *
	 * YYYY-MM-DD: P1, P2, P3 5 - 3 P4, P5, P6 [Venue] <VOID>
	 *
	 * @param  string  $string
	 * @return App\Match
	 */
	public function parse($string)
	{
		$this->allPlayers = [];

		$match = preg_match('/^(?:(.+): )?(.+) (\d+) ?[\-v] ?(\d+) ([^\[\]<>]+)(?: \[(.+)\])?(?: (<VOID>))?$/', $string, $matches);

		if ( ! $match) throw new \Exception('Unknown format');


		[, $date, $firstTeam, $score1, $score2, $secondTeam] = $matches;

		$venue = count($matches) == 7 ? $matches[6] : null;

		$void = count($matches) == 8;

		$match = Match::create([
			'date' => new \DateTime($date),
			'venue_id' => $this->lookupVenue($venue)->id,
			'is_void' => $void
		]);

		[$team1, $team2] = $this->createTeams($score1, $score2, $void);

		$match->teams()->saveMany([$team1, $team2]);

		$team1->players()->sync($this->parsePlayers($firstTeam));
		$team2->players()->sync($this->parsePlayers($secondTeam));

		if (!$void) {
			$team1->handicap = $team1->players->count() < $team2->players->count();
			$team2->handicap = $team2->players->count() < $team1->players->count();
		}

		$team1->save();
		$team2->save();

		return $match;
	}

	/**
	 * Looks up venue by string or gets the latest
	 *
	 * @param  string  $venueString
	 * @return App\Venue
	 */
	private function lookupVenue($venueString)
	{
		if ($venueString) {
			return Venue::whereName($venueString)->firstOrFail();
		} else {
			return Venue::latest()->first();
		}
	}

	/**
	 * Returns an array of created team instances from 2 scores
	 *
	 * @param  int  $score1
	 * @param  int  $score2
	 * @param  boolean  @void
	 * @return Illuminate\Support\Collection
	 */
	private function createTeams($score1, $score2, $void)
	{
		return collect([$score1, $score2], [$score2, $score1])->map(function($scores) use ($void) {
			return new Team([
				'scored' => $scores[0],
				'winners' => $void ? null : $scores[0] > $scores[1],
				'draw' => $void ? null : $scores[0] == $scores[1],
			]);
		});
	}

	/**
	 * Return a collection of players from a comma separated string
	 *
	 * @param  string  $string
	 * @return Illuminate\Support\Collection
	 */
	private function parsePlayers($string)
	{
		$players = new \Illuminate\Database\Eloquent\Collection(explode(',', trim($string)));

		return $players->map(function($player) {
			$player = trim($player);
			if (in_array($player, $this->allPlayers)) {
				throw new \Exception($player . ' already appears in a team');
			}
			$this->allPlayers[] = $player;
			return $this->lookupPlayer($player);
		});
	}

	protected function lookupPlayer($string)
	{
		$data = explode(' ', $string);

		$firstName = array_shift($data);
		$lastName = implode(' ', $data);

		$builder = Player::whereFirstName($firstName);

		if ($lastName) {
			$builder->whereLastName($lastName);
		}

		$player = $builder->first();

		if ($player) return $player;

		return Player::create(['first_name' => $firstName, 'last_name' => $lastName]);
	}
}
