<?php

define("INTERVAL", 600);

function db_connect() {
	$db = "flickrtweet";
	$host = "localhost";
	$username = "flickrtweet";
	$password = "VWJmEEfEd5f8buyD";
	
	if( !mysql_connect($host, $username, $password) ) {
		echo "Could not connect to database!  Check your connection details and MySql installation.";
		exit;
	};
	
	if( !mysql_select_db($db) ) {
		echo "Could not select database $db.  Check permissions and database existence.";
		exit;
	}
}

function read_directory($dir) {
	$dirname = $dir;
	$dir = array();
	
	$dir_handle = opendir($dirname);
	while($file = readdir($dir_handle)) {
		if ($file != "." && $file != "..") {
			if (is_dir($dirname."/".$file))
				$dir[$file] = read_directory($dirname . "/" . $file);
			else
			     $dir[$file] = $file;
		}
	}
	
	return $dir;
}

function my_strip_slashes($string) {
	return preg_replace("/\\\\/", "", $string);
}

function print_user_form($user, $admin = false) {
	echo "<form id='user_form' action='' method='post'><table id='user_form' border='0'>";
	echo "<tr><td class='label'>first name</td></tr><tr><td><textarea name='first_name' width='50' height='1' style='width: 25em'>$user->first_name</textarea></tr></td>";
	echo "<tr><td class='label'>last name</td></tr><tr><td><textarea name='last_name' width='50' height='2' style='width: 25em'>$user->last_name</textarea></tr></td>";
	echo "<tr><td class='label'>nickname</td></tr><tr><td><textarea name='nickname' width='50' height='2' style='width: 25em'>$user->nickname</textarea></tr></td>";
	
	if($admin) {
		echo "<tr><td class='label'>username</td></tr><tr><td><textarea name='username' width='50' height='2' style='width: 25em'>$user->username</textarea></tr></td>";
	}
	else {
		echo "<tr><td class='label'>username (unchangeable)</td></tr><tr><td><div style='font-size: 0.8em; padding: 4px; height: 1em; border: 1px solid #555'>$user->username</div></td></tr>";
	}
	
	echo "<tr><td>&nbsp;</td></tr>";
	
	echo "<tr><td class='label'>new password (if applicable)";
	echo "<a href='javascript:createPassword();' style='float: right;'>generate</a>";
	echo "</td></tr><tr><td><input type='password' name='pass1' style='width: 25em'></tr></td>";
	echo "<tr><td class='label'>repeat new password</td></tr><tr><td><input type='password' name='pass2' style='width: 25em'></tr></td>";
	echo "<tr><td><a href='javascript:verifyAndSubmit();'>go!</a></td></tr>";
	echo "</table></form>";
	
	echo <<<SCRIPT
	<script type='text/javascript'>
	
	function createPassword() {
		var pass = ""; var len = 8;
		
		for(var i = 1; i <= len; i++) {
			var char = Math.floor(Math.random() * 26) + 65;
			pass += String.fromCharCode(char);
		}
		
		alert("Your password is:\\n\\n" + pass + "\\n\\nCopy and paste this, and do not lose it!");
	}
	
	function verifyAndSubmit() {
		var errors = Array();
		var user_form = document.forms.user_form;
		var pass1 = user_form.pass1.value;
		var pass2 = user_form.pass2.value;
		
		if( pass1.length !== 0 && pass1.length < 6 ) {
			errors.push("The password must be more than 6 characters long!");
		}
		
		if ( pass1 !== pass2 ) {
			errors.push("The password values must be the same!");
		}
		
		if( errors.length > 0 ) {
			alert("The following errors have occured:\\n\\n- " + errors.join("\\n- ") + "\\n\\nYou must fix these errors before the form may be submitted.");
		} else {
			alert('user_form.submit()');
		}
	}
	</script>
SCRIPT;

}

function delete_directory($dirname) {
   if (is_dir($dirname))
      $dir_handle = opendir($dirname);
   if (!$dir_handle)
      return false;
   while($file = readdir($dir_handle)) {
      if ($file != "." && $file != "..") {
         if (!is_dir($dirname."/".$file))
            unlink($dirname."/".$file);
         else
            delete_directory($dirname.'/'.$file);     
      }
   }
   closedir($dir_handle);
   rmdir($dirname);
   return true;
}

function clear_tweets() {
	$db = db_connect();
	
	$sql = "TRUNCATE TABLE `flicktweet_tweets`;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		echo "\tMySQL ERROR: ", mysql_error(), "\n";
		my_error_log("clear_tweets()", "", "", mysql_error()) or exit;
		return false;
	}
	else {
		echo "Tweet database cleared.\n";
	}
	
	
}

function clear_photos() {
	$db = db_connect();
	
	$sql = "TRUNCATE TABLE `flicktweet_photos`;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		echo "\tMySQL ERROR: ", mysql_error(), "\n";
		my_error_log("clear_photos()", "", "", mysql_error()) or exit;
		return false;
	}
	else {
		$dirname = "photos/";
		$dir_handle = opendir($dirname);
		while($file = readdir($dir_handle)) {
			if ($file != "." && $file != "..") {
				if (is_dir($dirname."/".$file))
					delete_directory($dirname.'/'.$file);     
			}
		}
		echo "Photo database cleared.\n";
	}
	
	
}

function clear_errors() {
	$db = db_connect();
	
	$sql = "TRUNCATE TABLE `flicktweet_errors`;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		echo "\tMySQL ERROR: ", mysql_error(), "\n";
		my_error_log("clear_errors()", "", "", mysql_error()) or exit;
		return false;
	}
	else {
		echo "Error database cleared.\n";
	}
}

function get_user($query = "ALL", $method = "AND") {
	if( !is_array($query) ) {
		$sql = "SELECT * FROM `flicktweet_users` ORDER BY `username` DESC;";
		$params = "ALL";
	}
	else {
		$params = array();
		
		foreach( $query as $k => $v ) {
			$k = mysql_real_escape_string($k);
			$v = mysql_real_escape_string($v);
			
			$params[] = "`$k` = '$v'";
		}
		
		$params = join(" $method ", $params);
		
		$sql = "SELECT * FROM `flicktweet_users` WHERE $params ORDER BY `username` DESC;";
	}
	
	$results = mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		my_error_log("get_users($params)", "", "", mysql_error());
		return false;
	}
	else {
		$users = array();
		
		while($row = mysql_fetch_assoc($results)) {
			$users[] = new User($row);
		}
		
		return $users;
	}
}

// source: http://devzone.zend.com/article/1081, modified / functionized
// returns a ruby-like httpresponse "object" (associative array)

function http_req($type, $url, $data = array(), $headers = array(), $verbose = false) {
	$head = array();
	$headers['Content-type'] = "application/x-www-form-urlencoded";
	
	foreach($headers as $k=>$v) {
		$head[] = "${k}: ${v}";
	}
	
	$reqbody = http_build_query($data);
	
	$ch = curl_init();    // initialize curl handle 
	curl_setopt($ch, CURLOPT_URL, $url); // set url to post to 
	curl_setopt($ch, CURLOPT_FAILONERROR, 1); 
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable 
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $head);
	curl_setopt ($ch, CURLOPT_HEADER, true);
	curl_setopt ($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt ($ch, CURLOPT_VERBOSE, true);
	
	if( strtoupper($type) == "POST" ) {
		curl_setopt($ch, CURLOPT_POST, 1); // set POST method 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $reqbody); // add POST fields 
	} else {
		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}
	
	$result = curl_exec($ch); // run the whole process
	
	if ( $verbose ) {
		print "POST data: " . $reqbody . "\n\nHeaders: ";
		print_r($head);
		print "\n\nBody: ";
		print_r(curl_getinfo($ch));  
		echo "\n\ncURL error number: " .curl_errno($ch); 
		echo "\ncURL error: " . curl_error($ch) . "\n\n";
	}
	  
	$response = curl_getinfo($ch);
	
	if($response['http_code'] != "200"	) {
		$response['errorno'] = curl_errno($ch);
		$response['error'] = curl_error($ch);
	}
	else {
		$result = preg_split("/\r\n\r\n/", $result);
		$response['response_head'] = $result[0] . "\r\n";
		$response['body'] = $result[1];
	}
	
	return $response;
	
	curl_close($ch);
}

class Error {
	function __construct($row) {
		foreach($row as $key => $value) {
			$value = mysql_real_escape_string($value);
			// $value = preg_replace("/\\\\/", "", $value);
			$this->$key = $value;
		}
	}
}

class Tweet {
	function __construct($row) {
		foreach($row as $key => $value) {
			$value = mysql_real_escape_string($value);
			// $value = preg_replace("/\\\\/", "", $value);
			$this->$key = $value;
		}
	}
	
	function add_property($k, $v) {
		$this->$k = $v;
	}
}

class Photo {
	function __construct($row) {
		foreach($row as $key => $value) {
			$value = mysql_real_escape_string($value);
			// $value = preg_replace("/\\\\/", "", $value);
			$this->$key = $value;
		}
	}
	
	function is_downloaded() {
		return file_exists($file = "photos/" . $this->tweet_id . "/" . $this->id . "-" . $this->secret . ".jpg");
	}
	
	function get_filename() {
		$file = "photos/" . $this->tweet_id . "/" . $this->id . "-" . $this->secret . ".jpg";
		
		if( file_exists($file) ) {
			return $file;
		}
		else {
			return "photos/_final.png";
		}
	}
	
	function get_url($size = "") {
		if( $size == "m" || $size == "t" || $size == "b" || $size == "s" ){ return "http://farm$this->farm.static.flickr.com/$this->server/{$this->id}_{$this->secret}_$size.jpg";}
		else{ return "http://farm$this->farm.static.flickr.com/{$this->server}/{$this->id}_{$this->secret}.jpg";}
	}
}
	
class User {
	function __construct($row) {
		foreach($row as $key => $value) {
			$value = mysql_real_escape_string($value);
			$value = preg_replace("/\\\\/", "", $value);
			$this->$key = $value;
		}
	}
	
	function update_session() {
		if($this->sess_exp == 0) return true;
		
		$db = db_connect();

		$sess_id = mysql_real_escape_string($this->sess_id);
		$new_val = mysql_real_escape_string(time() + INTERVAL);

		$sql = "UPDATE `flickrtweet`.`flicktweet_users` SET `sess_exp` = '$new_val' WHERE `userid` = '$this->userid'";

		$result = mysql_query($sql);

		if(mysql_errno() > 0) {
			my_error_log("update_session(\"$sess_id\", $new_val )", "", "", mysql_error());
			
			return false;
		}
		else {
			if(mysql_affected_rows() > 0) {
				return true;
			}
			else {
				my_error_log("update_session(\"$sess_id\", $new_val )", "", "", "sess_id does not exist.");
				return false;
			}
		}
	}
	
	function set_session($interval = true) {
		$db = db_connect();
		
		$sess_id = "";
		
		for($i = 0; $i < 25; $i++) {
			$sess_id .= chr(rand(65, 90));
		}
		
		if( $interval ) {
			$sess_exp = time() + INTERVAL;
		}
		else {
			$sess_exp = 0;
		}

		$sql = "UPDATE `flickrtweet`.`flicktweet_users` SET `sess_exp` = '$sess_exp', `sess_id` = '$sess_id' WHERE `userid` = '$this->userid'";

		$result = mysql_query($sql);

		if(mysql_errno() > 0) {
			my_error_log("set_session()", "", "", mysql_error());
			
			return false;
		}
		else {
			if(mysql_affected_rows() > 0) {
				
				return $sess_id;
			}
			else {
				my_error_log("update_session()", "", "", "could not set sess_id.");
				
				return false;
			}
		}
	}
}

function check_user_credentials($username, $password) {
	$db = db_connect();
	
	$username = mysql_real_escape_string($username);
	$password = mysql_real_escape_string($password);
	$sql = "SELECT * FROM `flickrtweet`.`flicktweet_users` WHERE `username` = '$username';";
	
	$result = mysql_query($sql);
	
	if(mysql_errno() > 0) {
		my_error_log("check_user_credentials(\"$username\", $password)", "", "", mysql_error());
		return false;
	}
	else {
		if(mysql_affected_rows() > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$user = new User($row);
		
				if( $user->password == $password ) {
					
					return $user;
				}
				else {
					
					return false;
				}
			}
		}
		else {
			return false;
		}
	}
}

function check_session($sess_id) {
	$db = db_connect();
	
	$sess_id = mysql_real_escape_string($sess_id);
	$sql = "SELECT * FROM `flickrtweet`.`flicktweet_users` WHERE `sess_id` = '$sess_id'";
	
	$result = mysql_query($sql);
	
	if(mysql_errno() > 0) {
		my_error_log("check_session(\"$sess_id\")", "", "", mysql_error());
		return false;
	}
	else {
		if( mysql_affected_rows() > 0) {
			while($row = mysql_fetch_assoc($result)) {
				$user = new User($row);
	
				if( $user->sess_exp != 0 && $user->sess_exp < time() ) {
					return false;
				}
				else {
					return $user;
				}
			}
		}
		else {
			return false;
		}
	}
}

function delete_error($error_id) {
	$db = db_connect();
	$error_id = mysql_real_escape_string($error_id);

	$sql = "DELETE FROM `flickrtweet`.`flicktweet_errors` WHERE `error_id` = '$error_id' LIMIT 1;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		my_error_log("delete_error<br>$sql", "", "", mysql_error());
		return false;
	}
	else {
		if( mysql_affected_rows() == 1 ) {
			return true;
		}
		else {
			my_error_log("delete_error<br>$sql", "", "", "Error ID #$error_id does not exist, cannot delete.");
			return false;
		}
	}
}

function update_error($error_id, $fields = array()) {
	$db = db_connect();
	$set = array();
	
	if( empty($error_id) || empty($fields)) return false;
	
	foreach($fields as $k=>$v) {
		$set[] = "`$k` = '$v'";
	}
	
	$set = join($set, ", ");
	
	$sql = "UPDATE `flickrtweet`.`flicktweet_errors` SET $set WHERE `error_id` = '$error_id' LIMIT 1;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		my_error_log("update_error<br>$sql", "", "", mysql_error());
		return false;
	}
	else {
		if( mysql_affected_rows() == 1 ) {
			return true;
		}
		else {
			my_error_log("update_error<br>$sql", "", "", "Error ID #$error_id does not exist, cannot update.");
			return false;
		}
	}
}

function vote($int_id) {
	$db = db_connect();
	$set = array();
	
	$int_id = mysql_real_escape_string($int_id);
	
	if( empty($int_id) ) return false;
	
	$sql = "UPDATE `flickrtweet`.`flicktweet_photos` SET `tally` = `tally` + 1 WHERE `int_id` = '$int_id' LIMIT 1;";
	mysql_query($sql);
	
	if( mysql_errno() > 0 ) {
		my_error_log("vote()", "", "", mysql_error());
		return false;
	}
	else {
		if( mysql_affected_rows() == 1 ) {
			return true;
		}
		else {
			my_error_log("vote", "", "", "Image #$int_id does not exist, cannot update.");
			return false;
		}
	}
}

function my_error_log($process, $tweet_id, $photo_id, $descrip, $resolved = 'no') {
	$process = mysql_real_escape_string($process);
	$tweet_id = mysql_real_escape_string($tweet_id);
	$photo_id = mysql_real_escape_string($photo_id);
	$descrip = mysql_real_escape_string($descrip);
	$resolved = mysql_real_escape_string($resolved);

	$sql = "INSERT INTO  `flickrtweet`.`flicktweet_errors` (
	`error_id`,
	`process` ,
	`tweet_id` ,
	`photo_id` ,
	`description`,
	`resolved`
	)
	VALUES (
	'', '$process', '$tweet_id' , '$photo_id' ,  '$descrip', '$resolved'
	);";

	mysql_query($sql);

	if( mysql_errno() > 0 ) {
		print "\tMySQL ERROR: " . mysql_error() . "\n";
		my_error_log("trim_tweets()", "","", mysql_error());
		return false;
	}
	else return true;
}

class TweetJob {
	private $db;
	public $last_batch;

	function __construct() {
		$this->db = db_connect();
	}

	public function collect_tweets() {
		$batch = time();
		$this->last_batch = $batch;
		
		// $response = http_req("GET", "http://api.twitter.com/1/statuses/public_timeline.xml");
		
		$contents = file_get_contents("http://api.twitter.com/1/statuses/public_timeline.xml");
	
		if( $contents ) {
			libxml_clear_errors();
			libxml_use_internal_errors(true);
			$stati = simplexml_load_string($contents);
	
			if( isset($stati->status) ) {
				foreach( $stati->status as $status ) {			
					$sql = "INSERT IGNORE INTO `flickrtweet`.`flicktweet_tweets` (`batch`, `tweet_id`, `tweet_text`, `tweet_lang`, `tweet_userid`, `tweet_screename`, `tweet_user_name`, `profile_photo`, `location`) VALUES ('$batch', '" . mysql_real_escape_string($status->id) . "', '" . mysql_real_escape_string($status->text) ."', '" . mysql_real_escape_string($status->user->lang) . "', '" . mysql_real_escape_string($status->user->id) . "', '" . mysql_real_escape_string($status->user->screen_name) . "', '" . mysql_real_escape_string($status->user->name) . "', '" . mysql_real_escape_string($status->user->profile_image_url) . "', '" . mysql_real_escape_string($status->user->location) . "');";
					mysql_query($sql);
		
					if( mysql_errno() > 0 ) {
						print "\tMySQL ERROR: " . mysql_error() . "\n";
						my_error_log("collect_tweets() - adding statuses foreach()", "","", mysql_error());
						return false;
					}
				}
				if( mysql_errno() > 0 ) {
					print "\tMySQL ERROR: " . mysql_error() . "\n";
					my_error_log("collect_tweets() - adding statuses foreach()", "","", mysql_error());
					return false;
				}
				else {
					echo "-- 20 tweets collected! --\n";
					return $this->trim_tweets();
				}
			}
			else {
				echo "-- Collection / Parsing error --\n\n";		
				
				my_error_log("collect_tweets()", "","", "Collection or parser error");
				return false;
			}
		}
		else {
			echo "ERROR: " . mysql_real_escape_string($response['error']);
			my_error_log("collect_tweets() - http response", "","", $response['error']);
			return false;
		}
	}

	public function trim_tweets() {
		//trim: non-english tweets
		$sql = array();
	
		$sql['non-English / undefined lang'] = "DELETE FROM `flicktweet_tweets` WHERE `tweet_lang`<>'en' OR `tweet_lang`='';";
	
		foreach($sql as $key => $value) {
			mysql_query($value);
	
			if( mysql_errno() > 0 ) {
				echo mysql_error();
				return false;
			}
			else {
				echo "-- " . mysql_affected_rows() . " $key tweets dropped --\n";
				return true;
			}
		}
	}
	
	function remove_photo($int_id) {
		$sql = "DELETE FROM `flicktweet_photos` WHERE `int_id` = '$int_id' LIMIT 1;";
		mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			echo "\tMySQL ERROR: " . mysql_error() . "\n";
			my_error_log("remove_photo()", "", "$int_id", mysql_error());
			return false;
		}
		else {
			if( mysql_affected_rows() == 1 ) {
				echo "Photo #$int_id deleted successfully!\n";
				return true;
			}
			else {
				echo "\tERROR: Photo #$int_id does not exist!\n";
				my_error_log("remove_tweet()", "", $int_id, "Photo could not be deleted.");
				return false;
			}
		}
	}
	
	function remove_photo_tweet($tweet_id) {
		$sql = "DELETE FROM `flicktweet_photos` WHERE `tweet_id` = '$tweet_id';";
		mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			echo "\tMySQL ERROR: " . mysql_error() . "\n";
			my_error_log("remove_photo_batch()", $tweet_id, "", mysql_error());
			return false;
		}
		else {
			if( mysql_affected_rows() > 0 ) {
				if(file_exists("photos/" . $tweet_id . "/") ) {
					delete_directory("photos/$tweet_id/");
				}
				
				echo mysql_affected_rows() . " photos deleted successfully!\n";
				return true;
			}
			else {
				echo "\tWARNING: There are no photos for tweet #$tweet_id.  Could not delete.\n";
				#my_error_log("remove_photos_batch()", "", $photo_id, "Photos could not be deleted."); # why log it?  maybe not an error.
				return false;
			}
		}
	}
	
	function remove_tweet($tweet_id) {
		$sql = "DELETE FROM `flicktweet_tweets` WHERE `tweet_id` = '$tweet_id' LIMIT 1;";
		mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			echo "\tMySQL ERROR: " . mysql_error() . "\n";
			my_error_log("remove_tweet()", $tweet_id, "", mysql_error());
			return false;
		}
		else {
			if( mysql_affected_rows() == 1 ) {
				echo "Tweet #$tweet_id deleted successfully!\n";
				$this->remove_photo_tweet($tweet_id);
				return true;
			}
			else {
				echo "\tERROR: Tweet #$tweet_id does not exist!\n";
				my_error_log("remove_tweet()", $tweet_id, "", "Tweet could not be deleted.");
				return false;
			}
		}
	}
	
	public function flickrScour_tweet($tweet) {
		require_once "phpFlickr.php";
		$text = preg_replace("/ /i", ",", $tweet->tweet_text);
		$id = $tweet->tweet_id;

		$photo_array = array();
		$f = new phpFlickr("a34b23783f3da2d978f6620d857e67e4");
		$photos = $f->photos_search(array("tags"=>"$text", "tag_mode" => "any", "sort" => "relevance"));
		$photos = $photos['photo'];

		if( count($photos) >= 5 ) {
			$sql = "";

			for($i = 0; $i < 5; $i++) {
				$photo = new Photo($photos[$i]);
	
				$sql = "INSERT IGNORE INTO `flicktweet_photos` (`int_id`, `tweet_id`, `id`, `secret`, `server`, `farm`, `title`, `tally`) VALUES ('', '$id',  '$photo->id',  '$photo->secret',  '$photo->server',  '$photo->farm',  '$photo->title', 0 ); ";
	
				mysql_query($sql);
				if( mysql_errno() > 0 ) {
					echo mysql_error();
					my_error_log("flickrScour_tweet()", $id, $photo->id, mysql_error());
				}
				else {
					echo "Photo $photo->id added to the db for tweet $id!\n";
				}
			}
		}
		else {
			$er = "Not enough images for tweet $id!  Removing...";
			echo "\tERROR: " . $er . "\n";

			my_error_log("flickrScour()", "$id", "", "$er", $this->remove_tweet($id) ? "yes - tweet deleted" : "no - error");
		}
	}

	public function flickrScour($batch = "*") {
		$db = db_connect();
		
		$params = array();
		if( $batch != "*" ) {
			$params["batch"] = mysql_real_escape_string($batch);
		}
	
		$tweets = $this->get_tweets($params);
		foreach($tweets as $tweet) {
			$this->flickrScour_tweet($tweet);
		}
		return true;
	}
	
	function clean_downloads() {
		$run_download = false;
		$to_delete = array();
		
		$photos = $this->get_photos_tweet();
		$files = read_directory("photos/");
		
		foreach($photos as $photo) {
			if( !$photo->is_downloaded() ) {
				$run_download = true;
			}
			else {
				$files[$photo->tweet_id][$photo->id . "-" . $photo->secret . ".jpg"] = null;
			}
		}
		
		foreach($files as $k=>$v) {
			if( is_array($v) && !empty($v) ) {
				foreach($v as $x=>$y) {
					if($y != "")
						unlink("photos/$k/$y");
				}
			}
		}
		
		$files = read_directory("photos/");
		
		foreach($files as $k=>$v) {
			if( is_array($v) && empty($v) ) {
				delete_directory("photos/" . $k);
			}
		}
		return true;
	}
	
	function download_images() {
		if( $photos = $this->get_photos_tweet()) {
			foreach($photos as $photo) {
				$dir = "photos/$photo->tweet_id";
				if( !file_exists($dir) ) mkdir($dir) or die;
				$filename = $dir . "/" . $photo->id . "-" . $photo->secret . ".jpg";
			
				if( !is_file($filename) ) {
					$url = $photo->get_url();
					$f = file_get_contents($url);
					
					if( !$f ) {
						$success = $this->remove_photo($photo->id);
						my_error_log("download_images()", "", "$photo->id", "Could not download image", $success ? "Yes - removed" : "no");
						continue;
					}
					
					$f2 = fopen($filename, "w");
			
					fwrite($f2, $f);

					fclose($f2);
			
					echo "File " . $filename . " saved!<br>";
					flush(); ob_flush();
				}
			}
			return true;
		}
		else {
			my_error_log("download_images()", "", "", "Could not retrieve tweets!");
			return false;
		}
	}
	
	function get_random_tweet() {
		$photos = array();
		
		$offset_result = mysql_query( "SELECT FLOOR(RAND() * COUNT(*)) AS `offset` FROM `flicktweet_tweets` ");
		
		if( !(mysql_errno() > 0) && !$offset_row = mysql_fetch_object( $offset_result ) ) {
			my_error_log("get_random_tweet()", "", "", mysql_error());
			return false;
		}
		
		$offset = $offset_row->offset;
		if( !(mysql_errno() > 0) && !$result = mysql_query( " SELECT * FROM `flicktweet_tweets` LIMIT $offset, 1 " ) ) {
			my_error_log("get_random_tweet()", "", "", mysql_error());
			return false;
			die;
		}
		
		while($row = mysql_fetch_assoc($result) ) {
			$tweet = new Tweet($row);
		}
		
		$sql = "SELECT * FROM `flicktweet_photos` WHERE `tweet_id` = '$tweet->tweet_id';";
		$results = mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			my_error_log("get_random_tweet()", "", "", mysql_error());
			return false;
			die;
		}
		elseif( mysql_affected_rows() == 0 ) {
			return $this->get_random_tweet();
		}
		
		while($row = mysql_fetch_assoc($results)) {
			$p = new Photo($row);
			$photos[] = $p;
		}
		
		$tweet->add_property("photos", $photos);
		return $tweet;
		
	}
	
	function get_tweets($params = array()) {
		$tweets = array();
		
		if(!empty($params)) {
			$p_string = array();
			foreach($params as $k=>$v) {
				$p_string[] = "`$k` = '$v'";
			}
			
			$p_string = join(" AND ", $p_string);
		}
		else {
			$p_string = "1";
		}
		
		$sql = "SELECT * FROM `flicktweet_tweets` WHERE $p_string ORDER BY `batch`;";
		
		$result = mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			echo mysql_error();
			
			$out = array();
			foreach($params as $k => $v) {
				$out[] = "`$k` => '$v'";
			}
			$out = mysql_real_escape_string(join(", ", $out));
			
			my_error_log("get_tweets($out)", "", "", mysql_error());
			return false;
		}
		else {
			while( $row = mysql_fetch_assoc($result) ) {
				$tweets[] = new Tweet($row);
			}
			return $tweets;
		}
	}
	
	function get_photos_tweet($tweet_id = "") {
		$photos = array();
		$tweet_id = mysql_real_escape_string($tweet_id);
		
		if($tweet_id == "") {
			$tweet_id = "1";
		}
		else {
			$tweet_id = "`tweet_id` = '$tweet_id'";
		}
		
		$sql = "SELECT * FROM `flicktweet_photos` WHERE $tweet_id;";
		
		$result = mysql_query($sql);
		
		if( mysql_errno() > 0 ) {
			echo mysql_error();
			my_error_log("get_photos()", "", "", mysql_error());
			return false;
		}
		else {
			while( $row = mysql_fetch_assoc($result) ) {
				$photos[] = new Photo($row);
			}
			return $photos;
		}
	}
	
	function run() {
		$this->collect_tweets();
		$this->flickrScour($this->last_batch);
	}
}

?>