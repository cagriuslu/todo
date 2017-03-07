<?php
	include_once '../../includes/todo/db-connect.php';
	include_once '../../includes/todo/sec-connect.php';
	include_once '../../includes/todo/functions.php';
	include_once '../../includes/todo/todo-functions.php';

	start_secure_session();

	if (check_login($secure_mysqli) === false)
		header('Location: /todo/login.php');

	if ($_SERVER['REQUEST_METHOD'] !== 'GET')
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

	echo get_tasks_table($mysqli, 0);
	echo '<br>';

	include('../../templates/todo/footer.php');

	$mysqli->close();
?>