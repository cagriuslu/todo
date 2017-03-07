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

		// check if all required files are filled
		if (isset($_POST['task'], $_POST['name'], $_POST['description'], $_FILES['contents']) && $_POST['task'] != '')
		{
			// check if task exits
			if (query_fetch_one($mysqli, "SELECT NULL FROM tasks WHERE id = $_POST[task] AND user = $_SESSION[user_id]"))
			{
				$_FILES['contents'] = array_map('trim', $_FILES['contents']);
				if($_FILES['contents']['size'] > 0)
				{
					$file_name = $_POST['name'] == '' ? $_FILES['contents']['name'] : $_POST['name'];
					$description = mysqlString($_POST['description']);
					$tmp_name  = $_FILES['contents']['tmp_name'];

					$fp = fopen($tmp_name, 'r');
					$content = fread($fp, filesize($tmp_name));
					$content = addslashes($content);
					fclose($fp);

					if(!get_magic_quotes_gpc())
						$file_name = addslashes($file_name);

					if ($mysqli->query("INSERT INTO files (user,task,name,description,contents) VALUES ($_SESSION[user_id],$_POST[task],'$file_name',$description,'$content')"))
						header("Location: /todo/task.php?id=$_POST[task]");
					else
						echo 'ERROR ' . $mysqli->error;
					exit();
				}
				else
				{
					echo 'File Upload Error';
					exit();
				}
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

	$parent = isset($_GET['parent']) ? $_GET['parent'] : '';
?>

<form action="/todo/new_file.php" method="post" enctype="multipart/form-data">
	<table>
		<tr><th colspan="2">New File</th></tr>
		<tr><td>Task*</td><td><input type="text" name="task" value="<?= $parent ?>"></td></tr>
		<tr><td>Rename</td><td><input type="text" name="name"></td></tr>
		<tr><td>Description</td><td><input type="text" name="description"></td></tr>
		<tr><td>File*</td><td><input type="file" name="contents"></td></tr>
	</table>
	<input type="submit" value="Upload">
</form><br>

<?php
	include('../../templates/todo/footer.php');
	$mysqli->close();
?>