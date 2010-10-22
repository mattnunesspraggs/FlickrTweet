<?php

	require_once("php/function.php");

	$user = null;
	$output = array();

	if( isset($_COOKIE['sess_id']) ) {
		$sess_id = $_COOKIE['sess_id'];
		$user = check_session($sess_id);
		
		if(! $user ) {
			#$user = null;
			header("Location: home?msg=0");
		}
		elseif( $user->permissions < 5 ) {
			$user = null;
			header("Location: home?msg=1");
		}
		else {
			$user->update_session();
		}
	}
	else {
		header("Location: home");
	}

?><html>
<head>
	<title>flicktweet - current tweets</title>
	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/errors.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
	
	<div id="page-container">

		<?php

			include 'header.php';

		?>
		
		<div id="content">
	<form action="" method="POST">
		<div id="resolve_error">
			
			<?php
				require_once("php/function.php");
			
				if( isset($_REQUEST['clear_errors']) ) {
					clear_errors();
				}
				
				if( isset($_POST['error_msg']) ) {
					$new_msg = mysql_real_escape_string($_POST['error_msg']);
					$error_id = mysql_real_escape_string($_POST['error_id']);
					
					if( update_error($error_id, array("resolved" => $new_msg)) ) {
						echo "Error id #$error_id successfully updated!<br>";
					}
					else {
						echo "Could not update error.  Check error log for details.<br>";
					}
				}
				
				if( isset($_REQUEST['delete']) ) {
					$err_id = $_REQUEST['delete'];
					if(delete_error($err_id)) {
						echo "Error id $err_id deleted successfully!<br>";
					}
					else {
						echo "Error id $err_id could not be deleted.<br>";
					}
				}
			
			?>
			
		</div>
	</form>
	<a href="?clear_errors">clear error db</a> <a style="float: right;" href="?">refresh</a> 
	<table style="width: 100%">
	<?php
	
	$sql = "SELECT * FROM `flicktweet_errors`";
	
	$result = mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		echo mysql_error();
	}
	else {
		echo "<td class='thead'>Process</td><td class='thead'>Tweet ID</td><td class='thead'>Photo ID</td><td class='thead'>Message</td><td class='thead'>Resolved?</td><td class='thead'>Tools</a>";
		while( $row = mysql_fetch_assoc($result) ) {
			echo "<tr>";
			$error = new Error($row);
			
			echo "<td>" . $error->process . "</td>";
			echo "<td>" . $error->tweet_id . "</td>";
			echo "<td>" . $error->photo_id . "</td>";
			echo "<td>" . $error->description . "</td>";
			echo "<td><a href='javascript:resolveError($error->error_id);'>" . $error->resolved . "</a>";
			echo "<td><a href=\"?delete=$error->error_id\">delete</a></td>";
			
			echo "</tr>";
		}
	}
	
	?>
	</table>
	
	<?php
	
		include 'footer.php';
	
	?>
	</div>
	
	<script type="text/javascript">
		var err = document.getElementById("resolve_error");
		
		function resolveError(id) {
			err.innerHTML = "New <i>Resolved</i> Message: <input type='text' name='error_msg' style='width: 500px;'> <input type='submit' value='Set Message'><input type='hidden' name='error_id' value='" + id + "'>";
		}
	</script>
</body>
</html>