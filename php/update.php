<pre>
<?php
	require_once "function.php";
	
	clear_tweets();
	clear_photos();
	clear_errors();
	
	flush(); ob_flush();
	
	$job = new TweetJob();
	$job->run();
	
	print_r(libxml_get_errors());
?>
</pre>