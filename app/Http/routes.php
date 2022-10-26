<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['web']], function() {
	Route::get('/', 'PlayerController@summary');

	Route::resource('matches', 'MatchController');
	Route::get('players/history', 'PlayerController@history');
	Route::get('players/matrix', 'PlayerController@matrix');
	Route::resource('players', 'PlayerController');
	Route::resource('teams', 'TeamController');
	Route::resource('seasons', 'SeasonController');

	Route::get('matches/create', 'AdminController@createMatch');
	Route::post('matches', 'AdminController@storeMatch');
});

Route::get('data.json', 'DataController@all');
Route::group(['prefix' => 'api'], function() {
	Route::get('players', 'DataController@players');
	Route::get('players/{player}', 'DataController@player');
	Route::get('seasons', 'DataController@seasons');
	Route::get('seasons/{season}', 'DataController@seasons');
	Route::group(['prefix' => 'v2'], function() {
		Route::get('seasons', 'DataController@allSeasons');
		Route::get('seasons/{year}', 'DataController@seasons');
	});
	Route::get('matches', 'DataController@matches');
	Route::get('venues', 'DataController@venues');
});
