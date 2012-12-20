<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/view_settings.php
 * 
 * Main part of /i/profile/edit webpage
 *
 */
?> 

	<?php $this->load->view('includes/view_alerts'); ?>

	<?=form_open('i/profile/update', 'class="form-horizontal"')?>

		<h2 class="form-heading">Edit your profile </h2>		
		
		<div class="control-group">
			<label class="control-label" for="form_fullname">Name (required)</label>
			<div class="controls">
				<?=form_input('fullname', $user->fullname, 'class="span6" id="form_fullname"')?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="form_email">Email<?=($user->password ? ' (required)' : '')?></label>
			<div class="controls">
				<?=form_input('email', $user->email, 'class="span6" id="form_email"')?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="form_location">Location</label>
			<div class="controls">
				<?=form_input('location', $user->location, 'class="span6" id="form_location"')?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="form_bio">Bio</label>
			<div class="controls">
				<?=form_input('bio', $user->bio, 'class="span6" id="form_bio"')?>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="form_web">Website</label>
			<div class="controls">
				<?=form_input('web', $user->web, 'class="span6" id="form_web"')?>
			</div>
		</div>

		<!-- Twitter profile connect/disconnect -->
		<div class="control-group">
			<label class="control-label" for="form_twitter">Twitter</label>
			<div class="controls">
				<?php if ($user->twitter_id): ?>
					<a href="http://twitter.com/<?=$user->twitter_username?>" target="_blank" class="no_hover_underline">
						<img src="<?=$user->twitter_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
					</a>
					<?php /* Although the following link is the same it is separated from previous,
						   * not to underline the gap between the image and the name.
						   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
					<a href="http://twitter.com/<?=$user->twitter_username?>" target="_blank">
						<strong><?=($user->twitter_name ? $user->twitter_name : '')?></strong>
						@<?=$user->twitter_username?>
					</a>&nbsp;
				<?php endif; ?>

				<?php if ($user->twitter_id): ?>
					<a href="<?=base_url()?>i/profile/twitter" id="button_twitter" class="btn">
						Disconnect
					</a>
				<?php else: ?>
					<a href="<?=base_url()?>i/profile/twitter" id="button_twitter" class="btn btn-info" style="width: 130px">
						Connect Twitter
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Linkedin profile connect/disconnect -->
		<div class="control-group"> 
			<label class="control-label" for="form_linkedin_username">Linkedin</label>
			<div class="controls">
				<?php if ($user->linkedin_id): ?>
					<a href="http://linkedin.com/<?php echo $user->linkedin_username; ?>" target="_blank" class="no_hover_underline">
						<img src="<?=$user->linkedin_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
					</a>
					<?php /* Although the following link is the same it is separated from previous,
						   * not to underline the gap between the image and the name.
						   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
					<a href="http://linkedin.com/<?php echo $user->linkedin_username; ?>" target="_blank">
						<?php if($user->linkedin_name): ?>
							<strong><?php echo $user->linkedin_name; ?></strong>
						<?php else: ?>
							linkedin.com/<?php echo $user->linkedin_username; ?>
						<?php endif; ?>
					</a>&nbsp;
				<?php endif; ?>

				<?php if ($user->linkedin_id): ?>
					<a href="<?=base_url()?>i/profile/linkedin" id="button_linkedin" class="btn">
						Disconnect
					</a>
				<?php else: ?>
					<a href="<?=base_url()?>i/profile/linkedin" id="button_linkedin" class="btn btn-info" style="width: 130px">
						Connect LinkedIn
					</a>
				<?php endif; ?>
			</div>
		</div>

		<!-- Facebook profile connect/disconnect -->
		<div class="control-group"> 
			<label class="control-label" for="button_facebook">Facebook</label>
			<div class="controls">
				<?php if ($user->facebook_id): ?>	
					<a href="http://facebook.com/<?=$user->facebook_username?>" target="_blank" class="no_hover_underline">
						<img src="<?=$user->facebook_img_url?>" style="height: 30px; width: 30px" class="img-rounded small-img-rounded" />
					</a>
					<?php /* Although the following link is the same it is separated from previous,
						   * not to underline the gap between the image and the name.
						   * CSS class .no_hover_underline in the <a> tag of the image does the trick */ ?>
					<a href="http://facebook.com/<?=$user->facebook_username?>" target="_blank">
						<?=($user->facebook_name ? $user->facebook_name : 'facebook.com/'.$user->facebook_username)?>
					</a>&nbsp;
				<?php endif; ?>
			
				<?php if ($user->facebook_id): ?>
					<a href="<?=base_url()?>i/profile/facebook" id="button_facebook" class="btn">
						Disconnect
					</a>
				<?php else: ?>
					<a href="<?=base_url()?>i/profile/facebook" id="button_facebook" class="btn btn-info" style="width: 130px">
						Connect Facebook
					</a>
				<?php endif; ?>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="form_interested_in">Interested in:</label>
			<div class="controls">
				<textarea name="interested_in" rows="4" class="span6" id="form_interested_in"><?=$user->interested_in?></textarea>
			</div>
		</div>

		<div class="control-group">
			<span class="ajax"><a class="btn btn-large" href="<?=user_profile_url()?>">Cancel</a></span>
			&nbsp;
			<?=form_submit('profile_submit', 'Save changes', 'class="btn btn-large btn-primary"')?>
		</div>

		<?php if ($user->email): ?>
			<p><a href="<?=base_url()?>i/profile/password">Change password</a></p>
		<?php endif; ?>

	<?=form_close()?>
