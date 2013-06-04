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

	/**
	 * Grunt work to assign users
	 * 
	 * @return void
	 */
	public function assign_users()
	{
		if (count($this->roles) == 0)
			throw new Exception('Roles are not defined for allocation.');
	}

	/**
	 * Add a user role (problem creator, solver, etc)
	 *
	 * @param string
	 */
	public function create_role($name)
	{
		$this->roles[] = $name;
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

