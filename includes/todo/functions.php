<?php
	include_once('psl-config.php');

	function start_secure_session()
	{
		// Set a custom session name
		// This must come before session_set_cookie_params due to an undocumented bug/feature in PHP.
		session_name('secure_session_id');

		$secure = SECURE;
		// This stops JavaScript being able to access the session id
		$httponly = true;
		// Forces sessions to only use cookies
		if (ini_set('session.use_only_cookies', 1) === FALSE)
		{
			echo "Could not initiate a safe session (ini_set)";
			exit();
		}

		// Gets current cookies params
		$cookieParams = session_get_cookie_params();
		session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);

		// Start the PHP session
		session_start();
		// regenerated the session, delete the old one
		session_regenerate_id(true);
	}

	function do_login($email, $password, $mysqli)
	{
		// Using prepared statements means that SQL injection is not possible.
		if ($query = $mysqli->prepare("SELECT id,password FROM users WHERE email = ? LIMIT 1"))
		{
			$query->bind_param('s', $email);  // Bind "$email" to parameter.
			$query->execute();    // Execute the prepared query.
			$query->store_result();

			// get variables from result.
			$query->bind_result($db_id, $db_password);
			$query->fetch();

			if ($query->num_rows == 1)
			{
				// If the user exists we check if the account is locked
				// from too many login attempts
				if (checkbrute($db_id, $mysqli) == true)
				{
					// Account is locked
					// Send an email to user saying their account is locked
					return false;
				}
				else
				{
					// Check if the password in the database matches
					// the password the user submitted. We are using
					// the password_verify function to avoid timing attacks.
					if (password_verify($password, $db_password))
					{
						// Password is correct!
						// Get the user-agent string of the user.
						$user_browser = $_SERVER['HTTP_USER_AGENT'];
						// XSS protection as we might print this value
						$_SESSION['user_id'] = preg_replace("/[^0-9]+/", "", $db_id);
						// XSS protection as we might print this value
						$_SESSION['email'] = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $email);
						$_SESSION['login_string'] = hash('sha512', $db_password . $user_browser);
						// Login successful.
						return true;
					}
					else
					{
						// Password is not correct
						// We record this attempt in the database
						$now = time();
						$mysqli->query("INSERT INTO login_attempts(user_id, time) VALUES ('$db_id', '$now')");
						return false;
					}
				}
			}
			// No user exists.
			else
				return false;
		}
		return false;
	}

	function checkbrute($user_id, $mysqli)
	{
		// Get timestamp of current time
		$now = time();
		// All login attempts are counted from the past 2 hours.
		$valid_attempts = $now - (2 * 60 * 60);

		if ($query = $mysqli->prepare("SELECT time FROM login_attempts WHERE user_id = ? AND time > '$valid_attempts'"))
		{
			$query->bind_param('i', $user_id);
			$query->execute();
			$query->store_result();

			// If there have been more than 5 failed logins
			if ($query->num_rows > 5)
				return true;
			else
				return false;
		}
	}

	function check_login($mysqli)
	{
		// Check if all session variables are set
		if (isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['login_string']))
		{
			$user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			$email = $_SESSION['email'];

			// Get the user-agent string of the user.
			$user_browser = $_SERVER['HTTP_USER_AGENT'];

			if ($query = $mysqli->prepare("SELECT password FROM users WHERE id = ? LIMIT 1"))
			{
				$query->bind_param('i', $user_id);
				$query->execute();   // Execute the prepared query.
				$query->store_result();

				if ($query->num_rows == 1)
				{
					// If the user exists get variables from result.
					$query->bind_result($password);
					$query->fetch();
					$login_check = hash('sha512', $password . $user_browser);

					if (hash_equals($login_check, $login_string))
					{
						// Logged In!!!!
						return true;
					}
				}
			}
		}

		return false;
	}

	function esc_url($url)
	{
		if ('' == $url)
		{
			return $url;
		}

		$url = preg_replace('|[^a-z0-9-~+_.?#=!&;,/:%@$\|*\'()\\x80-\\xff]|i', '', $url);

		$strip = array('%0d', '%0a', '%0D', '%0A');
		$url = (string) $url;

		$count = 1;
		while ($count)
		{
			$url = str_replace($strip, '', $url, $count);
		}

		$url = str_replace(';//', '://', $url);

		$url = htmlentities($url);

		$url = str_replace('&amp;', '&#038;', $url);
		$url = str_replace("'", '&#039;', $url);

		if ($url[0] !== '/')
		{
			// We're only interested in relative links from $_SERVER['PHP_SELF']
			return '';
		}
		else
		{
			return $url;
		}
	}

	function query_fetch_all($mysqli, $query)
	{
		$result = $mysqli->query($query);
		if ($result === true)
		{
			return true;
		}
		else if ($result)
		{
			$rows = $result->fetch_all(MYSQLI_ASSOC);
			$result->free();
			return $rows;
		}
		else
		{
			return false;
		}
	}

	function query_fetch_one($mysqli, $query)
	{
		$result = $mysqli->query($query);
		if ($result === true)
		{
			return true;
		}
		else if ($result)
		{
			$row = $result->fetch_assoc();
			$result->free();
			return $row;
		}
		else
			return false;
	}

?>