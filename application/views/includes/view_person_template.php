<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_person_template.php
 * 
 * Template to display user's and person's information
 *---------------------------------------------------------------
 *
 * view_person and view_profile use this template
 *
 * Variable passed:
 *   $person - dbFullUser or dbPersonToMeet object with user/person information
 *
 */

$this->load->language('messages');

?>

	<div>
	<?php if ($person->big_picture_url): ?>
		<?php if ($show_user): ?>
			<div style="float: left; padding-right: 10px; height: 100px; width: 100px">
				<img src="<?=$person->big_picture_url?>" style="height: 100px; width: 100px" class="img-rounded" />
			</div>
		<?php else: ?>
			<div style="float: left; padding-right: 10px; height: 80px; width: 80px">
				<img src="<?=$person->big_picture_url?>" style="height: 80px; width: 80px" class="img-rounded" />
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<div style="float: none">
		<?php if ($current_user): ?>
			<h1><strong><?=$person->fullname?></strong></h1>
			<h4 style="max-width: 600px"><?=my_auto_link($person->bio)?></h4>
		<?php elseif ($show_user): ?>
			<h2>
				<strong><?=$person->fullname?></strong>
				<?php if ($meet_me): ?>
					<span class="label" style="vertical-align: 3px">
						<big><big>wants to meet me!</big></big>
					</span>
				<?php endif; ?>
			</h2>
			<h4 style="max-width: 600px"><?=my_auto_link($person->bio)?></h4>
		<?php else: ?>
			<h4>
				<strong><?=$person->fullname?></strong>
				<span class="ajax" style="vertical-align: 2px">
				<?php if ($meet_me): ?>
					<a href="<?=connected_user_profile_url($person)?>">
						<span class="label label-success"><big>wants to meet me!</big></span>
					</a>
				<?php elseif ($person->usertomeetid): ?>
					<a href="<?=connected_user_profile_url($person)?>">
						<span class="label"><big>is a user!</big></span>
					</a>
				<?php elseif ($person->email): ?>
					<a href="mailto:<?=$person->email . '?subject=' . rawurldecode(my_lang('view_person_invite_email_subject')) . '&body=' . str_replace('_', '%0D%0A', rawurldecode(my_lang('view_person_invite_email_body', $person->fullname))) . $this->session->userdata('fullname')?>">
						<span class="label label-info"><big>Invite!</big></span>
					</a>
				<?php elseif ($person->twitter_username): ?>
					<a href="https://twitter.com/intent/tweet?screen_name=<?=$person->twitter_username?>&amp;text=<?=rawurlencode(my_lang('view_person_invite_tweet_text'))?>&amp;tw_p=tweetbutton&amp;via=WhoYouMeet" target="_blank">
						<span class="label label-info"><big>Invite!</big></span>
					</a>
				<?php endif; ?>
				</span>
			</h4>
			<p style="max-width: 600px"><?=my_auto_link($person->bio)?></p>
		<?php endif; ?>
	</div>
	</div>

	<p style="float: none">
	<?=$person->location?>
	<?php if ($person->location && $person->web): ?>
	<span class="divider"> · </span>
	<?php endif; ?>
	<a href="<?=prep_url($person->web)?>" target="_blank"><?=$person->web?></a>&nbsp;
	</p>

	<?php if ( $show_user): ?><p>&nbsp;</p><?php else: ?><div style="height: 10px"></div><?php endif; ?>

	<?php if (($person->email && ($meet_me || ! $show_user )) || $current_user): ?>
		<p>
			<img src="<?=base_url()?>img/icon-envelope.png" class="contact-icon-page" />
			<span style="vertical-align: sub;">
				Email: 
				<a href="mailto:<?=($current_user ? $person->email : $person->email . '?subject=' . rawurldecode(my_lang('view_user_email_subject')) . '&body=' . str_replace('_', '%0D%0A', rawurldecode(my_lang('view_user_link_email_body', $person->fullname))) . $this->session->userdata('fullname') ) ?>"><?=$person->email?></a>
			</span>
			<?php if ($meet_me && $show_user): ?>
				<?php if ($person->verified): ?>
					<span class="label label-success">verified</span>
				<?php else: ?>
					<span class="label">not verified</span>
				<?php endif; ?>
			<?php endif; ?>

			<?php if($current_user): ?>
				<?php if ($person->email && $person->verified): ?>
					<span class="label label-success">verified</span>
				<?php elseif ($person->email): ?>
					<span class="label label-warning">not verified</span>

					<span style="vertical-align: middle;"><small>
						<a href="<?=base_url()?>i/profile/verify">Verify</a>
						&nbsp;&nbsp;
						<a href="#why" data-toggle="modal">Why?</a>
					</small></span>
				<?php else: ?>
					<span class="label label-important">not set</span>
					<span style="vertical-align: middle;"><small>
						<a href="<?=base_url()?>i/profile/edit">Enter email</a>
						&nbsp;&nbsp;
						<a href="#why" data-toggle="modal">Why?</a>
					</small></span>

				<?php endif; ?>

				<!-- Modal - why enter and verify your email address -->
				<div id="why" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">Why enter and verify your email address?</h3>
				  </div>
				  <div class="modal-body">
					<p>Please enter and verify your email so that people who you want to meet (your <strong>"I want to meet"</strong> list) could send you messages.</p>
					<p>Also, you need to have have LinkedIn or Twitter or Facebook connected or your email verified so that people who you want to meet could see you in their <strong>"Who wants to meet me"</strong> lists.</p>
				  </div>
				  <div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				  </div>
				</div>


				<!-- Modal - why connect social profiles -->
				<div id="why_connect" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h3 id="myModalLabel">Why connect your LinkedIn, Twitter and Facebook profiles?</h3>
				  </div>
				  <div class="modal-body">
					<p>When people add you to their <strong>"I want to meet"</strong> lists, they can specify only one of your social profiles.</p>
					<p>If any of your profiles matches, you will see these people in your <strong>"Who wants to meet me"</strong> list.</p>
					<p>By connecting all your social profiles (LinkedIn, Twitter and Facebook), you won't miss anyone who wants to meet you.</p>
				  </div>
				  <div class="modal-footer">
					<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
				  </div>
				</div>


			<?php endif; ?>
		</p>
	<?php endif; ?>


	<!-- Twitter profile -->
	<?php if ($person->twitter_username || $current_user): ?>
		<p>
			<img src="<?=base_url()?>img/icon-twitter.png" class="contact-icon-page" />
			<span class="social-name">Twitter: </span>
			<?php if ($person->twitter_username): ?>
				<span style="vertical-align: middle;">
				<a href="http://twitter.com/<?=$person->twitter_username?>" target="_blank" class="no_hover_underline">
					<?php if ($person->twitter_img_url): ?>
						<img src="<?=$person->twitter_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
					<?php endif; ?>
				</a>
				<?php /* Although the following link is the same it is separated from previous,
					   * not to underline the gap between the image and the name.
					   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
				<?php if ($person->twitter_username): ?>
					<a href="http://twitter.com/<?=$person->twitter_username?>" target="_blank">
						@<?php echo $person->twitter_username; ?>
					</a>
				<?php endif; ?>
				</span>
			<?php else: ?>
				<span class="label label-info">not connected</span>

				<span style="vertical-align: middle;"><small>
					<a href="<?=base_url()?>i/profile/twitter">Connect</a>
					&nbsp;&nbsp;
					<a href="#why_connect" data-toggle="modal">Why?</a>
				</small></span>
			<?php endif; ?>
			<?php if(!$current_user): ?>
			<span style="vertical-align: -6px">
			<a href="https://twitter.com/<?=$person->twitter_username?>" class="twitter-follow-button" data-show-count="false" data-show-screen-name="false" data-size="small" data-dnt="true">Follow</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			<?php /* if ($show_user): ?>
			&nbsp;
			<a href="https://twitter.com/intent/tweet?screen_name=<?=$person->twitter_username?>&text=<?=rawurlencode(my_lang('view_user_tweet_text'))?>" class="twitter-mention-button" data-size="large" data-dnt="true" data-via="WhoYouMeet">Tweet to <?=$person->twitter_username?></a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
			<?php endif; */ /* "tweet to user" button is removed - to much clutter */ ?>
			</span>
			<?php endif; ?>

		</p>
	<?php endif; ?>


	<!-- Linkedin profile -->
	<?php if ($person->linkedin_username || $current_user): ?>
		<p>
			<img src="<?=base_url()?>img/icon-linkedin.png" class="contact-icon-page" />
			<span class="social-name">LinkedIn: </span>
			<?php if ($person->linkedin_username): ?>
				<span style="vertical-align: middle;">
				<a href="http://linkedin.com/<?=$person->linkedin_username?>" target="_blank" class="no_hover_underline">
					<?php if ($person->linkedin_img_url): ?>
						<img src="<?=$person->linkedin_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
					<?php endif; ?>
				</a>
				<?php /* Although the following link is the same it is separated from previous,
					   * not to underline the gap between the image and the name.
					   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
				<a href="http://linkedin.com/<?=$person->linkedin_username?>" target="_blank">
					<?php echo ($person->linkedin_name ? $person->linkedin_name : 'linkedin.com/' . $person->linkedin_username); ?>
				</a>
				</span>
			<?php else: ?>
				<span class="label label-info">not connected</span>

				<span style="vertical-align: middle;"><small>
					<a href="<?=base_url()?>i/profile/linkedin">Connect</a>
					&nbsp;&nbsp;
					<a href="#why_connect" data-toggle="modal">Why?</a>
				</small></span>
			<?php endif; ?>
		</p>
	<?php endif; ?>

		
	<!-- Facebook profile -->
	<?php if ($person->facebook_username || $current_user): ?>
		<p>
			<img src="<?=base_url()?>img/icon-facebook.png" class="contact-icon-page" />
			<span class="social-name">Facebook: </span>
			<?php if ($person->facebook_username): ?>
				<span style="vertical-align: middle;">
				<a href="http://facebook.com/<?=$person->facebook_username?>" target="_blank" class="no_hover_underline">
					<img src="<?=$person->facebook_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
				</a>
				<?php /* Although the following link is the same it is separated from previous,
					   * not to underline the gap between the image and the name.
					   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
				<a href="http://facebook.com/<?=$person->facebook_username?>" target="_blank">
					<?=($person->facebook_name ? $person->facebook_name : 'facebook.com/'.$person->facebook_username)?>
				</a>
				</span>
			<?php else: ?>
				<span class="label label-info">not connected</span>

				<span style="vertical-align: middle;"><small>
					<a href="<?=base_url()?>i/profile/facebook">Connect</a>
					&nbsp;&nbsp;
					<a href="#why_connect" data-toggle="modal">Why?</a>
				</small></span>
			<?php endif; ?>

		</p>
	<?php endif; ?>
