<?php

$path = '/Users/callumacrae/Music/iTunes/iTunes Music Library Backup.xml';

function parseValue( $valueNode ) {
  $valueType = $valueNode->nodeName;

  $transformerName = "parse_$valueType";

  if ( is_callable($transformerName) ) {
    // there is a transformer function for this node type
    return call_user_func($transformerName, $valueNode);
  }

  // if no transformer was found
  return null;
}

function parse_integer( $integerNode ) {
  return $integerNode->textContent;
}

function parse_string( $stringNode ) {
  return $stringNode->textContent;  
}

function parse_date( $dateNode ) {
  return $dateNode->textContent;
}

function parse_true( $trueNode ) {
  return true;
}

function parse_false( $trueNode ) {
  return false;
}

function parse_dict( $dictNode ) {
  $dict = array();

  // for each child of this node
  for (
    $node = $dictNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeName == "key" ) {
      $key = $node->textContent;

      $valueNode = $node->nextSibling;

      // skip text nodes
      while ( $valueNode->nodeType == XML_TEXT_NODE ) {
        $valueNode = $valueNode->nextSibling;
      }

      // recursively parse the children
      $value = parseValue($valueNode);

      $dict[$key] = $value;
    }
  }

  return $dict;
}

function parse_array( $arrayNode ) {
  $array = array();

  for (
    $node = $arrayNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeType == XML_ELEMENT_NODE ) {
      array_push($array, parseValue($node));
    }
  }

  return $array;
}

$plistDocument = new DOMDocument();
$plistDocument->load($path);

function parsePlist( $document ) {
  $plistNode = $document->documentElement;

  $root = $plistNode->firstChild;

  // skip any text nodes before the first value node
  while ( $root->nodeName == "#text" ) {
    $root = $root->nextSibling;
  }

  return parseValue($root);
}

$array = parsePlist($plistDocument);

$total_time = 0;

foreach ($array['Tracks'] as $track)
{
	if (empty($track['Play Count']))
	{
		continue;
	}

	$total_time += $track['Total Time'] * $track['Play Count'];
}

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