<?php
/**
 * Dirty PHP Example of a User Allocation
 *
 * Used at NJIT for PLA
 *
 * @license MIT
 */

/**
 * Retrieve a Random name for use
 *
 * We use this a recursive function to ensure we never get
 * an empty name.
 * 
 * @return string
 */
function random_name() {
	$file = __DIR__.'/names.txt';
	$f_contents = file($file);
	$line = $f_contents[rand(0, count($f_contents) - 1)];

	if (empty($line))
		return random_name();

	return $line;
}

class Allocator {

}