<?php
	function statusToString($status)
	{
		switch ($status)
		{
			case 0: return "New";
			case 1: return "In Progress";
			case 2: return "Resolved";
			case 3: return "Closed";
			default: return "ERROR";
		}
	}

	function priorityToString($priority)
	{
		switch ($priority)
		{
			case 0: return "Low";
			case 1: return "Normal";
			case 2: return "High";
			default: return "ERROR";
		}
	}

	function datetimeToString($datetime)
	{
		if (empty($datetime))
		{
			// if the string is empty, strtotime returns 1970-01-01 00:00:00
			// so, check for empty string and return empty string
			return "";
		}
		else
		{
			return date('d M Y D, H:i', strtotime($datetime));
		}
	}

	function mysqlString($s)
	{
		return $s === '' ? "NULL" : "'$s'";
	}

	function mysqlDatetime($s)
	{
		return $s === '' ? "NULL" : date("'Y-m-d H:i:s'", strtotime($s));
	}

	function mysqlNumber($n)
	{
		return $n === '' ? "NULL" : $n;
	}

	function get_tasks_table_header($title)
	{
		$s = "<tr><th colspan=\"6\">$title</th></tr>";
		$s .= '<tr>';
		$s .= '<th>Title</th>';
		$s .= '<th>Status</th>';
		$s .= '<th>Priority</th>';
		$s .= '<th>Started On</th>';
		$s .= '<th>Due Date</th>';
		$s .= '<th>Progress</th>';
		$s .= "</tr>\n";
		return $s;
	}

	function get_task_row($mysqli, $task)
	{
		$status = statusToString($task['status']);
		$priority = priorityToString($task['priority']);
		$start = datetimeToString($task['start']);
		$due = datetimeToString($task['due']);
		$s = '<tr>';
		$s .= "<td><a href=\"/todo/task.php?id=$task[id]\">$task[title]</a></td>";
		$s .= "<td>$status</td>";
		$s .= "<td>$priority</td>";
		$s .= "<td>$start</td>";
		$s .= "<td>$due</td>";
		$s .= "<td>$task[progress]</td>";
		$s .= "</tr>\n";
		return $s;
	}

	function get_tasks_table_partition($mysqli, $title, $query)
	{
		$tasks = query_fetch_all($mysqli, $query);
		if (count($tasks))
		{
			$s = get_tasks_table_header($title);

			foreach ($tasks as $task)
				$s .= get_task_row($mysqli, $task);
			return $s;
		}
		else
			return '';
	}

	function get_tasks_table($mysqli, $parent)
	{
		$s = "<table>\n";
		$s .= get_tasks_table_partition($mysqli, 'Active Tasks', "SELECT * FROM tasks WHERE user = $_SESSION[user_id] AND parent = $parent AND status < 2 ORDER BY ISNULL(due) ASC");
		$s .= get_tasks_table_partition($mysqli, 'Resolved Tasks', "SELECT * FROM tasks WHERE user = $_SESSION[user_id] AND parent = $parent AND status = 2 ORDER BY ISNULL(due) ASC");
		$s .= get_tasks_table_partition($mysqli, 'Closed Tasks', "SELECT * FROM tasks WHERE user = $_SESSION[user_id] AND parent = $parent AND status = 3 ORDER BY ISNULL(due) ASC");
		$s .= '</table>';

		return $s;
	}

	function get_task_details_table($mysqli, $id)
	{
		$task = query_fetch_one($mysqli, "SELECT * FROM tasks WHERE user = $_SESSION[user_id] AND id = $id LIMIT 1");
		if ($task)
		{
			$parent_row = query_fetch_one($mysqli, "SELECT title FROM tasks WHERE id = $task[parent] LIMIT 1");
			if ($parent_row)
				$parent = $parent_row['title'];
			else
				$parent = '';

			$status = statusToString($task['status']);
			$priority = priorityToString($task['priority']);
			$start = datetimeToString($task['start']);
			$due = datetimeToString($task['due']);

			$s = '<table>';
			$s .= '<tr><th colspan="2">Task</th></tr>';
			$s .= "<tr><th>ID</th><td>$id</td></tr>";
			$s .= "<tr><th>Parent Task</th><td><a href=\"/todo/task.php?id=$task[parent]\">$parent</a></td></tr>";
			$s .= "<tr><th>Title</th><td>$task[title]</td></tr>";
			$s .= "<tr><th>Description</th><td>$task[description]</td></tr>";
			$s .= "<tr><th>Status</th><td>$status</td></tr>";
			$s .= "<tr><th>Priority</th><td>$priority</td></tr>";
			$s .= "<tr><th>Started On</th><td>$start</td></tr>";
			$s .= "<tr><th>Due Date</th><td>$due</td></tr>";
			$s .= "<tr><th>% Done</th><td>$task[progress]</td></tr>";
			$s .= '</table>';
			return $s;
		}
		else
			return 'Task Not Found';
	}

	function get_files_table($mysqli, $task)
	{
		$s = '<table>';
		$files = query_fetch_all($mysqli, "SELECT id,name,LENGTH(contents) AS size FROM files WHERE user = $_SESSION[user_id] AND task = $task ORDER BY name");
		if ($files)
		{
			$s .= '<tr><th colspan="3">Files</th></tr>';
			$s .= '<tr><th>Name</th><th>Size</th><th></th></tr>';

			foreach ($files as $file)
			{
				$s .= "<tr>";
				$s .= "<td><a href=\"/todo/file.php?id=$file[id]\">$file[name]</a></td>";
				$s .= "<td>$file[size]</td>";
				$s .= "<td><a href=\"/todo/download.php?id=$file[id]\">Download</a></td>";
				$s .= "</tr>";
			}

		}

		$s .= '</table>';
		return $s;
	}

	function get_file_details_table($mysqli, $file)
	{
		$file = query_fetch_one($mysqli, "SELECT id,task,name,description,LENGTH(contents) AS size FROM files WHERE user = $_SESSION[user_id] AND id = $file LIMIT 1");
		if ($file)
		{
			$task_row = query_fetch_one($mysqli, "SELECT title FROM tasks WHERE user = $_SESSION[user_id] AND id = $file[task] LIMIT 1");
			$task_name = $task_row ? $task_row['title'] : '';

			$s = '<table>';
			$s .= '<tr><th colspan="2">File</th></tr>';
			$s .= "<tr><th>ID</th><td>$file[id]</td></tr>";
			$s .= "<tr><th>Task</th><td><a href=\"/todo/task.php?id=$file[task]\">$task_name</a></td></tr>";
			$s .= "<tr><th>Name</th><td>$file[name]</td></tr>";
			$s .= "<tr><th>Description</th><td>$file[description]</td></tr>";
			$s .= "<tr><th>Size</th><td>$file[size]</td></tr>";
			$s .= '</table>';
			return $s;
		}
		else
			return 'File Not Found';
	}

	function delete_tasks_recursively($mysqli, $parent)
	{
		query_fetch_one($mysqli, "DELETE FROM tasks WHERE id = $parent AND user = $_SESSION[user_id] LIMIT 1");
		query_fetch_one($mysqli, "DELETE FROM files WHERE task = $parent AND user = $_SESSION[user_id]");

		$child_tasks = query_fetch_all($mysqli, "SELECT id FROM tasks WHERE parent = $parent AND user = $_SESSION[user_id]");
		foreach ($child_tasks as $child)
			delete_tasks_recursively($mysqli, $child['id']);
	}

?>