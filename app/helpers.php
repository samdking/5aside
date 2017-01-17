<?php

function tally($number)
{
	$units = [];

	for($i = 0; $i < $number; $i++) {
		$units[] = '|';
		if (($i+1) % 5 === 0) {
			$units[] = ' ';
		}
	}

	return implode($units);
}