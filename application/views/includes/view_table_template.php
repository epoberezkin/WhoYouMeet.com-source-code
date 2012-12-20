<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/includes/view_table_template.php
 *
 * Table of people to meet and users, main part of /i/iMeet, /i/meetMe and /Users webpages
 *---------------------------------------------------------------
 *
 * Variables passed:
 *  $list - list of people or users
 *
 */

?>

<table class="table table-hover">
	<thead>
		<tr>
			<th style="width: 34px">
			</th>

			<th style="width: 35%">
				Name
			</th>
			<th style="width: 120px">
				Contacts
			</th>
			
			<th style="width: 65%">
				<?php echo $last_column_heading; ?>
			</th>
		</tr>
	</thead>
	<tbody>

		<?php foreach ($list as $user_or_person): ?>
		<?php
			$item_url = base_url();
			if ($list_of_users) {
				$item_url = user_profile_url($user_or_person);
				$user_url = $item_url;
				$link_class = 'ajax';
			} else {
				$user_url = connected_user_profile_url($user_or_person);
				$item_url = base_url() . $list_item_path . $user_or_person->id;
				$link_class = 'person-ajax';
			}

		?>
			<tr style="height: 40px">
				<td class="<?=$link_class?>">
					<?php if ($user_or_person->picture_url): ?>
						<a href="<?php echo $item_url; ?>">
						<img src="<?php echo $user_or_person->picture_url; ?>" style="height: 32px; width: 32px" class="img-rounded small-img-rounded" style="margin-top: 3px" />
						</a>
					<?php endif; ?>
				</td>

				<td>
					<div style="height: 40px" class="ellipsis <?=$link_class?>">
						<a href="<?php echo $item_url; ?>"<?=($list_of_users? '' : ' data-toggle="modal"')?>><?=$user_or_person->fullname?></a>
					</div>
				</td>

				<?php /* Social/Contact icons */ ?>
				<td>
					<?php if ( $list_of_users || $user_or_person->usertomeetid): ?>
						<a href="<?php echo $user_url; ?>" class="no_hover_underline ajax">
							<img src="<?php echo base_url();?>img/icon-user.png" class="contact-icon" />
						</a>
					<?php else: ?>
						<img src="<?php echo base_url(); ?>img/icon-placeholder.png" class="contact-icon" />
					<?php endif; ?>
					<?php if ($user_or_person->email) : ?>
						<a href="mailto:<?php echo $user_or_person->email; ?>" class="no_hover_underline">
							<img src="<?php echo base_url();?>img/icon-envelope.png" class="contact-icon" />
						</a>
					<?php else: ?>
						<img src="<?php echo base_url(); ?>img/icon-placeholder.png" class="contact-icon" />
					<?php endif; ?>

					<?php if ($user_or_person->twitter_username) : ?>
						<a href="http://twitter.com/<?php echo $user_or_person->twitter_username; ?>" target="_blank" class="no_hover_underline">
							<img src="<?php echo base_url(); ?>img/icon-twitter.png" class="contact-icon" alt="<?php echo $user_or_person->fullname; ?>'s Twitter profile" />
						</a>
					<?php else: ?>
						<img src="<?php echo base_url();?>img/icon-placeholder.png" class="contact-icon" />
					<?php endif; ?>

					<?php if ($user_or_person->linkedin_username): ?>
						<a href="http://linkedin.com/<?php echo $user_or_person->linkedin_username; ?>" target="_blank" class="no_hover_underline">
							<img src="<?php echo base_url(); ?>img/icon-linkedin.png" class="contact-icon" alt="<?php echo $user_or_person->fullname; ?>'s Linkedin profile" />
						</a>
					<?php else : ?>
						<?php if ($user_or_person->twitter_username) : ?>
							<img src="<?php echo base_url(); ?>img/icon-placeholder.png" class="contact-icon" />
						<?php elseif ($user_or_person->facebook_username) : ?>
							<a href="http://facebook.com/<?=$user_or_person->facebook_username?>" target="_blank" class="no_hover_underline">
								<img src="<?php echo base_url(); ?>img/icon-facebook.png" class="contact-icon" alt="<?php echo $user_or_person->fullname; ?>'s Facebook profile" />
							</a>
						<?php endif; ?>
					<?php endif; ?>
				</td>

				<td>
					<div style="height: 40px" class="ellipsis"><?php echo my_auto_link($user_or_person->reason); ?></div>
				</td>

			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
