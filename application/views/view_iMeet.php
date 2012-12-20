<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_iMeet.php
 * 
 * List of people user wants to meet - main part of /i/iMeet webpage
 *---------------------------------------------------------------
 *
 * Variables passed:
 *  $list - list of people user wants to meet
 *
 */
?>
	<?php if ( ! isset($person)) { $this->load->view('includes/view_alerts'); } ?>

	<h2 id="iMeet_page">People who <strong>I</strong> want to meet</h2>

	<p><a id="add-person-form" "href="#" data-toggle="modal" class="btn btn-info"><i class="icon-plus icon-white"></i><i class="icon-user icon-white"></i></a></p>

	<?php if ( $list ) : ?>
		<?php $this->load->view('includes/view_table_template',
				array(
					'list_item_path' => 'i/iMeet/person/',
					'last_column_heading' => 'Reason to meet person',
					'list_of_users' => false
					)
				); ?>
	<?php else: ?>
		<p>The list of people I want to meet is empty.</p>
		<p>Why not <a href="#add_person" data-toggle="modal">add somebody I want to meet</a>?</p>
	<?php endif; ?>
	
	<!-- Empty modal form to show a person -->
	<div id="empty_show_person" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 560px">
		<div class="modal-body person">
			<div class="person-popup" rel="empty"></div>
		</div>
		<div class="modal-footer">
			<span class="pull-left loading_person" style="display: none;"><img src="<?=base_url()?>img/ajax-loader.gif" style="padding: 9px 0 0 9px" /></span>
			<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
		</div>
	</div>

	<!-- Modal form to show a person -->
	<div id="show_person" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 560px">
		<?php /* ajax content is loaded here */ ?>

		<?php
			if (isset($person)) {
				if ($popup_content == 'person') {
					echo $this->load->view('view_person', array(
						'person' => $person,
					), true);
				} elseif ($popup_content == 'iMeet_form') {
					echo $this->load->view('view_iMeet_form', array(
						'person' => $person,
						'new_person' => $new_person,
					), true);
				}
			} else {
		?>
			<div id="person-popup-empty" class="modal-body person">
				<div class="person-popup"></div>
			</div>
			<div class="modal-footer">
				<span class="pull-left loading_person" style="display: none;"><img src="<?=base_url()?>img/ajax-loader.gif" style="padding: 9px 0 0 9px" /></span>
				<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
			</div>
		<?php } /* end of else */ ?>
	</div>


	<!-- Modal form to add a person -->
	<div id="add_person" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 492px">
		
		<?=form_open('i/iMeet/add')?>

		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Add person I want to meet</h3>
		</div>
		<div class="modal-body">

			<div class="control-group">
				<label class="control-label" for="form_twitter">Enter <strong>Twitter</strong> profile</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">&nbsp;<big>@</big>&nbsp;</span>
						<?=form_input('twitter_username', '', 'style="width: 448px" id="form_twitter" placeholder="paste link"')?>

						<img src="<?=base_url()?>img/icon-ok.png" id="twitter-ok" class="field-ajax-status opacity-30">
						<img src="<?=base_url()?>img/icon-wrong.png" id="twitter-wrong" class="field-ajax-status opacity-30" />
						<img src="<?=base_url()?>img/ajax-loader.gif" id="twitter-ajax" class="field-ajax-status" />
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="form_linkedin">... or public Linkedin profile</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">linkedin.com/</span>
						<?=form_input('linkedin_username', '', 'style="width: 385px" id="form_linkedin" placeholder="paste link"')?>

						<img src="<?=base_url()?>img/icon-ok.png" id="linkedin-ok" class="field-ajax-status opacity-30">
						<img src="<?=base_url()?>img/icon-wrong.png" id="linkedin-wrong" class="field-ajax-status opacity-30" />
						<img src="<?=base_url()?>img/ajax-loader.gif" id="linkedin-ajax" class="field-ajax-status" />
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="form_facebook">... or Facebook profile</label>
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">facebook.com/</span>
						<?=form_input('facebook_username', '', 'style="width: 374px" id="form_facebook" placeholder="paste link"')?>

						<img src="<?=base_url()?>img/icon-ok.png" id="facebook-ok" class="field-ajax-status opacity-30">
						<img src="<?=base_url()?>img/icon-wrong.png" id="facebook-wrong" class="field-ajax-status opacity-30" />
						<img src="<?=base_url()?>img/ajax-loader.gif" id="facebook-ajax" class="field-ajax-status" />
					</div>
				</div>
			</div>

			<div class="control-group">
				<label class="control-label" for="form_reason">... and the reason to meet (optional)</label>
				<div class="controls">
					<textarea name="reason" rows="4" style="width: 481px" id="form_reason" placeholder='Why do you want to meet this person?'></textarea>
				</div>
			</div>

			<div class="pull-right">
				... and we add all the details!
			</div>

		</div>
		<div class="modal-footer">
			<span class="pull-left loading_person" style="display: none;"><img src="<?=base_url()?>img/ajax-loader.gif" style="padding: 9px 0 0 9px" /></span>
			<span class="pull-left"><a id="new-person-ajax" href="<?=base_url()?>i/iMeet/new">Manually add information</a></span>
			<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Cancel</button>

			<?=form_submit('add_person_submit',
				'Add person',
				'class="btn btn-large btn-primary disabled" id="add_button" disabled="disabled"')?>

		</div>

		<?=form_close()?>

	</div>

<script type='text/javascript'>
	window.BaseURL = '<?=base_url()?>';
</script>

<script src="<?=base_url()?>js/social-fields.js"></script>

<script type='text/javascript'>
	self.setInterval(function(){CheckFieldsChanges();}, 350);


	$('#add_person').on('hidden', function () {
		$('#form_twitter').val('');
		$('#form_linkedin').val('');
		$('#form_facebook').val('');
		$('#form_reason').val('');
	})
	
	var iMeet_page = true;
</script>