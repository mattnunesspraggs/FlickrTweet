<?php
	require_once("php/function.php");
	
	$title = "login";
	$user = null;
	$output = array();
	
	if( isset($_GET['logout']) && isset($_COOKIE['sess_id']) ) {
		setcookie("sess_id", "");
		$title = "logout";
		$output[] = "User logged out.";
	}
	else {
		if( isset($_COOKIE['sess_id']) ) {
			$sess_id = $_COOKIE['sess_id'];
			$user = check_session($sess_id);
			
			if( !$user ) {
				$title = "login";
				$user = null;
			}
			else {
				$title = "home";
				$user->update_session();
			}
		}
	
		if( isset($_REQUEST["doLogin"]) ) {
			$user = $_POST['username'];
			$pass = $_POST['password'];
			$int = !isset( $_POST['no_int'] );
		
			$user = check_user_credentials($user, $pass);
		
			if( $user ) {
				$sess_id = $user->set_session($int);
				if( $sess_id ) {
					$title = "home";
					setcookie("sess_id", $sess_id);
					$user = check_session($sess_id);
				}
				else {
					$title = "login";
					$user = null;
					$output[] = "Session ID could not be set.  Login aborted.";
				}
			}
			else {
				$title = "login";
				$user = null;
				$output[] = "Incorrect username or password supplied.";
			}
		}
		
		if( isset($_REQUEST['msg']) ) {
			$msg = $_REQUEST['msg'];
			
			switch($msg) {
				case 0:
					$output[] = "Your session has timed out (NoActivity > " . INTERVAL . "s).";
					break;
				case 1:
					$output[] = "You do not have adequate permissions to view this page.";
					break;
				default:
					$output[] = "Unknown error occured.";
					break;
			}
		}
	}
	
?><html>
<head>
	<title>flickrtweet - <?php echo $title ?></title>
	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/login.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div id="page-container">
	
	<?php
	
		include 'header.php';
	
	?>
		<div style="height: 1em; clear: both;"></div>
		<div id="content">
			
		<?php if(!$user): ?>
		<form action="home" method="POST">
		<div id="login">
			<?php 
			
			if( count($output) > 0 ) {
				echo "<div class='msg'>"  . join($output, "<br>") . "</div>"; 
			}
			
			?>
			<span class="label">username</span><br>
			<input type="text" name="username" class="fw">
			<span class="label">password</span><br>
			<input type="password" name="password" class="fw">
			<input type="hidden" name="doLogin" value="LOGIN!">
			<input type="submit" value="Login" class="c"><label style="float: right;" for="no_int">Keep me logged in</label><input style="float: right;" type="checkbox" name="no_int" id="no_int">
		</div>
		<div style="height:150px"><!-- spacer --></div>
		</form>
		<?php else:
			
			if( count($output) > 0 ) {
				echo "<div class='msg'>"  . join($output, "<br>") . "</div>"; 
			}
			
			$pages = array(
				0 => array(
					array("title"=>"Voting Page", "url" => "index"),
					),
				5 => array(
					array("title"=>"Error Management", "url" => "errors"),
					array("title"=>"Tweet and Photo Management", "url" => "tweets"),
					),
				10 => array(
					array("title"=>"User Management", "url" => "users"),
					),
				);
			
			for($i = 0; $i <= $user->permissions; $i++) {
				if( empty( $pages[$i] ) ) continue;
				
				echo "\n\nLevel $i Permission Pages:<ul>\n";
				
				foreach($pages[$i] as $link) {
					echo "\t<li><a href='" . $link['url'] . "'>" . $link['title'] . "</a></li>\n";
				}
				echo "</ul>";
			}
		 endif; ?>
	</div>
	<?php
	
		include 'footer.php';
	
	?>
</body>
</html>