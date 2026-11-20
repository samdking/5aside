@extends('layouts.default')

@section('content')

<h2>Seasons</h2>

<div class="season-blocks">
	@foreach($seasons as $year => $season)
		@include('seasons.partials.block', [
			'title' => $year,
			'href' => route('seasons.show', $year),
			'season' => $season,
			'champion' => $champions->get($year),
			'inProgress' => $year == $currentYear,
		])
	@endforeach

	@if($allTime && $allTime['total_matches'])
		@include('seasons.partials.block', [
			'title' => 'All time',
			'href' => '/players',
			'season' => $allTime,
			'champion' => null,
			'inProgress' => false,
			'modifier' => 'season-block--all-time',
		])
	@endif
</div>

@stop
