<?php

/**
 * @package callumacrae's plist parser
 * @version 1.0.0
 * @copyright (c) Callum Macrae 2011
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC by-sa
 *
 * Demo: iTunes library parser
 *
 * DO NOT USE THIS ON A LIVE SERVER, EVER!
 * The file name is NOT escaped
 */

require('./plist.php');

//default file to parse
$default = '/Users/callumacrae/Music/iTunes/iTunes Music Library.xml';

$array = plist::parse(isset($_GET['path']) ? $_GET['path'] : $default);

$total_time = 0;

foreach ($array['Tracks'] as $track)
{
	if (empty($track['Play Count']) || empty($track['Total Time']))
	{
		continue;
	}

	$total_time += $track['Total Time'] * $track['Play Count'];
}

$total_time /= 1000;

echo PHP_EOL;
echo 'Total time in seconds: ' . $total_time . PHP_EOL;
echo 'Total time in hours: ' . $total_time / 3600 . PHP_EOL;
echo PHP_EOL;

$units = array(
	'year'		=> 29030400, // seconds in a year
	'month'		=> 2419200,  // seconds in a month
	'week'		=> 604800,   // seconds in a week
	'day'		=> 86400,    // seconds in a day
	'hour'		=> 3600,     // seconds in an hour
	'minute'	=> 60,       // seconds in a minute
	'second'	=> 1         // 1 second
);

$diff = $total_time;
$output = '';

foreach($units as $unit => $mult)
{
	if ($diff >= $mult)
	{
		$output .= (($mult != 1) ? ', ' : ' and ') . intval($diff / $mult) . ' ' . $unit . ((intval($diff / $mult) == 1) ? null : 's');
		$diff -= intval($diff / $mult) * $mult;
	}
}
$output = substr($output, strlen(', '));

echo $output . PHP_EOL;
echo PHP_EOL;
