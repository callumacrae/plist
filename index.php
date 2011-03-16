<?php

$path = '/Users/callumacrae/Music/iTunes/iTunes Music Library Backup.xml';

function parse_value($value_node)
{
	$value_type = $value_node->nodeName;

	$transformer_name = "parse_$value_type";

	if (is_callable($transformer_name))
	{
		// there is a transformer function for this node type
		return call_user_func($transformer_name, $value_node);
	}

	// if no transformer was found
	return null;
}

function parse_integer($integer_node)
{
	return $integer_node->textContent;
}

function parse_string($string_node)
{
	return $string_node->textContent;  
}

function parse_date($date_node)
{
	return $date_node->textContent;
}

function parse_true($true_node)
{
	return true;
}

function parse_false($true_node)
{
	return false;
}

function parse_dict($dict_node)
{
	$dict = array();

	// for each child of this node
	for ($node = $dict_node->firstChild; $node != null; $node = $node->nextSibling)
	{
		if ($node->nodeName == 'key')
		{
			$key = $node->textContent;

			$value_node = $node->nextSibling;

			// skip text nodes
			while ($value_node->nodeType == XML_TEXT_NODE)
			{
				$value_node = $value_node->nextSibling;
			}

			// recursively parse the children
			$value = parse_value($value_node);

			$dict[$key] = $value;
		}
	}

	return $dict;
}

function parse_array($array_node)
{
	$array = array();

	for ($node = $array_node->firstChild; $node != null; $node = $node->nextSibling)
	{
		if ($node->nodeType == XML_ELEMENT_NODE)
		{
			array_push($array, parse_value($node));
		}
	}

	return $array;
}

$plistDocument = new DOMDocument();
$plistDocument->load($path);

function parse_plist($document)
{
	$plist_node = $document->documentElement;

	$root = $plist_node->firstChild;

	// skip any text nodes before the first value node
	while ($root->nodeName == '#text')
	{
		$root = $root->nextSibling;
	}

	return parse_value($root);
}

$array = parse_plist($plistDocument);

$total_time = 0;

foreach ($array['Tracks'] as $track)
{
	if (empty($track['Play Count']))
	{
		continue;
	}

	$total_time += $track['Total Time'] * $track['Play Count'];
}

$total_time /= 1000;

echo PHP_EOL;
echo 'Total time in seconds: ' . $total_time . PHP_EOL;
echo 'Total time in hours: ' . $total_time/3600 . PHP_EOL;
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