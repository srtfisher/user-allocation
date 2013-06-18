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

	public function __construct($size = 0)
	{
		if ($size == 0)
			$size = rand(self::$min_size, self::$max_size);
		
		if ($size > 2999)
			die('Size to large.');

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
		
		if (count($this->names) == 0) :
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

		// First run, add all workflows
		foreach ($this->students as $student_id => $student)
			$this->workflows[] = $this->empty_workflow();

		// Now let's find the assignes
		foreach($this->roles as $role) :
			// Get just their student IDs
			$this->roles_queue[$role] = array_keys($this->students);

			// Let's keep this very random
			shuffle($this->roles_queue[$role]);
		endforeach;

		// Go though the workflows
		foreach($this->workflows as $workflow_id => $workflow)
		{
			foreach($workflow as $workflow_role => $ignore) :
				// Start from the beginning of the queue
				$attempt_student = reset($this->roles_queue[$workflow_role]);
				$assigned = false;
				$i = 0;

				while(! $assigned) {
					$i++;

					// Preventing an infinite loop
					if ($i > count($this->roles_queue[$workflow_role]))
						break;

					// They're not a match!
					if (! $this->can_enter_workflow($attempt_student, $this->workflows[$workflow_id]))
					{
						// Point to the next student
						$attempt_student = next($this->roles_queue[$workflow_role]);
						continue;
					}

					$this->workflows[$workflow_id][$workflow_role] = $attempt_student;

					// Remove this student off the queue to be added for such role
					unset($this->roles_queue[$workflow_role][ key($this->roles_queue[$workflow_role]) ]);
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
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<script type="text/javascript">
	$(document).ready(function()
{
	console.log('ready');
	$('table td').click(function() {
		name = $(this).text();
		
		// Remove the previous ones
		$('table td[bgcolor="green"]').removeAttr('bgcolor')

		$('table td').each(function()
		{
			if ($(this).text() == name) {
				$(this).attr('bgcolor', 'green');
			}
		});
	});
});
</script>
<table width="100%" border="1">
	<thead>
		<tr>
			<?php foreach($this->roles as $role) : ?>
				<th><?php echo $role; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->workflows as $student_id => $workflow) : ?>
			<tr <?php if ($this->contains_error($workflow)) echo 'bgcolor="orange"'; ?>>
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

<!-- Now Show a user's membership table -->
<p>&nbsp;</p>

<table width="100%" border="1">
	<thead>
		<tr>
			<th>Student</th>

			<?php foreach($this->roles as $role) : ?>
				<th><?php echo $role; ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php foreach($this->students as $student_id => $student) : ?>
		<tr>
			<td><?php echo $student; ?></td>

		<?php foreach($this->roles as $role) : $found = false; ?>
			<?php
			foreach($this->workflows as $workflow) :
				if ($workflow[$role] !== NULL AND $workflow[$role] == $student_id) :
					?><td bgcolor="blue">YES</td><?php
				$found = true;
				endif;
			endforeach;
			if (! $found) : ?>
					<td bgcolor="red">NO</td>
				<?php endif;
		endforeach; endforeach; ?>
	</tr>
	</tbody>
</table>

<p><strong>Total Students:</strong> <?php echo count($this->students); ?></p>
<?php
	}
}