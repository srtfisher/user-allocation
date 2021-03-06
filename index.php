<?php
/**
 * Dirty PHP Example of a User Allocation
 *
 * Used at NJIT for PLA
 * See also previous work {@link http://web.njit.edu/~mt85/UsersAlg.php}
 * 
 * @license MIT
 */

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$starttime = $mtime; 

require 'allocator.php';
$allocator = new Allocator((isset($_GET['size'])) ? (int) $_GET['size'] : 0);

// Adding the roles
$allocator->create_role('problem creator');
$allocator->create_role('problem solver');
$allocator->create_role('evaluator 1', array('problem creator', 'problem solver'));
$allocator->create_role('evaluator 2', array('problem creator', 'problem solver'));

// Assign
try {
	$execute = $allocator->assignmentRun();
} catch (Exception $e) {
	die(sprintf('ERROR: <strong>%s</strong>', $e->getMessage()));
}

$execute->dump();

$mtime = microtime(); 
$mtime = explode(" ",$mtime); 
$mtime = $mtime[1] + $mtime[0]; 
$endtime = $mtime; 
$totaltime = ($endtime - $starttime); 
echo "<p>Created in ".$totaltime." seconds.</p>"; 
