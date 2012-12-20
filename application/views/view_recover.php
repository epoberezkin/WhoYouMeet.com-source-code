<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_recover.php
 *
 * Main part of /login/Recover webpage
 *---------------------------------------------------------------
 *
 */
?>

	<?=form_open('login/recover/validate', 'class="form-signin"')?>

		<h2 class="form-signin-heading">Recover your password</h2>

		<?php $this->load->view('includes/view_alerts'); ?>

		<p><?=form_input('email', (isset($email) ? $email : ''), 'class="input-block-level" placeholder="Email address"')?></p>

		<p><?=form_submit('login_submit', 'Send recovery message', 'class="btn btn-large btn-primary"')?></p>

		<p class="ajax"><a href="<?=base_url()?>login">I know my password! I want to sign in!</a></p>

	<?=form_close()?>
