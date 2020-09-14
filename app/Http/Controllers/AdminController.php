<?php

namespace App\Http\Controllers;

use App\MatchCreator;
use Illuminate\Http\Request;

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
			$match->push();
		});

		return redirect()->route('matches.show', [$match->id]);
	}
}