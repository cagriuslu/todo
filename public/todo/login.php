<?php
	include_once '../../includes/todo/db-connect.php';
	include_once '../../includes/todo/sec-connect.php';
	include_once '../../includes/todo/functions.php';
	include_once '../../includes/todo/todo-functions.php';

	start_secure_session();

	if (check_login($secure_mysqli) === true)
		header('Location: /todo/');

	if ($_SERVER['REQUEST_METHOD'] === 'GET')
	{
		// else do nothing
	}
	else if ($_SERVER['REQUEST_METHOD'] === 'POST')
	{
		if (isset($_POST['email'], $_POST['p']))
		{
			if (do_login($_POST['email'], $_POST['p'], $secure_mysqli) === true)
			{
				// login success, redirect to home page
				header('Location: /todo/');
			}
			else
			{
				$message = 'Invalid Password';
			}
		}
		else
		{
			// since the password is hashed before being sent, $_POST[p] is always set
			// but do the check anyways

			// since e-mail is set implicitly, only password must be problematic
			$message = 'Please enter password.';
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

<?php include('../../templates/todo/header.php') ?>

<?= isset($message) ? "$message<br>" : '' ?>
<form action="/todo/login.php" method="post" name="login_form">
	<input type="hidden" name="email" value="me@cagriuslu.com" />
	<input type="password" name="password" id="password" placeholder="Password" /><br>
	<input type="submit" value="Login" onclick="formhash(this.form, this.form.password);" />
</form>

<?php include('../../templates/todo/footer.php') ?>

<?php $mysqli->close() ?>