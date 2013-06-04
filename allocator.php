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
	public $workflows = [];
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

		// Reset it
		$this->workflows = [];

		// First run, add all work flows with the respective roles not defined yet
		foreach ($this->students as $student)
			$this->workflows[$student] = $this->empty_workflow();
		


	}

	/**
	 * Empty Workflow
	 * The default values for a workflow
	 *
	 * @return array
	 */
	public function empty_workflow()
	{
		$i = [];
		foreach($this->roles as $r) $i[$r] = NULL;

		return $i;
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
		//@header("Content-Type: text/plain");

		//var_dump($this->workflows);
		
		?>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>&nbsp;</th>
			<?php foreach($this->roles as $role) : ?>
				<th><?php echo $role; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->workflows as $name => $workflow) : ?>
			<tr>
				<th><?php echo $name; ?></th>

				<?php foreach($workflow as $role => $assigne) :
					if ($assigne == NULL) :
						?><td bgcolor="red">NONE</td><?php
					else :
						?><td><?php echo $assigne; ?></td><?php
					endif;
				endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<p><strong>Total Students:</strong> <?php echo count($this->students); ?></p>
<?php
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

