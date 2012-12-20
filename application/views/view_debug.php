<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_debug.php
 *
 * Debug page
 *---------------------------------------------------------------
 *
 *
 */
?>

	<h1>Session data</h1>

	<pre>
		<?php print_r($this->session->all_userdata()); ?>
	</pre>

