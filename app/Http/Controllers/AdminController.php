<?php

namespace App\Http\Controllers;

use App\MatchCreator;
use App\MatchResult;
use App\MissedMatch;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class AdminController extends Controller
{
	public function createMatch()
	{
		return view('matches.create');
	}

	public function storeMatch(Request $request, MatchCreator $creator)
	{
		$match = null;

		\DB::transaction(function() use ($request, $creator, &$match) {
			$match = $creator->parse($request->get('match'));

			if ($match instanceof MatchResult) {
				$match->push();
			}

			$client = new Client();
			$res = $client->request('POST', 'https://api.netlify.com/build_hooks/637de03bcc1086005c254056?trigger_title=New+Match+Added');
		});

		if ($match instanceof MissedMatch) {
			return redirect()->route('players.index');
		}

		return redirect()->route('matches.show', [$match->id]);
	}
}