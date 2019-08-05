<?php

namespace App;

class MatchCreator
{
	protected $allPlayers = [];

	/**
	 * Parse a string into a Match instance. Example format:
	 *
	 * YYYY-MM-DD: P1, P2, P3 5 - 3 P4, P5, P6 (Venue Override)
	 *
	 * @param  string  $string
	 * @return App\Match
	 */
	public function parse($string)
	{
		$this->allPlayers = [];

		$match = preg_match('/^(?:(.+): )?(.+) (\d+) ?[\-v] ?(\d+) (.+) ?\((.+)\)?/', $string, $matches);

		if ( ! $match) throw new \Exception('Unknown format');

		list(, $date, $firstTeam, $score1, $score2, $secondTeam, $venue) = $matches;

		$match = Match::create([
			'date' => new \DateTime($date),
			'venue_id' => $this->lookupVenue($venue)->id
		]);

		$team1 = $this->createTeam($score1, $score2);
		$team2 = $this->createTeam($score2, $score1);

		$match->teams()->saveMany([$team1, $team2]);

		$team1->players()->sync($this->parsePlayers($firstTeam));
		$team2->players()->sync($this->parsePlayers($secondTeam));

		$team1->handicap = $team1->players->count() < $team2->players->count();
		$team2->handicap = $team2->players->count() < $team1->players->count();

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
			return Venue::latest();
		}
	}

	/**
	 * Create a team instance from a string of players
	 *
	 * @param  string  $string
	 * @param  int  $score1
	 * @param  int  $score2
	 * @return App\Team
	 */
	private function createTeam($score1, $score2)
	{
		return new Team([
			'scored' => $score1,
			'winners' => $score1 > $score2,
			'draw' => $score1 == $score2,
		]);
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
		$builder = Player::whereFirstName($data[0]);

		if (count($data) === 2) {
			$builder->whereLastName($data[1]);
		}

		$player = $builder->first();

		if ($player) return $player;

		list($first_name, $last_name) = $data;

		return Player::create(compact('first_name', 'last_name'));
	}
}