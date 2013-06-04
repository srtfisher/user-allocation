<?php
/**
 * Dirty PHP Example of a User Allocation
 *
 * Used at NJIT for PLA
 * See also previous work {@link http://web.njit.edu/~mt85/UsersAlg.php}
 * 
 * @license MIT
 */

class Allocator {
	public $students = [];
	public $instructor;
	public static $min_size = 4;
	public static $max_size = 40;
	public $assignments = [];
	private $roles = [];

	/**
	 * @var array
	 */
	private $names = [];

	public function __construct()
	{
		$size = rand(self::$min_size, self::$max_size);
		
		$this->instructor = $this->random_name();

		// Setup the student's names
		for($i = 0; $i < $size; $i++)
			$this->students[] = $this->random_name();
	}

	/**
	 * Retrieve a Random name for use
	 *
	 * We use this a recursive function to ensure we never get
	 * an empty name.
	 * 
	 * @return string
	 */
	public function random_name() {
		$file = __DIR__.'/names.txt';
		
		if ($this->names == NULL) :
			$this->names = file($file);
			shuffle($this->names);
		endif;

		// Ensure we never repeat a name
		$line = array_pop($this->names);

		if (empty($line))
			return self::random_name();
		
		return trim($line);
	}

	public function assign_users()
	{

	}

	/**
	 * Add a user role (problem creator, solver, etc)
	 *
	 * @param string
	 * @param array
	 */
	public function create_role($name, array $rules)
	{
		$this->roles[$name] = $rules;
	}

	/**
	 * Dump the details of the allocation
	 * 
	 * @return void
	 */
	public function dump()
	{

	}
}

$allocator = new Allocator();

// Adding the rules
$allocator->create_role('problem creator', [

]);

$allocator->create_role('problem solver', [
	'unique to' => [
		'problem creator',
		'evaulator 1',
		'evaulator 2',
	]
]);

$allocator->create_role('evaulator 1', [
	'unique to' => [
		'problem creator',
		'problem solver',
		'evaulator 2',
	]
]);

$allocator->create_role('evaulator 2', [
	'unique to' => [
		'problem creator',
		'problem solver',
		'evaulator 1',
	]
]);

// Assign
$allocator->assign_users();

$allocator->dump();

