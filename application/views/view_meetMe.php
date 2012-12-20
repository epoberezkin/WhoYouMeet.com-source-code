<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_meetMe.php
 *
 * List of people who want to meet me (current user) - main part of /i/meetMe webpage
 *---------------------------------------------------------------
 *
 * Variables passed:
 *  $list - list of people who wants to meet user
 *
 */
?>

	<?php $this->load->view('includes/view_alerts'); ?>

	<h2>Users who want to meet <strong>me</strong></h2>
	
	<?php if ( $list ) : ?>

		<?php $this->load->view('includes/view_table_template',
			array(
				'list_item_path' => 'i/meetMe/user/',
				'last_column_heading' => 'Reason to meet me',
				'list_of_users' => true
				)
			); ?>
	
		<p>If you invite people you know to use <strong>Who You Meet</strong>, more people will want to meet you :)</p>

	<?php else: ?>
		<p>The list of people who want to meet you is empty.</p>
		<p>Why not invite people you know to use <strong>Who You Meet</strong>?</p>
	<?php endif; ?>

	<p><?php $this->load->view('includes/view_share_buttons'); ?></p>
