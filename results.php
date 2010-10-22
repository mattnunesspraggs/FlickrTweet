<?php

	require_once("php/function.php");

	$user = null;
	$page_length = 20;
	
	if( isset($_COOKIE['sess_id']) ) {
		$sess_id = $_COOKIE['sess_id'];
		$user = check_session($sess_id);
		if( $user ) {
			$user->update_session();
		}
	}
	
	if( isset($_REQUEST['vote']) ) {
		vote($_REQUEST['vote']);
	}
	
	if( isset($_GET['page']) ) {
		$page = $_GET['page'];
		$start = $page * $page_length;
	}
	else {
		$page = 0;
		$start = 0;
	}

?><html>
<head>
	<title>flickrtweet - results</title>
	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/results.css" type="text/css" media="screen" title="no title" charset="utf-8">
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div id="page-container">
	
	<?php
	
		include 'header.php';
	
	?>
	
	<div id='content'>
		<a class='button' style='margin-right: 0px' href="index">&lt; &lt; Vote &gt; &gt;</a>
		<?php
			$tj = new TweetJob;

			$t = $tj->get_tweets();
			$count = 0;
			$tweets = array();
		
			foreach($t as $tw) {
				$tweet_ph = $tj->get_photos_tweet($tw->tweet_id);

				if( count($tweet_ph) > 0 ) {
					$total = 0;
					$photos = array();

					foreach($tweet_ph as $photo) {
						$total += $photo->tally;
						if( !isset($photos[$photo->tally]) ) $photos[$photo->tally] = array();
						$photos[$photo->tally][] = $photo;
					}

					if( !isset($tweets[$total]) ) $tweets[$total] = array();
					$tw->add_property('photos', $photos);
					$tweets[$total][] = $tw;
					$count += 1;
				}
			}
			
			$page_back = $page - 1;
			$page_for = $page + 1;
			
			$pagination = "";
			
			if( $count - ($page * $page_length) < 0 ) {
				echo "No results to display.  <a href='?page=0'>Go to results home</a>";
			}
			else {
				$max = ((1 + $page) * $page_length);
				$max = (($max <= $count) ? $max : $count);
				
				if( ($start + 1) != $max )
					$pagination .= "Showing results " . ($start + 1) . " through " . $max . " of " . $count . "<br>";
				else
					$pagination .= "Showing result $max";
					
				if($page_back >= 0) {
					$pagination .= "<a href='?page=$page_back'>&lt; &lt; " . ($page_back == 0 ? "home" : $page_back) . "</a>&nbsp;";
				}
				$pagination .= "<span class='current_page'>" . ($page == 0 ? "home" : $page) . "</span>&nbsp;";
			
				if( $count - ($page_for * $page_length) > 0 ) {
					$pagination .= "<a href='?page=$page_for'>" . $page_for . " &gt; &gt;</a>";
				}
			}
			
			echo $pagination;
		
			echo "<div id='table'>";
			
	krsort($tweets);
	
	echo "<table width='100%' style='width: 100%;'>
		<tr>
			<td class=\"thead\">winning images</td>
			<td class=\"thead\">tweet</td>
		</tr>";
		
		$total_tweets = 0;
		
	foreach($tweets as $total => $tw_array) {
		foreach($tw_array as $tw) {
			$total_tweets += 1;
			
			if($total_tweets > $start and $total_tweets < $start + $page_length) {
				$text = my_strip_slashes($tw->tweet_text);

				$text = preg_replace('/(http[s]{0,1}:\/\/[A-Za-z0-9.\/?=&%-_]+[^(^)^,^\.^ ])/i', "<a href=&quot;$1&quot; target=&quot;_blank&quot;>$1</a>", $text);
				$text = preg_replace("/(#[A-Za-z0-9-_]+)/", "<a href='http://twitter.com/search?q=$1'>$1</a>", $text);
				$text = preg_replace("/@([A-Za-z0-9-_]+)/", "<a href='http://twitter.com/$1'>@$1</a>", $text);
		
				echo "<tr><td rowspan='2' class='left c'>";
			
				krsort($tw->photos);
			
				$winning = array_shift($tw->photos);
			
				$winning_votes = $winning[0]->tally;
			
				if($winning_votes > 0) {
					foreach($winning as $img) {
						echo "<div class='photo'>";
						echo "<span class='num_votes'>" . $img->tally . "</span> / <span class='total_votes'>$total</span><br>";
						echo "<a href='" . $img->get_filename() . "'><img src='" . $img->get_url('s') . "' alt='" . my_strip_slashes($img->title) . "' title='" . my_strip_slashes($img->title) . "'></a></div>";
					}
				}
				else {
					echo "<span class='num_votes'>No winner</span>";
					array_unshift($tw->photos, $winning);
				}
			
				echo "</td><td class='c tweet_text'>" . $text . "</td></tr>";
		
				echo "<tr><td class='c'>";
			
				foreach( $tw->photos as $photos ) {
					foreach( $photos as $votes => $ph ) {
						echo "<div class='photo'>";
						if( $total == 0 ) echo "No votes<br>";
						else echo "<span class='num_votes'>" . $ph->tally . "</span> / <span class='total_votes'>$total</span><br>";
						echo "<a href='" . $ph->get_filename() . "'><img src='" . $ph->get_url('s') . "' alt='" . my_strip_slashes($ph->title) . "' title='" . my_strip_slashes($ph->title) . "'></a></div>";
					}
				}
			}
		}
	}
	echo "</table>";
	echo $pagination;
	?>
			</div>
		</div>
	</div>
	
	<?php
	
		include 'footer.php';
	
	?>
	
</div>
</body>
</html>
