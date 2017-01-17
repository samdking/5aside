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

	public function storeMatch(Request $request)
	{
		\DB::transaction(function() use ($request) {
			$creator = new MatchCreator;
			$match = $creator->parse($request->get('match'));
			$match->push();
		});

		return redirect()->back();
	}
}