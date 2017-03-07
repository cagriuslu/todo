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

	echo get_file_details_table($mysqli, $_GET['id']);
	echo '<br>';
	echo "<a href=\"/todo/edit_file.php?id=$_GET[id]\">Edit </a>";
	echo "<a href=\"/todo/delete_file.php?id=$_GET[id]\">Delete </a>";

	include('../../templates/todo/footer.php');
	$mysqli->close();
?>