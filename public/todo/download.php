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

		$file = query_fetch_one($mysqli, "SELECT name,contents,LENGTH(contents) AS size FROM files WHERE user = $_SESSION[user_id] AND id = $_GET[id]");
		if ($file)
		{
			$file['name'] = addslashes($file['name']);

			header('Content-Type: application/octet-stream');
			header("Content-Length: $file[size]");
			header("Content-Disposition: attachment; filename=$file[name]");
			echo $file['contents'];
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
?>