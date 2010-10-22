<div id="header">
	flickrtweet <div id='beta_label'>βeta</div></div>
	<div id="toolbar">
		<?php
		if($user) {
			echo "welcome, <span id='name'>" . strtolower($user->nickname) . "</span>!";
			echo " [<a class=\"toolbar\" href=\"home\">home</a> | <a class=\"toolbar\" href=\"home?logout\">logout</a>]";
			echo "<br>Session expires " . ($user->sess_exp == "0" ? "never" : "at " . date("h:ia m/d/Y", $user->sess_exp)) . ".";
		}
		else {
			echo "[<a class=\"toolbar\" href=\"home\">login</a>]";
		}
		?>
	</div>