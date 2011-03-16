<?php

class plist
{
	public function parse($path)
	{
		$document = new DOMDocument();
		$document->load($path);

		$plist_node = $document->documentElement;

		$root = $plist_node->firstChild;

		// skip any text nodes before the first value node
		while ($root->nodeName == '#text')
		{
			$root = $root->nextSibling;
		}

		return self::parse_value($root);
	}
	
	public function parse_value($value_node)
	{
		$value_type = $value_node->nodeName;

		$transformer_name = "parse_$value_type";

		if (is_callable("self::$transformer_name"))
		{
			return self::$transformer_name($value_node);
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
				$value = self::parse_value($value_node);

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
				array_push($array, self::parse_value($node));
			}
		}

		return $array;
	}
}