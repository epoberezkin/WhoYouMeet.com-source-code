<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/view_profile.php
 * 
 * Main part of /i/profile webpage that displays user's information
 *
 * Variable passed:
 *   $user - dbFullUser object with user's information
 *
 */
?>
 
	<?php $this->load->view('includes/view_alerts'); ?>

	<?php $this->load->view('includes/view_person_template', array(
			'person' => $user,
			'current_user' => true,
			'show_user' => true,
			'meet_me' => false
		));
	?>

	<br />

	<h4>Interested in:</h4>

	<p><?php echo show_line_breaks(my_auto_link($user->interested_in)); ?></p>

	<br />

	<p class="ajax"><a href='<?=base_url()?>i/profile/edit'>Edit profile</a></p>
