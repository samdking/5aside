@extends('layouts.default')

@section('content')

<h2>Seasons</h2>

<div class="season-blocks">
	@foreach($seasons as $year => $season)
		@include('seasons.partials.block', [
			'title' => $year,
			'href' => route('seasons.show', $year),
			'season' => $season,
			'players' => $playerCounts->get($year, 0),
			'champion' => $champions->get($year),
			'inProgress' => $year == $currentYear,
		])
	@endforeach

	@if($allTime && $allTime['total_matches'])
		@include('seasons.partials.block', [
			'title' => 'All time',
			'href' => '/players',
			'season' => $allTime,
			'players' => $playerCounts->get('all', 0),
			'champion' => null,
			'inProgress' => false,
			'modifier' => 'season-block--all-time',
		])
	@endif
</div>

@stop
