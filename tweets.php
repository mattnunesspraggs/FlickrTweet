<?php

	require_once("php/function.php");

	$user = null;
	$output = array();

	if( isset($_COOKIE['sess_id']) ) {
		$sess_id = $_COOKIE['sess_id'];
		$user = check_session($sess_id);
		if(!$user || $user->permissions < 5) {
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
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="css/tweets.css" type="text/css" media="screen" title="no title" charset="utf-8">
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
			
			$tj = new TweetJob();
			
			echo "<pre>";
			
			if( isset($_REQUEST['delete']) ) {
				$id = mysql_real_escape_string($_REQUEST['delete']);
				$tj->remove_tweet($id);
				echo "<br>";
			}
			
			if( isset($_REQUEST['fetch']) ) {
				$id = mysql_real_escape_string($_REQUEST['tweet']);
				
				$tweet = $tj->get_tweets(array('tweet_id' => $id));
				
				foreach($tweet as $tw) {
					$tj->flickrScour_tweet($tw);
				}
			}
			
			if( isset($_REQUEST['delete-photo']) ) {
				$id = mysql_real_escape_string($_REQUEST['delete-photo']);
				$tj->remove_photo($id);
				echo "<br>";
			}
			
			if( isset($_REQUEST['delete-photo-tweet']) ) {
				$id = mysql_real_escape_string($_REQUEST['delete-photo-tweet']);
				$tj->remove_photo_tweet($id);
				echo "<br>";
			}
			
			if( isset($_REQUEST['clear_tweet']) ) {
				clear_tweets();
				echo "<br>";
			}
			
			if( isset($_REQUEST['download_images']) ) {
				$tj->download_images();	
				$tj->clean_downloads();
				echo "<br>";
			}
			
			if( isset($_REQUEST['clean_downloads']) ) {
				$tj->clean_downloads();
				echo "<br>";
			}
			
			if( isset($_REQUEST['clear_photo']) ) {
				clear_photos();
				echo "<br>";
			}
			
			if( isset($_REQUEST['run_update']) ) {
				$tj->run();
			}
			
			if( isset($_REQUEST['get_tweets']) ) {
				$tj->collect_tweets();
			}
			
			echo "</pre>";
			$tweets = $tj->get_tweets();
			$num_tweets = count($tweets);
			
			if( $num_tweets == 0 ) {
				echo "No posts to show! <a href='?get_tweets'>get tweets</a>";
			}
			else {				
				echo "Tweets in database: " . $num_tweets . " ";
				echo "&nbsp;&nbsp;|&nbsp;&nbsp;<a href='?clear_tweet'>clear tweet db</a> | <a href='?clear_photo'>clear photo db</a> | <a href='?clear_tweet&clear_photo'>clear both</a> | <a href='?run_update'>run update</a> | <a href='?get_tweets'>get only tweets</a> | <a href='?download_images'>download images</a> | <a href='?clean_downloads'>tidy</a> <a style='float: right' href='?'>refresh</a><br>";
				echo "<table>
					<tr>
						<td class=\"thead\">user</td>
						<td class=\"thead\">tweet text</td>
						<!-- <td class=\"thead\">score</td> -->
						<td class=\"thead\">tools</td>
					</tr>";
					
				foreach($tweets as $tw) {
					$text = my_strip_slashes($tw->tweet_text);

					$text = preg_replace('/(http[s]{0,1}:\/\/[A-Za-z0-9.\/?=&%-_]+[^(^)^,^\.^ ])/i', "<a href=&quot;$1&quot; target=&quot;_blank&quot;>$1</a>", $text);
					$text = preg_replace("/(#[A-Za-z0-9-_]+)/", "<a href='http://twitter.com/search?q=$1'>$1</a>", $text);
					$text = preg_replace("/@([A-Za-z0-9-_]+)/", "<a href='http://twitter.com/$1'>@$1</a>", $text);
					
					echo "<tr><td class='left'><img style='float: right; height: 75px; width: 75px;' alt='profile image' src='". $tw->profile_photo . "'><a href='http://www.twitter.com/" . $tw->tweet_screename . "' target='_blank'>" . $tw->tweet_screename . "</a><br>(" . $tw->tweet_user_name;
					echo $tw->location != "" ? " &mdash; " . $tw->location : "";
					echo ")</td><td>" . $text . "</td><td><a href='?delete=$tw->tweet_id'>delete everything</a></tr>";
					
					echo "<tr><td>&nbsp;</td><td class='c'>";
					
					$tweet_photos = $tj->get_photos_tweet($tw->tweet_id);
					$photo_ct = count($tweet_photos);
					
					if($photo_ct == 0) {
						echo "No photos have been fetched yet for this tweet - <a href='?fetch&tweet=$tw->tweet_id'>fetch them now!</a></td><td>";
					}
					else {
						$total = 0;
						
						foreach($tweet_photos as $ph) {
							$total += $ph->tally;
						}
						
						foreach( $tweet_photos as $ph ) {
							echo "<div class='photo'>";
							if( $total == 0 ) echo "No votes<br>";
							else echo (int)(100 * ($ph->tally / $total)) . "%<br>";
							echo "<a href='" . $ph->get_filename() . "'><img src='" . $ph->get_url('s') . "' alt='" . my_strip_slashes($ph->title) . "' title='" . my_strip_slashes($ph->title) . "'></a><br><a href='?delete-photo=$ph->int_id'>delete</a></div>";
						}
						echo "</td><td><a href='?delete-photo-tweet=$tw->tweet_id'>delete photos</a>";
					}
				}
				echo "</table>";
			}
		
			echo "<div name='output'>";
		
			flush();
			ob_flush();
		?>
		</div>
	</div>
	<?php
	
		include 'footer.php';
	
	?>
	</div>
</body>
</html>