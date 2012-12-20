<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/views/view_home.php
 *
 * Home page
 *---------------------------------------------------------------
 *
 *
 */
?>

	<?php $this->load->view('includes/view_alerts'); ?>
	
	<div class="hero-unit">
		<?php if ( $this->session->userdata('logged_in') ): ?>
			<h1 style="margin-bottom: 45px">Hi,  <strong><?php echo $this->session->userdata('fullname'); ?></strong>!</h1>

			<big>
			
				<p style="margin-bottom: 30px">
					<span style="font-size: 40px; vertical-align: top">&#10112;</span>
					Organize the lists of people who <strong>You</strong> want to meet
				</p>

				<p style="margin-bottom: 45px">
					<a class="btn btn-large ajax" href="<?=base_url()?>i/iMeet" style="width: 220px"><strong>I</strong> want to meet</a>
				</p>

				<p style="margin-bottom: 30px">
					<span style="font-size: 40px; vertical-align: top">&#10113;</span>
					Manage those who wants to meet <strong>You</strong>.
					<a href="#how" data-toggle="modal">How it works?</a>
				</p>

				<p style="margin-bottom: 15px">
					<a class="btn btn-large ajax" href="<?=base_url()?>i/meetMe" style="width: 220px">Who wants to meet <strong>me</strong></a>
				</p>

			</big>
		<?php else: ?>
			<h1>Manage <strong>Who You Meet</strong>!</h1>
			<h2 style="margin-top: 30px;">It helps you to meet more people you need.</h2>

			<div style="float: right">
				<a href="#slides" data-toggle="modal">
					<p style="text-align: right">View slides</p>
					<p><img src="<?=base_url()?>img/BobAndJohn.png" class="img-polaroid" /></p>
				</a>
			</div>
			<div style="margin-bottom: 45px"></div>
			<big>
				<p style="margin-bottom: 30px">
					<span style="font-size: 40px; vertical-align: top">&#10112;</span>
					Organize the lists of people who <strong>You</strong> want to meet
				</p>
				<p style="margin-bottom: 30px">
					<span style="font-size: 40px; vertical-align: top">&#10113;</span>
					Manage those who wants to meet <strong>You</strong>.
					<a href="#how" data-toggle="modal">How it works?</a>
				</p>
				<p style="margin-bottom: 45px">
					<span style="font-size: 40px; vertical-align: top">&#10114;</span>
					Have more productive&nbsp;&amp;&nbsp;helpful meetings!
				</p>
			</big>

			<p style="margin-bottom: 45px">
				<a class="btn btn-large" href="<?=base_url()?>login/twitter_redirect">
						<img src="<?=base_url()?>img/icon-twitter-color.png" class="contact-icon-login"/>
						<span style="vertical-align: middle">&nbsp;Sign in with Twitter</span>
				</a>
				&nbsp;
				<a class="btn btn-large" href="<?=base_url()?>login/linkedin_redirect">
						<img src="<?=base_url()?>img/icon-linkedin-color.png" class="contact-icon-login"/>
						<span style="vertical-align: middle">&nbsp;Sign in with LinkedIn</span>
				</a>
				&nbsp;
				<a class="btn btn-large" href="<?=base_url()?>login/facebook_redirect">
						<img src="<?=base_url()?>img/icon-facebook-color.png" class="contact-icon-login"/>
						<span style="vertical-align: middle">&nbsp;Sign in with Facebook</span>
				</a>
			</p>
			<p class="ajax">
				<a href="<?=base_url()?>signup">Join Who You Meet</a> or <a href="<?=base_url()?>login">Sign in</a> with email and password.
			</p>

		<?php endif; ?>

		<!-- Modal - how it works -->
		<div id="how" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h2 id="myModalLabel">How it works?</h2>
		  </div>
		  <div class="modal-body">
			<h3>
				<span style="font-size: 40px; vertical-align: top">&#10112;</span>
					Who <strong>You</strong> want to meet
			</h3>
				<p>You add people to your list by giving us their Twitter, LinkedIn or Facebook profiles, they don't have to be our users.</p>
				<p>Just copy/paste one link and we add all the details.</p>
			<h3>
				<span style="font-size: 40px; vertical-align: top">&#10113;</span>
					Who wants to meet <strong>You</strong>
			</h3>
				<p>When you sign up with Twitter, LinkedIn or Facebook (or connect them later), you will see all people who want to meet you, together with the reason they want to meet you and their contact details.</p>
				<p>Only you can see who wants to meet you.</p>
		  </div>
		  <div class="modal-footer">
			<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
		  </div>
		</div>

		<?php if ( ! $this->session->userdata('logged_in') ): ?>
		<!-- Modal - embedded presentation -->
		<div id="slides" class="modal hide" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width: 512px">
		  <div class="modal-body">
			  <div style="text-align: center"><iframe src="http://www.slideshare.net/slideshow/embed_code/15449639" width="476" height="390" frameborder="0" marginwidth="0" marginheight="0" scrolling="no" style="border:1px solid #CCC;border-width:1px 1px 0" allowfullscreen webkitallowfullscreen mozallowfullscreen> </iframe></div>
		  </div>
		  <div class="modal-footer">
			<div class="pull-left">
				<div class="fb-like pull-left" style="margin-right:5px" data-href="http://www.slideshare.net/WhoYouMeet/who-you-meet-presentation-15449639" data-send="false" data-layout="button_count" data-width="450" data-show-faces="true"></div>
				<span class="pull-left">
				<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://www.slideshare.net/WhoYouMeet/who-you-meet-presentation-15449639" data-text="Presentation of @WhoYouMeet on @slideshare">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></span>
			</div>
			<button class="btn btn-large" data-dismiss="modal" aria-hidden="true">Close</button>
		  </div>
		</div>
		<?php endif; ?>

	</div>

	<p>Thank you for inviting your friends!</p>
	<p><?php $this->load->view('includes/view_share_buttons'); ?></p>

