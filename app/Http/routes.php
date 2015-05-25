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

Route::get('/', 'PlayerController@index');

Route::model('matches', 'App\Match');
Route::model('players', 'App\Player');
Route::model('teams', 'App\Team');

Route::resource('matches', 'MatchController');
Route::resource('players', 'PlayerController');
Route::resource('teams', 'TeamController');