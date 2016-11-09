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

	// === READ FROM DATABASE ===
	// Get incomplete tasks
	$future_query = $db->query("SELECT TASK_ID, TASK_NAME, START_DATE, STATUS 
                                FROM schedule 
                                WHERE (STATUS='NEW' OR STATUS='IN_PROGRESS')");

	// Get completed tasks
	$past_query = $db->query("SELECT TASK_ID, TASK_NAME, START_DATE, STATUS 
                                FROM schedule 
                                WHERE START_DATE<date('now', 'localtime', 'start of day')");

	//strtotime($_POST['start_date']) > strtotime('now')

	// === WRITE TO DATABASE ===
	if (isset($_POST['task_name'])) {

		if ($_POST['task_name'] != '') {
			if ($_POST['start_date'] != '' and strtotime($_POST['start_date']) > strtotime('now')) {

				// Friendly names for $_POST-ed variables
				$task_name = $_POST['task_name'];
				$start_date = $_POST['start_date'];

				// Save new task to database's schedule table:
				$db->exec('INSERT INTO schedule 
							(TASK_NAME, START_DATE, STATUS) 
							VALUES ("' . $task_name . '", "' . $start_date . '", "NEW")');

				// Get newly created task's ID:
				$id_query = $db->query("SELECT TASK_ID 
                                FROM schedule 
                                ORDER BY TASK_ID DESC LIMIT 1");
				$task_id = $id_query->fetchArray()[0];

				// Create record of this shceduled task in the tasks table:
				$db->exec('INSERT INTO tasks 
							(TASK_ID, STATUS, START_DATE) 
							VALUES (' . $task_id . ', "NEW", "' . $start_date . '")');

				// Display new task creation dialogue
				echo '	<div class="mask">
							<div class="create_task_popup">
								<div class="section_heading">New Task</div>
								<br />
								<table class="task_list"">
									<tr>
										<th style="width: 6em;">Task ID</th>
										<td>#' . $task_id . '</td>
									</tr>
									<tr>
										<th>Task Name</th>
										<td>' . $task_name . '</td>
									</tr>
									<tr>
										<th>Start Date</th>
										<td>' . $start_date . ' (YYYY-MM-DD)</td>
									</tr>
								</table>
								<p>
									The folder "emails/' . $task_id . '" has been created for this task.
									<br />
									Save your HTML email in this folder and specify the <a href="help.html#placeholder_strings" target="_blank">placeholder strings</a> below (leave any that are not needed blank):
								</p>
								<form id="task_data_fields" action="index.php" method="post">
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
								<br />
								<input type="hidden" name="new_task">
								</form>

								<form id="cancel_task_creaton" action="index.php" method="post">
									<input type="hidden" name="cancel_id" value="' . $task_id . '">
								</form>

								<div class="submit_button" onclick="document.getElementById(\'task_data_fields\').submit();">Save</div>
								<div class="submit_button cancel_button" onclick="document.getElementById(\'cancel_task_creaton\').submit();">Cancel</div>
								

							</div><!--popup -->
						</div><!--mask-->';
			} else {
				echo '<div class="overlay_message bad">New Task:<br />Please enter a Start Date in the future.</div>';
			}
		} else {
			echo '<div class="overlay_message bad">New Task:<br />Please enter a Task Name</div>';
		}	
	}

	if (isset($_POST['new_task'])) {
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

		echo '<div class="overlay_message good">New Task:<br />Task successfully scheduled</div>';

	}

	if (isset($_POST['cancel_id'])) {
		// Task creation cancelled, delete record from database

		$cancel_id = intval($_POST['cancel_id']);

		$db->exec('DELETE FROM schedule WHERE TASK_ID=' . $cancel_id);
		$db->exec('DELETE FROM tasks WHERE TASK_ID=' . $cancel_id);
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
			<div class="section_heading">New Task</div>
			<form id="new_task_form" action="index.php" method="post">
				<table>
					<tr>
						<td>Name</td>
						<td>Start date</td>
						<td></td>
					</tr>
					<tr>
						<td><input type="text" name="task_name" value="<?php echo $_POST['task_name']; ?>"></td>
						<td><input type="date" name="start_date" value="<?php echo $_POST['start_date']; ?>"></td>
						<td><div class="submit_button" onclick="document.getElementById('new_task_form').submit();">Create</div></td>
					</tr>
				</table>	
			</form>
		</div>

		<br />

		<div class="subsection">
			<div class="section_heading">Scheduled Tasks</div>
			<table class="task_list">
				<tr>
					<th style="width: 3em;">ID</th>
					<th>Name</th>
					<th style="width: 7em;">Start Date</th>
					<th style="width: 7em;">Status</th>
					<th style="width: 7em;">Emails sent</th>
				</tr>
				<?php
					// Display  details of every incomplete task in this table
					while ($scheduled = $future_query->fetchArray()) 
					{
						$task_id = intval($scheduled[0]);
						$task_name = $scheduled[1];
						$start_date = $scheduled[2];
						$status = $scheduled[3];

						$customerCount_query = $db->query("SELECT LAST_CUST_ID 
                                FROM tasks
                                WHERE TASK_ID=" . $task_id);

						$last_cust_id = $customerCount_query->fetchArray()[0];

						echo '<tr>
								<td>#' . $task_id . '</td>
								<td><a href="task.php?id=' . $task_id .'"">' . $task_name . '</a></td>
								<td>' . $start_date . '</td>
								<td>' . $status . '</td>
								<td>' . $last_cust_id . '</td>
							</tr>';
					}
				?>
			</table>
		</div>

		<br />

		<div class="subsection">
			<div class="section_heading">Completed Tasks</div>
			<table class="task_list">
				<tr>
					<th style="width: 3em;">ID</th>
					<th>Name</th>
					<th style="width: 7em;">Start Date</th>
					<th style="width: 7em;">Status</th>
					<th style="width: 7em;">Emails sent</th>
				</tr>
				<?php
					// Display  details of every completed task in this table
					while ($scheduled = $past_query->fetchArray()) 
					{
						$task_id = intval($scheduled[0]);
						$task_name = $scheduled[1];
						$start_date = $scheduled[2];
						$status = $scheduled[3];

						$customerCount_query = $db->query("SELECT LAST_CUST_ID 
                                FROM tasks
                                WHERE TASK_ID=" . $task_id);

						$last_cust_id = $customerCount_query->fetchArray()[0];

						echo '<tr>
								<td>#' . $task_id . '</td>
								<td><a href="task.php?id=' . $task_id .'"">' . $task_name . '</a></td>
								<td>' . $start_date . '</td>
								<td>' . $status . '</td>
								<td>' . $last_cust_id . '</td>
							</tr>';
					}
				?>
			</table>
		</div>

	</div> <!-- container -->
</body>
</html>
