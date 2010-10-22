<?php

	require_once("php/function.php");

	$user = null;
	$output = array();

	if( isset($_COOKIE['sess_id']) ) {
		$sess_id = $_COOKIE['sess_id'];
		$user = check_session($sess_id);
		if(!$user) {
			$user = null;
			header("Location: home?msg=0");
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
	<title>flickrtweet - current tweets</title>
	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/users.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div id="page-container">
	
	<?php
	
		include 'header.php';
	
	?>
	
		<div id="content">
		<br>
		<?php
		
			require_once("php/function.php");
			
			if( $user->permissions >= 10 ) {
				
				$users = get_user();
				
				echo "<table style='width: 100%'><tr><td class='thead'>username</td><td class='thead'>first name</td><td class='thead'>last name</td><td class='thead'>nickname</td><td class='thead'>sess_id</id><td class='thead'>valid</id></tr>\n";
				
				foreach($users as $us) {
					echo "<tr><td>$us->username</td><td>$us->first_name</td><td>$us->last_name</td><td>$us->nickname</td><td>$us->sess_id</td><td>$us->sess_exp: ";
					if($us->sess_id != "" && $us->sess_exp == "0") {
						echo "yes";
					}
					elseif($us->sess_id != ""){
						echo $us->sess_exp < time() ? "yes" : "no";
					}
					else {
						echo "no";
					}
					echo "</td></tr>\n";
				}
				
				echo "</table></div><div style='text-align: center'><div style='margin: 0 auto; display: inline-block;'>";
				//print_user_form($user);
				echo "</div></div>";
			}
			elseif( $user->permissions <= 5 ) {
				
			}
			
		
		?>
		</div>
	</div>
	<?php
	
		include 'footer.php';
	
	?>
	</div>
</body>
</html>