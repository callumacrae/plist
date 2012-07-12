<?php

/**
 * @package callumacrae/plist
 * @version 1.1.0
 * @copyright (c) Callum Macrae 2011 - 2012
 * @license http://creativecommons.org/licenses/by-sa/3.0/ CC by-sa
 * 
 * Thanks for using (or looking at) this plist parser! It's pretty
 * easy to use, just do $yourvar = plist::parse('path/to/file') and
 * an array will be returned. Please visit my website:
 * http://lynxphp.com/
 */

class plist {
	/**
	 * Parse given plist file and returned parsed array.
	 *
	 * @static
	 *
	 * @param string $path Path to plist file to parse.
	 * @return mixed Parsed data.
	 */
	public static function Parse($path) {
		$document = new DOMDocument();
		$document->load($path);

		$plist_node = $document->documentElement;

		$root = $plist_node->firstChild;

		// Skip any text nodes before the first value node
		while ($root->nodeName == '#text') {
			$root = $root->nextSibling;
		}

		return self::parse_value($root);
	}

	/**
	 * Parses given DOMDocument. Internal.
	 *
	 * @static
	 * @private
	 *
	 * @param DOMDocument $value_node DOMDocument to parse.
	 * @return mixed Parsed value (or null if data doesn't exist).
	 */
	private static function parse_value($value_node) {
		$value_type = $value_node->nodeName;

		$transformer_name = "parse_$value_type";

		if (is_callable("self::$transformer_name")) {
			return self::$transformer_name($value_node);
		}

		// If no transformer was found
		return null;
	}

	/**
	 * Transformer. Parses given integer.
	 *
	 * @static
	 * @private
	 *
	 * @param Node $integer_node The integer node.
	 * @return int Parsed integer.
	 */
	private static function parse_integer($integer_node) {
		return $integer_node->textContent;
	}

	/**
	 * Transformer. Parses given string.
	 *
	 * @static
	 * @private
	 *
	 * @param Node $string_node The string node.
	 * @return string Parsed string.
	 */
	private static function parse_string($string_node) {
		return $string_node->textContent;  
	}

	/**
	 * Transformer. Parses given date.
	 *
	 * @static
	 * @private
	 *
	 * @param Node $date_node The date node.
	 * @return string Parsed date.
	 */
	private static function parse_date($date_node) {
		return $date_node->textContent;
	}

	/**
	 * Transformer. Parses given true value (returns true).
	 *
	 * @static
	 * @private
	 *
	 * @param Node $true_node The true node.
	 * @return bool True.
	 */
	private static function parse_true($true_node) {
		return true;
	}
	/**
	 * Transformer. Parses given false value (returns false).
	 *
	 * @static
	 * @private
	 *
	 * @param Node $false_node The false node.
	 * @return bool False.
	 */
	private static function parse_false($false_node) {
		return false;
	}

	/**
	 * Transformer. Parses given dictionary into a PHP array.
	 *
	 * @static
	 * @private
	 *
	 * @param Node $dict_node The dictionary node.
	 * @return array The parsed array.
	 */
	private static function parse_dict($dict_node) {
		$dict = array();

		// For each child of this node
		$node = $dict_node->firstChild;
		for (; $node != null; $node = $node->nextSibling) {
			if ($node->nodeName == 'key') {
				$key = $node->textContent;

				$value_node = $node->nextSibling;

				// Skip text nodes
				while ($value_node->nodeType == XML_TEXT_NODE) {
					$value_node = $value_node->nextSibling;
				}

				// Recursively parse the children
				$value = self::parse_value($value_node);

				$dict[$key] = $value;
			}
		}

		return $dict;
	}

	/**
	 * Transformer. Parses given array into a PHP array.
	 *
	 * @static
	 * @private
	 *
	 * @param Node $array_node The array node.
	 * @return array The parsed array.
	 */
	private static function parse_array($array_node) {
		$array = array();

		$node = $array_node->firstChild;
		for (; $node != null; $node = $node->nextSibling) {
			if ($node->nodeType == XML_ELEMENT_NODE) {
				array_push($array, self::parse_value($node));
			}
		}

		return $array;
	}
}
