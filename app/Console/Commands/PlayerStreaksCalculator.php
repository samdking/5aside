<?php

namespace App\Console\Commands;

use App\Player;
use App\Match;
use Illuminate\Console\Command;

class PlayerStreaksCalculator extends Command
{
	protected $signature = 'calculate:streaks';

	public function handle()
	{
		$matches = Match::with('teams.players')->orderBy("date")->get();
		$allPlayers = Player::all()->keyBy('id');

		$players = $allPlayers->map(function($p) {
			return [
				'id' => $p->id,
				'count' => 0,
				'from' => null,
			];
		})->toArray();

		$streaks = [];
		$maxCountPerPlayer = $allPlayers->map(function($p) { return 0; })->toArray();

		foreach($matches as $match) {
			$participants = $match->participants();
			$maxCount = 0;
			foreach($participants as $participant) {
				$players[$participant->id]['count']++;
				$players[$participant->id]['from'] = is_null($players[$participant->id]['from']) ? $match->date : $players[$participant->id]['from'];
				$players[$participant->id]['to'] = $match->date;
				$maxCount = max($players[$participant->id]['count'], $maxCount);
				$maxCountPerPlayer[$participant->id] = max($players[$participant->id]['count'], $maxCountPerPlayer[$participant->id]);
			}
			foreach($players as $id => $player) {
				if (in_array($id, $participants->pluck('id')->all())) continue;
				if ($player['count'] == 0) continue;
				if ($player['count'] > $maxCount) {
					$streaks[] = ['id' => $id] + $player;
				}
				$players[$id]['count'] = 0;
				$players[$id]['from'] = null;
			}
		}

		foreach($players as $id => $player) {
			if ($player['count'] == 0) continue;
			if ($player['count'] == $maxCount) {
				$streaks[] = ['id' => $id] + $player;
			}
		}

		arsort($maxCountPerPlayer);

		$this->info('All-time highest streaks');
		foreach($maxCountPerPlayer as $id => $topStreak) {
			if ($topStreak == 1) continue;
			$player = $allPlayers[$id];
			$this->line(
				"{$player->fullName()} - {$topStreak}"
			);
		}

		$this->line('');

		$this->info('Historical streaks');
		foreach($streaks as $streak) {
			$player = $allPlayers[$streak['id']];
			$this->line(
				"{$streak['from']->format('Y-m-d')} - {$streak['to']->format('Y-m-d')}: {$player->fulLName()} ({$streak['count']})"
			);
		}

		$this->line('');

		$this->info('Current streaks');
		foreach(collect($players)->sortByDesc(function($p) { return $p['count']; }) as $player) {
			if ($player['count'] == 0) continue;
			$this->line(
				"{$player['from']->format('Y-m-d')}: {$allPlayers[$player['id']]->fullName()} - {$player['count']}"
			);
		}
	}
}
