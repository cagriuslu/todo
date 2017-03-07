<?php
	include_once '../../includes/todo/db-connect.php';
	include_once '../../includes/todo/sec-connect.php';
	include_once '../../includes/todo/functions.php';
	include_once '../../includes/todo/todo-functions.php';

	start_secure_session();

	if (check_login($secure_mysqli) === false)
		header('Location: /todo/login.php');

	if ($_SERVER['REQUEST_METHOD'] === 'GET')
	{
		if (!isset($_GET['id']) || $_GET['id'] == 0)
		{
			echo '400 Bad Request';
			http_response_code(400);
			$mysqli->close();
			exit();
		}
	}
	else if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		$_POST = array_map('trim', $_POST);

		if (isset($_POST['id'], $_POST['parent'], $_POST['title'], $_POST['description'], $_POST['status'], $_POST['priority'], $_POST['start'], $_POST['due'], $_POST['progress']) && $_POST['id'] && $_POST['title'] != '' && $_POST['status'] != '' && $_POST['priority'] != '')
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

			if ($mysqli->query("UPDATE tasks SET parent = $parent, title = '$_POST[title]', description = $description, status = $_POST[status], priority = $_POST[priority], start = $start, due = $due, progress = $progress WHERE user = $_SESSION[user_id] AND id = $_POST[id]"))
				header("Location: /todo/task.php?id=$_POST[id]");
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

	$task = query_fetch_one($mysqli, "SELECT * FROM tasks WHERE user = $_SESSION[user_id] AND id = $_GET[id]");
	if (!$task)
	{
		header('Location: /todo/new_task.php');
		exit();
	}
?>

Time format: DD.MM.YYYY HH:MM<br>
<br>
<form action="/todo/edit_task.php" method="post">
	<input type="hidden" name="id" value="<?= $_GET['id'] ?>">
	<table>
		<tr><th colspan="2">Edit Task</th></tr>
		<tr><td>Parent Task</td><td><input type="text" name="parent" value="<?= $task['parent'] ?>"></td></tr>
		<tr><td>Title*</td><td><input type="text" name="title" value="<?= $task['title'] ?>"></td></tr>
		<tr><td>Description</td><td><input type="text" name="description" value="<?= $task['description'] ?>"></td></tr>
		<tr><td>Status*</td><td><select name="status" value="<?= $task['status'] ?>"><option value="0">New</option><option value="1">In Progress</option><option value="2">Resolved</option><option value="3">Closed</option></select></td></tr>
		<tr><td>Priority*</td><td><select name="priority" value="<?= $task['priority'] ?>"><option value="1">Normal</option><option value="0">Low</option><option value="2">High</option></select></td></tr>
		<tr><td>Started On</td><td><input type="text" name="start" value="<?= $task['start'] ?>"></td></tr>
		<tr><td>Due Date</td><td><input type="text" name="due" value="<?= $task['due'] ?>"></td></tr>
		<tr><td>Progress</td><td><input type="text" name="progress" value="<?= $task['progress'] ?>"></td></tr>
	</table>
	<input type="submit" value="Edit">
</form><br>

<?php
	include('../../templates/todo/footer.php');
	$mysqli->close();
?>