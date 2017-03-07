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

		$task = query_fetch_one($mysqli, "SELECT parent FROM tasks WHERE user = $_SESSION[user_id] AND id = $_GET[id] LIMIT 1");
		// task exists
		if ($task)
		{
			delete_tasks_recursively($mysqli, $_GET['id']);
			header("Location: /todo/task.php?id=$task[parent]");
		}
		else
			echo 'File Not Found';
	}
	else
	{
		echo '405 Method Not Allowed';
		http_response_code(405);
		$mysqli->close();
		exit();
	}

	$mysqli->close();
?>