<html>
<head>
	<title>mailTime</title>

	<link rel="stylesheet" href="styles/main.css">
	<link href="https://fonts.googleapis.com/css?family=Ubuntu" rel="stylesheet">

</head>

<body>

<?php
	// === CONNECT TO DATABASE ===
	class MyDB extends SQLite3
	{
		function __construct()
		{
		    $this->open('../data/mailTime_data.db'); 
		}
	}
	$db = new MyDB();
	if(!$db)
	{
		echo '<div class="dbStatus">Database Connection Error: ' . $db->lastErrorMsg() . '</div>';
	}

	date_default_timezone_set("GMT"); // Set timezone for all date/time operations


	$task_id = intval($_GET['id']);

	// === READ FROM DATABASE ===
	// Get selected task's details
	$task_query = $db->query("SELECT TASK_NAME, START_DATE, STATUS 
                                FROM schedule 
                                WHERE TASK_ID=" . $task_id);
	$task_data = $task_query->fetchArray();

	$customerCount_query = $db->query("SELECT LAST_CUST_ID 
                                FROM tasks
                                WHERE TASK_ID=" . $task_id);
	$last_cust_id = $customerCount_query->fetchArray()[0];

	$task_name = $task_data[0];
	$start_date = $task_data[1];
	$status = $task_data[2];


	// === WRITE TO DATABASE ===
	if (isset($_POST['update_task'])) {
		// Alter task name and/or start date
		echo 'test';
		if ($_POST['task_name'] != '') {
			if ($_POST['start_date'] != '' and strtotime($_POST['start_date']) > strtotime('now')) {
					$db->exec('UPDATE schedule SET STATUS="NEW" TASK_NAME="' . $_POST['task_name'] . '", START_DATE="' . $_POST['start_date'] . '" WHERE TASK_ID=' . $task_id);
					$db->exec('UPDATE tasks SET STATUS="NEW",  WHERE TASK_ID=' . $task_id);
			} else {
				echo '<div class="overlay_message bad">Update Task:<br />Please enter a Start Date in the future.</div>';
			}
		} else {
			echo '<div class="overlay_message bad">Update Task:<br />Please enter a Task Name</div>';
		}	
		echo '<div class="overlay_message good">Update Task:<br />Task successfully updated</div>';
	}

	if (isset($_POST['delete'])) {
		// Delete task

		$db->exec('DELETE FROM schedule WHERE TASK_ID=' . $task_id);
		$db->exec('DELETE FROM tasks WHERE TASK_ID=' . $task_id);

		echo '<div class="overlay_message good">Delete Task:<br />Task successfully deleted</div>';
		header("refresh:3; url=index.php");
	}

	if (isset($_POST['update_customers'])) {
		// New task details entered via the create_task_popup form
		$data_field_1 = $_POST['data_field_1'];
		$data_field_2 = $_POST['data_field_2'];
		$data_field_3 = $_POST['data_field_3'];

		if ($data_field_1 != '') {
			$db->exec('UPDATE customers
					SET DATA_FIELD_1_LOC="' . $data_field_1 . '"');
		}

		if ($data_field_2 != '') {
			$db->exec('UPDATE customers
					SET DATA_FIELD_2_LOC="' . $data_field_2 . '"');
		}

		if ($data_field_3 != '') {
			$db->exec('UPDATE customers
					SET DATA_FIELD_3_LOC="' . $data_field_3 . '"');
		}
		echo '<div class="overlay_message good">Update Customers:<br />Customer placeholder strings updated</div>';
	}

?>

	<div class="container">
		<div class="header">
            <a href="index.php"><h1>mailTime</h1></a>
            <ul class="nav">
                <li><a href="index.php">Schedule</a></li>
                <li><a href="customer.php">Customers</a></li>
                <li><a href="help.html">Help</a></li>
            </ul>
        </div>

		<br />

		<div class="subsection">
			<div class="section_heading">Task Data</div>
			<form id="update_task" action="task.php?id=<?php echo $task_id; ?>" method="post">
				<table class="task_list">
					<tr>
						<th style="width: 6em;">Task ID</th>
						<td>#<?php echo $task_id; ?></td>
					</tr>
					<tr>
						<th>Task Name</th>
						<td><input type="text" name="task_name" value="<?php echo $task_name; ?>"></td>
					</tr>
					<tr>
						<th>Start Date</th>
						<td><input type="text" name="start_date" value="<?php echo $start_date; ?>"> (YYYY-MM-DD)</td>
					</tr>
					<tr>
						<th>Status</th>
						<td><?php echo $status; ?></td>
					</tr>
					<tr>
						<th>Emails Sent</th>
						<td><?php echo $last_cust_id; ?></td>
					</tr>
				</table>
				<form type="hidden" name="update_task">
				<div class="submit_button" style="float:right; width:10em; margin-right: 1em;" onclick="document.getElementById('update_task').submit();">Save Changes</div>
			</form>
			<br />
			<br />
		</div>

		<br />

		<div class="subsection">
			<div class="section_heading">Delete Task</div>
			<p>Permanently remove "<?php echo $task_name; ?>" from the schedule. <em>This process cannot be reversed.</em></p>
			<form id="delete_task" action="task.php?id=<?php echo $task_id; ?>" method="post">
				<input type="hidden" name="delete">
				<div class="submit_button" style="float:right; width:10em; margin-right: 1em;" onclick="document.getElementById('delete_task').submit();">Delete Task</div>
			</form>
			<br />
			<br />
		</div>

		<br />

		<div class="subsection">
			<div class="section_heading">Customer Data</div>
			<form id="update_customer" action="task.php?id=<?php echo $task_id; ?>" method="post">
				<table class="task_list">
					<tr>
						<th>Placeholder</th>
						<th>Customer Database Field</th>
					</tr>
					<tr>
						<td>^FIRSTNAME^</td>
						<td>FIRST_NAME</td>
					</tr>
					<tr>
						<td>^SURNAME^</td>
						<td>LAST_NAME</td>
					</tr>
					<tr>
						<td>^EMAIL^</td>
						<td>EMAIL</td>
					</tr>
					<tr>
						<td>^<input type="text" name="data_field_1">^</td>
						<td>DATA_FIELD_1</td>
					</tr>
					<tr>
						<td>^<input type="text" name="data_field_2">^</td>
						<td>DATA_FIELD_2</td>
					</tr>
					<tr>
						<td>^<input type="text" name="data_field_3">^</td>
						<td>DATA_FIELD_3</td>
					</tr>
				</table>
				<input type="hidden" name="update_customers">
				<div class="submit_button" style="float:right; width:10em; margin-right: 1em;" onclick="document.getElementById('update_customer').submit();">Save Changes</div>
			</form>
			<br />
			<br />
		</div>

	</div> <!-- container -->
</body>
</html>
