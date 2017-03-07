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

		// check if all required files are filled
		if (isset($_POST['id'], $_POST['task'], $_POST['name'], $_POST['description']) && $_POST['id'] != '' && $_POST['task'] != '' && $_POST['name'] != '')
		{
			// check if task exits
			if (query_fetch_one($mysqli, "SELECT NULL FROM tasks WHERE id = $_POST[task] AND user = $_SESSION[user_id]"))
			{
				$description = mysqlString($_POST['description']);

				if ($mysqli->query("UPDATE files SET task = $_POST[task], name = '$_POST[name]', description = $description WHERE user = $_SESSION[user_id] AND id = $_POST[id]"))
					header("Location: /todo/file.php?id=$_POST[id]");
				else
					echo 'ERROR ' . $mysqli->error;
				exit();
			}
			else
			{
				echo 'Task Not Found';
				exit();
			}
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

	$file = query_fetch_one($mysqli, "SELECT task,name,description FROM files WHERE user = $_SESSION[user_id] AND id = $_GET[id]");
	if (!$file)
	{
		header('Location: /todo/new_file.php');
		exit();
	}
?>

<form action="/todo/edit_file.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="id" value="<?= $_GET['id'] ?>">
	<table>
		<tr><th colspan="2">Edit File</th></tr>
		<tr><td>Task*</td><td><input type="text" name="task" value="<?= $file['task'] ?>"></td></tr>
		<tr><td>Rename</td><td><input type="text" name="name" value="<?= $file['name'] ?>"></td></tr>
		<tr><td>Description</td><td><input type="text" name="description" value="<?= $file['description'] ?>"></td></tr>
	</table>
	<input type="submit" value="Edit">
</form><br>

<?php
	include('../../templates/todo/footer.php');
	$mysqli->close();
?>