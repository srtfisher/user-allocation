<?php
/**
 * Dirty PHP Example of a User Allocation
 *
 * Used at NJIT for PLA
 * See also previous work {@link http://web.njit.edu/~mt85/UsersAlg.php}
 * 
 * @license MIT
 */

require 'allocator.php';
$allocator = new Allocator();

// Adding the roles
$allocator->create_role('problem creator');
$allocator->create_role('problem solver');
$allocator->create_role('evaluator 1');
$allocator->create_role('evaluator 2');

// Assign
try {
	$allocator->assign_users();
} catch (Exception $e) {
	die(sprintf('ERROR: <strong>%s</strong>', $e->getMessage()));
}

$allocator->dump();

