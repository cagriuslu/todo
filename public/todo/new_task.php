<?php
	include_once '../../includes/todo/db-connect.php';
	include_once '../../includes/todo/sec-connect.php';
	include_once '../../includes/todo/functions.php';
	include_once '../../includes/todo/todo-functions.php';

	start_secure_session();

	if (check_login($secure_mysqli) === false)
		header('Location: /todo/login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'GET')
	{ }
	else if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$_POST = array_map('trim', $_POST);

		if (isset($_POST['parent'], $_POST['title'], $_POST['description'], $_POST['status'], $_POST['priority'], $_POST['start'], $_POST['due'], $_POST['progress']) && $_POST['title'] != '' && $_POST['status'] != '' && $_POST['priority'] != '')
		{
			// if parent task is provided
			if ($_POST['parent'] !== '' && $_POST['parent'] != 0)
			{
				if (query_fetch_one($mysqli, "SELECT NULL FROM tasks WHERE id = $_POST[parent] AND user = $_SESSION[user_id]"))
					$parent = $_POST['parent'];
				else
				{
					echo 'Parent Task Not Found';
					exit();
				}
			}
			else
				$parent = '0';

			$description = mysqlString($_POST['description']);
			$start = mysqlDatetime($_POST['start']);
			$due = mysqlDatetime($_POST['due']);
			$progress = mysqlNumber($_POST['progress']);

			if ($mysqli->query("INSERT INTO tasks (user, parent, title, description, status, priority, start, due, progress) VALUES ($_SESSION[user_id], $parent, '$_POST[title]', $description, $_POST[status], $_POST[priority], $start, $due, $progress)"))
				header("Location: /todo/task.php?id=$parent");
			else
				echo 'ERROR' . $mysqli->error;
			exit();
		}
		else
		{
			echo 'Please fill all required field.';
			exit();
		}
	}
	else
	{
		echo '405 Method Not Allowed';
		http_response_code(405);
		$mysqli->close();
		exit();
	}
?>

<?php
	include('../../templates/todo/header.php');
	include('../../templates/todo/navigator.php');

	$parent = isset($_GET['parent']) ? $_GET['parent'] : '';
?>

Time format: DD.MM.YYYY HH:MM<br>
<br>
<form action="/todo/new_task.php" method="post">
	<table>
		<tr><th colspan="2">New Task</th></tr>
		<tr><td>Parent Task</td><td><input type="text" name="parent" value="<?= $parent ?>"></td></tr>
		<tr><td>Title*</td><td><input type="text" name="title"></td></tr>
		<tr><td>Description</td><td><input type="text" name="description"></td></tr>
		<tr><td>Status*</td><td><select name="status"><option value="0">New</option><option value="1">In Progress</option><option value="2">Resolved</option><option value="3">Closed</option></select></td></tr>
		<tr><td>Priority*</td><td><select name="priority"><option value="1">Normal</option><option value="0">Low</option><option value="2">High</option></select></td></tr>
		<tr><td>Started On</td><td><input type="text" name="start"></td></tr>
		<tr><td>Due Date</td><td><input type="text" name="due"></td></tr>
		<tr><td>Progress</td><td><input type="text" name="progress"></td></tr>
	</table>
	<input type="submit" value="Insert">
</form><br>

<?php
	include('../../templates/todo/footer.php');
	$mysqli->close();
?>