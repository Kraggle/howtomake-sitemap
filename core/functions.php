<?php

/**
 * These core functions are used for all How to Make plugins and are created only once.
 * 
 * @package howtomake
 * @author  kraggle
 */

if (!defined('IS_DEBUG')) 
	define('IS_DEBUG', true);
 
if (!function_exists('logger')) {
	function logger() {
		if (!IS_DEBUG) return;

		$db = array_shift(debug_backtrace());
		$line = $db['line'];
		$file = $db['file'];

		$msg = "$file:$line [logger]";

		foreach (func_get_args() as $arg) {
			$msg .= "\n" . (in_array(gettype($arg), ['string', 'double', 'integer']) ? $arg : json_encode($arg, JSON_PRETTY_PRINT));
		}

		error_log($msg . "\n");
	}
}

if (!function_exists('to_object')) {
	/**
	 * Converts a multidimensional array to an object.
	 * 
	 * @param array   $array The array to convert.
	 * @return object The converted array.
	 */
	function to_object($array) {
		return json_decode(json_encode($array), false);
	}
}

if (!function_exists('to_array')) {
	/**
	 * Converts an object to a multidimensional array.
	 * 
	 * @param object $object The object to convert.
	 * @return array The converted object.
	 */
	function to_array($object) {
		return json_decode(json_encode($object), true);
	}
}
