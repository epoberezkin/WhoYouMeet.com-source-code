<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/view_user.php
 * 
 * Main part of /i/meetMe/user webpage
 *
 * Variable passed:
 *   $person - dbFullUser object with person information
 *
 */

$current_user_name = $this->session->userdata('fullname');
$meet_me = isset($user->reason);
$back_to_meetMe = $this->input->server('HTTP_REFERER') == base_url() . 'i/meetMe';

$this->load->language('messages');

?> 

		<?php $this->load->view('includes/view_alerts'); ?>

		<p class="ajax"<?=($back_to_meetMe ? '' : ' style="display: none"')?>>
			<a href='<?=base_url()?>i/meetMe' class="no-hover-underline">
				<img src="<?=base_url()?>img/icon-left.png" class="contact-icon-page" />
			</a>
			<a href='<?=base_url()?>i/meetMe'>
				<span style="vertical-align: middle">Back to the list of users <strong>who want to meet me</strong></span>
			</a>
		</p>

		<?php $this->load->view('includes/view_person_template', array(
			'person' => $user,
			'current_user' => false,
			'show_user' => true,
			'meet_me' => $meet_me));
		?>

		<?php if($user->interested_in): ?>
		<h4>Interested in:</h4>

		<p><?=show_line_breaks(my_auto_link($user->interested_in))?></p>

		<?php endif; ?>

		<br />

		<?php if($meet_me): ?>
		<h4><strong><?=$user->fullname?></strong>'s reason to meet me:</h4>

		<p><?php echo ($user->reason ? show_line_breaks(my_auto_link($user->reason)) : '<em>Not specified.</em>'); ?></p>

		<br />
		<?php endif; ?>

		<p>
		<?php if ($user->email && $meet_me): ?>
		
			<a class ="btn" href="mailto:<?=$user->email . '?subject=' . rawurldecode(my_lang('view_user_email_subject')) . '&body=' . str_replace('_', '%0D%0A', rawurldecode(my_lang('view_user_button_email_body', $user->fullname))) . $current_user_name?>">
				Send email message
			</a>&nbsp;&nbsp;
		<?php endif; ?>

		<?php if ( ! $user->connected_person_id): ?>
			<a href="#meetUser" data-toggle="modal" class="btn btn-info"><i class="icon-plus icon-white"></i><i class="icon-user icon-white"></i> Add to my list</a>
		<?php else: ?>
			<a class="ajax" href="<?=base_url() . 'i/iMeet/person/' . $user->connected_person_id?>">
				<button class="btn disabled" style="cursor: pointer">
					<strong><?=$user->fullname?></strong> is in my list
				</button>
			</a>
		<?php endif; ?>
		</p>

<!-- Modal add user to I want to meet list / login-signup if not logged in -->
<?php if ($this->session->userdata('logged_in')): ?>
<div id="meetUser" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form name="meet_user" action="/i/iMeet/add_user" method="post" accept-charset="utf-8">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h2 id="myModalLabel">Add <strong><?=$user->fullname?></strong></h2>
		<h4>to my list of people <strong>I want to meet?</strong></h4>
	</div>
	<div class="modal-body">
		<input type="hidden" name="user_to_meet_id" value="<?=$user->id?>" />
		<p>Reason to meet:</p>
		<textarea name="reason" rows="4" style="width: 512px" id="form_reason" placeholder="Why do you want to meet <?=$user->fullname?>?"></textarea>
	</div>
	<div class="modal-footer">
		<button data-dismiss="modal" aria-hidden="true" class="btn btn-large">Cancel</button>
		<input type="submit" name="meet_user_submit" value="Add" class="btn btn-info btn-large" />
	</div>
	</form>
</div>
<?php else: ?>
<!-- Modal login/signup invitation -->
<div id="meetUser" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 600px">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h2 id="myModalLabel">Sign in or join <strong>Who You Meet</strong></h2>
		<h3>to add <strong><?=$user->fullname?></strong> to your list</h3>
	</div>
	<div class="modal-body">
		<p style="margin-bottom: 20px">
			<span style="vertical-align: -5px"><big><big>Sign in with</big></big>&nbsp;</span>
			<a class="btn btn-large" href="<?=base_url()?>login/twitter_redirect">
				<img src="<?=base_url()?>img/icon-twitter-color.png" class="contact-icon-login user-login-popup-icon"/>
				<span style="vertical-align: middle">&nbsp;Twitter</span>
			</a>
			&nbsp;
			<a class="btn btn-large" href="<?=base_url()?>login/linkedin_redirect">
				<img src="<?=base_url()?>img/icon-linkedin-color.png" class="contact-icon-login user-login-popup-icon"/>
				<span style="vertical-align: middle">&nbsp;LinkedIn</span>
			</a>
			&nbsp;
			<a class="btn btn-large" href="<?=base_url()?>login/facebook_redirect">
				<img src="<?=base_url()?>img/icon-facebook-color.png" class="contact-icon-login user-login-popup-icon"/>
				<span style="vertical-align: middle">&nbsp;Facebook</span>
			</a>
		</p>
		<p class="ajax" style="margin-bottom: 20px">
			<a href="<?=base_url()?>signup">Join Who You Meet</a> or <a href="<?=base_url()?>login">Sign in</a> with email and password.
		</p>
		<p class="ajax">
			Learn more about <a href="<?=base_url()?>">Who You Meet</a>.
		</p>
	</div>
	<div class="modal-footer">
		<button data-dismiss="modal" aria-hidden="true" class="btn">Close</button>
	</div>
</div>
<?php endif; ?>
