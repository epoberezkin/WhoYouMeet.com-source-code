<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/includes/view_share_buttons.php
 *
 * Social sharing buttons. Currently included on home page and on Who wants to meet me page
 *
 */

$this->load->language('messages');

?>

<span style="float: left; margin-right: 5px;">
	<a class="btn btn-mini"
	   href="mailto:?subject=<?=rawurldecode(my_lang('share_button_email_subject'))?>&body=<?=str_replace('_', '%0D%0A', rawurldecode(my_lang('share_button_email_body'))) . ($this->session->userdata('logged_in') ? '%0D%0A%0D%0A' . $this->session->userdata('fullname') : '')?>">
		 <i class="icon-envelope vert-sub"></i> Email
	</a>
</span>

<span style="float: left; margin-right: 5px">
	<a href="https://twitter.com/share" class="twitter-share-button" data-url="http://WhoYouMeet.com" data-text="<?=rawurldecode(my_lang('share_button_tweet_text'))?>" data-via="WhoYouMeet" data-count="none" data-dnt="true">Tweet</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
</span>

<span style="float: left; margin-right: 5px">
<script src="//platform.linkedin.com/in.js" type="text/javascript"></script>
<span id="LinkeinIn_button"><script type="IN/Share" data-url="http://WhoYouMeet.com"></script></span>
</span>

<div id="Facebook_buttons">
<div class="fb-like" data-href="https://www.facebook.com/pages/Who-You-Meet-Community/495531867144160" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-font="verdana"></div>
</div>

<!-- Facebook plugin for like button -->

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=159912110821363";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<script type="text/javascript">
	var ShareButtons = true;
	var fb_buttons = '<div class="fb-like" style="vertical-align: 4px; margin-right: 5px; data-href="https://www.facebook.com/pages/Who-You-Meet-Community/495531867144160" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false" data-font="verdana"></div>';
	var in_button = '<script type="IN/Share" data-url="http://WhoYouMeet.com">';

	function RestoreShareButtons() {
		if ('twttr' in window) {
			twttr.widgets.load();
		}
		if ('IN' in window) {
			$('#LinkeinIn_button').html(in_button);
			IN.parse($('#LinkeinIn_button').get(0));
		}
		if ('FB' in window) {
			$('#Facebook_buttons').html(fb_buttons);
			FB.XFBML.parse($('#Facebook_buttons').get(0));
		}
	}
</script>