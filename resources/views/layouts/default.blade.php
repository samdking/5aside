<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>5-a-side Leaderboard</title>

	<link href="/css/app.css?{{ filemtime('css/app.css') }}" rel="stylesheet">
</head>
<body>

<h1>5-a-side stats</h1>
<nav>
	<a href="/">Home</a> |
	Seasons:
		@foreach(range(0, 3) as $i)
			<a href="{!! route('players.index', [
				'from' => date('Y') - $i . '-01-01',
				'to' => date('Y') - $i . '-12-31'
			]) !!}">{{ date('Y') - $i }}</a> |
		@endforeach
	<a href="/players">All-time Table</a> |
	<a href="/players/history">History</a> |
	<a href="/matches">Matches</a>
</nav>
@yield('content')

</body>
</html>
