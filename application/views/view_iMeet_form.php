<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_iMeet_form.php
 * 
 * Form to edit person's information - main part of  /i/iMeet/new and /i/iMeet/edit/$person->id webpages
 *---------------------------------------------------------------
 *
 * Variables passed:
 *  $person - person information
 *  $new_person - true to create new person, false to edit person
 *
 */
?>
<?=form_open(($new_person ? 'i/iMeet/add_form' : 'i/iMeet/update/' . $person->id), 'class="form-horizontal"')?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	<h2 id="myModalLabel"><?=($new_person ? 'Add person I want to meet' : 'Edit info: <strong>' . $person->fullname . '</strong>')?></h2>
</div>

<div class="modal-body person">
	<div class="person-popup">

	<?php $this->load->view('includes/view_alerts'); ?>

		<div class="control-group">
			<label class="control-label" for="form_fullname">Name (required)</label>
			<div class="controls">
				<?=form_input('fullname', ($new_person ? '' : $person->fullname), 'style="width: 356px" id="form_fullname"')?>
			</div>
		</div>
		
		<div class="control-group additional-info" <?php if ($new_person): ?> style="display: inherit"<?php endif; ?>>
			<label class="control-label" for="form_location">Location</label>
			<div class="controls">
				<?=form_input('location', ($new_person ? '' : $person->location), 'style="width: 356px" id="form_location"')?>
			</div>
		</div>
		
		<div class="control-group additional-info" <?php if ($new_person): ?> style="display: inherit"<?php endif; ?>>
			<label class="control-label" for="form_bio">Bio</label>
			<div class="controls">
				<?=form_input('bio', ($new_person ? '' : $person->bio), 'style="width: 356px" id="form_bio"')?>
			</div>
		</div>
		
		<div class="control-group additional-info" <?php if ($new_person): ?> style="display: inheritinherit"<?php endif; ?>>
			<label class="control-label" for="form_web">Website</label>
			<div class="controls">
				<?=form_input('web', ($new_person ? '' : $person->web), 'style="width: 356px" id="form_web"')?>
			</div>
		</div>
		
		<div class="control-group additional-info" <?php if ($new_person): ?> style="display: inherit"<?php endif; ?>>
			<label class="control-label" for="form_email">Email</label>
			<div class="controls">
				<?=form_input('email', ($new_person ? '' : $person->email), 'style="width: 356px" id="form_email"')?>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="form_twitter">Twitter</label>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">&nbsp;<big>@</big>&nbsp;</span>
					<?=form_input('twitter_username', ($new_person ? '' : $person->twitter_username), 'style="width: 323px" id="form_twitter" placeholder="paste link"')?>
 			
					<img src="<?=base_url()?>img/icon-ok.png" id="twitter-ok" class="field-ajax-status opacity-30">
					<img src="<?=base_url()?>img/icon-wrong.png" id="twitter-wrong" class="field-ajax-status opacity-30" />
					<img src="<?=base_url()?>img/ajax-loader.gif" id="twitter-ajax" class="field-ajax-status" />
				</div>
			</div>
		</div>

		<div class="control-group">
			<label class="control-label" for="form_linkedin">LinkedIn (public)</label>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">linkedin.com/</span>
					<?=form_input('linkedin_username', ($new_person ? '' : $person->linkedin_username), 'style="width: 260px" id="form_linkedin" placeholder="paste link"')?>

					<img src="<?=base_url()?>img/icon-ok.png" id="linkedin-ok" class="field-ajax-status opacity-30">
					<img src="<?=base_url()?>img/icon-wrong.png" id="linkedin-wrong" class="field-ajax-status opacity-30" />
					<img src="<?=base_url()?>img/ajax-loader.gif" id="linkedin-ajax" class="field-ajax-status" />
				</div>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="form_facebook">Facebook</label>
			<div class="controls">
				<div class="input-prepend">
					<span class="add-on">facebook.com/</span>
					<?=form_input('facebook_username', ($new_person ? '' : $person->facebook_username), 'style="width: 249px" id="form_facebook" placeholder="paste link"')?>

					<img src="<?=base_url()?>img/icon-ok.png" id="facebook-ok" class="field-ajax-status opacity-30">
					<img src="<?=base_url()?>img/icon-wrong.png" id="facebook-wrong" class="field-ajax-status opacity-30" />
					<img src="<?=base_url()?>img/ajax-loader.gif" id="facebook-ajax" class="field-ajax-status" />
				</div>
			</div>
		</div>
		
		<div class="control-group">
			<label class="control-label" for="form_reason">Reason to meet</label>
			<div class="controls">
				<textarea name="reason" rows="2" style="width: 356px" id="form_reason" placeholder='Why do you want to meet this person?'><?=($new_person ? '' : $person->reason)?></textarea>
			</div>
		</div>
		
	</div>
</div>

<div class="modal-footer">
	<span class="pull-left loading_person" style="display: none;"><img src="<?=base_url()?>img/ajax-loader.gif" style="padding: 9px 0 0 9px" /></span>
	<?php if ($new_person):?>
		<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Cancel</button>
	<?php else: ?>
		<a class="btn btn-large person-edit-ajax" href="<?=base_url() . 'i/iMeet/person/' . $person->id?>">Cancel</a>
	<?php endif; ?>
	<?=form_submit('person_submit',
				($new_person ? 'Add this person' : 'Save info'),
				'class="btn btn-large btn-primary"')?>
</div>
<?=form_close()?>

<script type='text/javascript'>
	window.BaseURL = '<?=base_url()?>';
</script>

<script src="<?=base_url()?>js/social-fields.js"></script>

<script type='text/javascript'>

	self.setInterval(function(){CheckFieldsChanges();}, 350);

	$(function() {
		$('.person-edit-ajax').click(function(e) {
			ajaxPersonClick(e, this, false);
		});

	});

</script>