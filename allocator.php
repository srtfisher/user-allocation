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

	public $runCount = 0;

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
			//shuffle($this->names);
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
			// Loop though each role inside of the workflow
			// 
			// Loop though all the users in the queue
			// 
			// Can join: assign and remove from queue
			// Can't join: point to next user in queue
			foreach($workflow as $role => $ignore) :
				// Start from the beginning of the queue
				foreach($this->roles_queue[$role] as $queue_id => $user_id) :
					// They're not a match -- skip to the next user in queue
					if ($this->can_enter_workflow($user_id, $this->workflows[$workflow_id]))
					{
						$this->workflows[$workflow_id][$role] = $user_id;

						// Remove this student from the queue
						unset($this->roles_queue[$role][$queue_id]);
						break;
					}
				endforeach;
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
	public function can_enter_workflow($user_id, $workflow)
	{
		foreach($workflow as $role => $assigne)
		{
			if ($assigne !== NULL AND (int) $assigne === (int) $user_id)
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
		
		// Check if it contains unassigned users
		foreach ($workflow as $role => $user) :
			if ($user === NULL) return TRUE;
		endforeach;

		return FALSE;
	}

	/**
	 * See if an array of workflows contains any errors
	 *
	 * @return bool
	 */
	public function contains_errors($workflows)
	{
		foreach($workflows as $workflow) :
			if ($this->contains_error($workflow) ) return TRUE;
		endforeach;

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

	public function assignmentRun($maxRuns = 20)
	{
		$index = array();
		$errorIndex = array();
		$runCount = 0;

		for ($i = 0; $i < $maxRuns; $i++) :
			$this->runCount++;

			$this->assign_users();

			$hasErrors = $this->contains_errors($this->getWorkflows());

			if (! $hasErrors)
				return $this;
		endfor;

		return $this;
	}

	/**
	 * Dump the details of the allocation
	 * 
	 * @return void
	 */
	public function dump()
	{
		?>
<form action="/" method="GET">
	<input type="number" name="size" value="<?php if (isset($_GET['size'])) echo $_GET['size']; ?>" />
	<button type="submit">Generate Allocation</button>
</form>

<p><a href="/">Random Allocation</a></p>

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
					if ($assigne === NULL) :
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
				<th>is <?php echo $role; ?>?</th>
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
<p><strong>Total Runs:</strong> <?php echo $this->runCount; ?></p>
<pre>
<?php echo print_r($this->workflows); ?>
</pre>
<?php
	}
}