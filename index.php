<?php
	#error_reporting(0);
	require_once("php/function.php");

	$user = null;
	
	if( isset($_COOKIE['sess_id']) ) {
		$sess_id = $_COOKIE['sess_id'];
		$user = check_session($sess_id);
		if( $user ) {
			$user->update_session();
		}
	}
	
	if( isset($_POST['vote']) ) {
		vote($_REQUEST['vote']);
	}

?><html>
<head>
	<title>flickrtweet</title>
	<link rel="stylesheet" href="css/master.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<link rel="stylesheet" href="css/index.css" type="text/css" media="screen" title="no title" charset="utf-8">
	<script src="jquery.js" type="text/javascript" charset="utf-8"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body>
<div id="page-container">
	
	<?php
	
		include 'header.php';
	
	?>
	<div id='content'>	
	<div id='button_bar'><a class='button' href='results'>&lt; &lt; results &gt; &gt;</a><a class='button' href=''>&gt; &gt; skip &gt; &gt;</a></div>
	<?php	
	$tj = new TweetJob;
	
	$tweet = $tj->get_random_tweet();
	
	$text = my_strip_slashes($tweet->tweet_text);
	
	$text = preg_replace('/(http[s]{0,1}:\/\/[A-Za-z0-9.\/?=&%-_]+[^(^)^,^\.^ ])/i', "<a href=&quot;$1&quot; target=&quot;_blank&quot;>$1</a>", $text);
	$text = preg_replace("/(#[A-Za-z0-9-_]+)/", "<a href='http://twitter.com/search?q=$1'>$1</a>", $text);
	$text = preg_replace("/@([A-Za-z0-9-_]+)/", "<a href='http://twitter.com/$1'>@$1</a>", $text);
	
	echo "<div style='position: relative; top: 25px;' class='img_label'>read tweet</div><div class='tweet'>" . $text . "</div>\n\n";
	
	echo "<div class='img_label'>vote on an image</div>";
	echo "<div id='images'>\n";
	
	$output = array();
	
	foreach($tweet->photos as $photo) {
		$output[] = "<div class='image_m' id='x$photo->int_id'><img src='" . $photo->get_filename() . "'></div>";
		echo "<div class='tnail'><img class='thumbnail'";
		if(count($output) == 1) echo " name='first_image'";
		echo " id='$photo->int_id' src='". $photo->get_url('s') . "'></div>\n";
	}
	
	echo "<div id='vote_container'>\n";
	
	echo "<div id='vote_img'>\n\t<form action='' method='POST'><input type='hidden' name='vote' id='photo_id'><input type='submit' value='Vote!'></form>";
	echo join("\n", $output);
	echo "</div></div>";
	
	echo "</div>";
	
	?>
	
	<script type="text/Javascript">
	$(document).ready( function() {
		var input = document.getElementById("photo_id");
		var current_photo = "";
		var current_thumb = "";
	
		$('.thumbnail').mouseover( function() {
			var id = this.id;
	
			if( current_photo == "" ) {
				current_photo = document.getElementById("x" + id);
				current_thumb = document.getElementById(id)
			
				$(current_thumb).addClass('current_thumbnail');
				$(current_photo).fadeIn('fast');
			
				input.value = current_thumb.id;
			}
			else if(current_thumb.id !== id) {
				current_photo.style.display = "none";
			
				$(current_thumb).removeClass('current_thumbnail');
			
				current_thumb = document.getElementById(id);
				current_photo = document.getElementById("x" + id);
			
				$(current_thumb).addClass('current_thumbnail');
				current_photo.style.display = "inline-block";
			
				input.value = current_thumb.id;
			}
		});
		
		$('img[name="first_image"]').trigger('mouseover');
	});
	
	</script>
	
	</div>
	
	<?php
	
		include 'footer.php';
	
	?>
	</div>
</div>
</body>
</html>
			