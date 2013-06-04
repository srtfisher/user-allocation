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
	private $students = array();
	private $instructor;
	public static $min_size = 10;
	public static $max_size = 40;
	private $workflows = array();

	private $roles = array();
	private $roles_rules = array();

	private $roles_queue = array();

	/**
	 * @var array
	 */
	private $names = array();

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
		$this->workflows = array();

		// First run, add all work flows with the respective roles not defined yet
		foreach ($this->students as $student_id => $student)
			$this->workflows[$student_id] = $this->empty_workflow();
		

		// Now let's find the assignes
		foreach($this->roles as $role) :
			// Get just their student IDs
			$this->roles_queue[$role] = array_keys($this->students);

			// Let's keep this very random
			shuffle($this->roles_queue[$role]);
		endforeach;

		// Go though the workflows
		foreach($this->workflows as $student_id => $workflow)
		{
			foreach($workflow as $workflow_role => $ignore) :
				$attempt_student = reset($this->roles_queue[$workflow_role]);
				$assigned = false;
				$i = 0;

				while(! $assigned) {
					$i++;

					if ($i > count($this->roles_queue[$workflow_role]))
						break;

					// They're not a match!
					if (! $this->can_enter_workflow($attempt_student, $this->workflows[$student_id]))
					{
						// Point to the next student
						$attempt_student = next($this->roles_queue[$workflow_role]);
						continue;
					}

					$this->workflows[$student_id][$workflow_role] = $attempt_student;

					// Remove this student off the queue to be added for such role
					array_shift($this->roles_queue[$workflow_role]);
					$assigned = TRUE;
				}
			endforeach;
		}
	}

	/**
	 * Identify if a user can enter a specific workflow
	 *
	 * Helper function to see if a user is already in a
	 * workflow (cannot join then).
	 * 
	 * @param int
	 * @param array
	 * @return bool
	 */
	public function can_enter_workflow($student, $workflow)
	{
		foreach($workflow as $role => $assigne)
		{
			if ((int) $assigne == (int) $student)
				return FALSE;
		}
		return TRUE;
	}

	/**
	 * Does a workflow contain a duplicate error?
	 *
	 * @return bool
	 */
	public function contains_error($workflow)
	{
		if ($workflow !== array_unique($workflow, SORT_NUMERIC))
			return TRUE;
		else
			return FALSE;
	}

	/**
	 * Empty Workflow
	 * The default values for a workflow
	 *
	 * @return array
	 */
	public function empty_workflow()
	{
		$i = array();
		foreach($this->roles as $r) $i[$r] = NULL;

		return $i;
	}

	/**
	 * Add a user role (problem creator, solver, etc)
	 *
	 * @param string
	 * @param string
	 */
	public function create_role($name, $rules = array())
	{
		$this->roles[] = $name;
		$this->roles_rules[$name] = $rules;
	}

	/**
	 * Get the Workflows
	 *
	 * @return array
	 */
	public function getWorkflows()
	{
		return $this->workflows;
	}


	/**
	 * Dump the details of the allocation
	 * 
	 * @return void
	 */
	public function dump()
	{
		?>
<table width="100%" border="1">
	<thead>
		<tr>
			<th>problem creator</th>
			<?php foreach($this->roles as $role) : ?>
				<th><?php echo $role; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->workflows as $student_id => $workflow) : ?>
			<tr <?php if ($this->contains_error($workflow)) echo 'bgcolor="orange"'; ?>>
				<th><?php echo $this->students[$student_id]; ?></th>

				<?php foreach($workflow as $role => $assigne) :
					if ($assigne == NULL) :
						?><td bgcolor="red">NONE</td><?php
					else :
						?><td><?php echo $this->students[$assigne]; ?></td><?php
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