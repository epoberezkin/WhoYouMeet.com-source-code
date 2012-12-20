<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/view_person.php
 * 
 * Main part of /i/iMeet/person webpage
 *
 * Variable passed:
 *   $person - dbPersonToMeet object with person information
 *
 */
?> 
<div class="modal-body person">
	<div class="person-popup">

		<?php $this->load->view('includes/view_alerts'); ?>

		<?php /*

		<p class="ajax"><a href="<?=base_url()?>i/iMeet" class="no-hover-underline">
		<img src="<?=base_url()?>img/icon-left.png" class="contact-icon-page" /></a>
		<a href="<?=base_url()?>i/iMeet"><span style="vertical-align: middle">Back to the list of people <strong>I want to meet</strong>
		</a></p>

		 */
		?>

		<?php $this->load->view('includes/view_person_template', array(
			'person' => $person,
			'current_user' => false,
			'show_user' => false,
		)); ?>

		<div style="height: 5px"></div>

		<h4>My reason to meet <strong><?=$person->fullname?></strong>:</h4>
		<?php if ($person->reason): ?>
			<p style="max-width: 600px"><?php echo show_line_breaks(my_auto_link($person->reason)); ?></p>
		<?php else: ?>
			<p><em>Not specified.</em></p>
		<?php endif; ?>

<!-- Modal delete confirmation -->
<div id="ConfirmDelete" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		<h2 id="myModalLabel">Delete <strong><?=$person->fullname?>?</strong></h2>
	</div>
	<div class="modal-body">
		<p>Confirm that you want to delete <strong><?=$person->fullname?></strong> from your list.</p>
		<p>It cannot be undone!</p>
	</div>
	<div class="modal-footer">
		<a id="cancel-delete-person" href="#" class="btn btn-large">Cancel</a>
		<a class="btn btn-primary btn-large" href="<?=base_url()?>i/iMeet/delete/<?=$person->id?>">Delete</a>
	</div>
</div>


	</div>
</div>
<div class="modal-footer">
	<span class="pull-left loading_person" style="display: none;"><img src="<?=base_url()?>img/ajax-loader.gif" style="padding: 9px 0 0 9px" /></span>
	<a id="delete-person" href="#" data-toggle="modal">Delete</a>&nbsp;
	<a id="edit-person-ajax" class="btn btn-large" href='<?=base_url()?>i/iMeet/edit/<?=$person->id?>'>Edit</a>
	<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
</div>

<script type='text/javascript'>

	popup_HTML = '';

	$(function() {
		$('#edit-person-ajax').click(function(e) {
			ajaxPersonClick(e, this, false);
		});


		$('#delete-person').click(function(e) {
			clickDeletePerson(e);
		});


		function clickDeletePerson(e) {
			popup_HTML = $('#show_person').html();
			$('#show_person').html($('#ConfirmDelete').html());
			$('#cancel-delete-person').click(function(e) {
				$('#show_person').html(popup_HTML);
				$('#delete-person').click(function(e) {
					clickDeletePerson(e);
				});
				$('#edit-person-ajax').click(function(e) {
					ajaxPersonClick(e, this, false);
				});
				e.preventDefault();
			});
			e.preventDefault();
		}

	});

</script>