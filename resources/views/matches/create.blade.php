<form method="post" action="/matches">
	<h1>Add match</h1>
	<textarea name="match" cols="100" rows="1" style="padding: 10px; text-align: center; font-size: 16px; line-height: 40px"></textarea>
	<input style="display: block; margin: 1em 0" type="submit">
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
</form>