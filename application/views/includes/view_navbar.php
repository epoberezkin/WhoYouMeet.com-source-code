<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/includes/view_navbar.php
 *
 * Navigation menu on top of the page. Included in every page after the header before the main part of the page.
 *
 * Variables passed:
 *   $content - main part of the page based on which navbar highlights the active menu item
 *
 */

$user_full_name = $this->session->userdata('fullname');

?>

<nav <?=( $this->session->userdata('logged_in') || $content != 'home' ? '' : 'style="display: none"')?>>
	<div class="navbar navbar-fixed-top"> <!-- navbar-fixed-top-->
		<div class="navbar-inner">
			<a class="brand ajax" href="<?=base_url()?>i/home">
				<img src="<?=base_url()?>img/logo.png" />
				Who You Meet<?php echo (ENVIRONMENT == 'production' ? '' : ' ('.ENVIRONMENT.')'); ?>
			</a>
			<ul class="nav">
				<?php if ( $this->session->userdata('logged_in') ): ?>
					<li class="ajax content_iMeet<?=($content == 'iMeet' ? ' active' : '')?>">
						<a href="<?=base_url()?>i/iMeet"><big><strong>I</strong> want to meet</big></a>
					</li>
					<li class="ajax content_meetMe<?=($content == 'meetMe' ? ' active' : '')?>">
						<a href="<?=base_url()?>i/meetMe"><big>Who wants to meet <strong>me</strong></big></a>
					</li>
<?php /*
					<li class="dropdown-open">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<big>My lists</big> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li>
								<a href="<?=base_url()?>i/meetMe">Who wants to meet <strong>me</strong></a>
							</li>
							<li>
								<a href="<?=base_url()?>i/meetMe">Angel investors in US</a>
							</li>
							<li class="divider"></li>
							<li><a href="<?php echo base_url(); ?>debug">All lists...</a>
							<li><a href="<?php echo base_url(); ?>debug">Add list</a>
						</ul>
					</li>
*/ ?>
					<img src="<?=base_url()?>img/ajax-loader.gif" id="loading" style="display: none; padding: 9px 0 0 9px" />
			</ul>
			<ul class="nav pull-right">
					<li class="dropdown-open content_profile content_settings<?=($content == 'profile' || $content == 'settings' ? ' active' : '')?>">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<big><strong><?=$user_full_name?></strong></big> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li class="ajax"><a href="<?=user_profile_url()?>">
								<?php if ($this->session->userdata('picture_url')): ?>
									<span style="float: left; padding-right: 10px; height: 40px; width: 40px">
										<img src="<?=$this->session->userdata('picture_url')?>" style="height: 40px; width: 40px" class="img-rounded small-img-rounded" />
									</span>
								<?php endif; ?>
								<span>
									<?=$user_full_name?><br />
									<small>My profile</small>
								</span>
							</a></li>		
							<li class="ajax"><a href="<?=base_url()?>i/profile/edit">Edit my profile</a></li>
							<li class="divider"></li>
							<li><a href="<?=base_url()?>login/logout">Sign out</a></li>
                        </ul>
					</li>

				<?php else: ?>
					<img src="<?=base_url()?>img/ajax-loader.gif" id="loading" style="display: none; padding: 9px 0 0 9px" />
			</ul>
			<ul class="nav pull-right">
					<li class="pull-right ajax content_signup<?=($content == 'signup' ? ' active' : '')?>">
						<a href="<?=base_url()?>signup"><big>Join Who You Meet</big></a>
					</li>
					<li class="ajax content_login<?=($content == 'login' ? ' active' : '')?>">
						<a href="<?=base_url()?>login"><big>Sign in</big></a>
					</li>
				<?php endif; ?>

				<?php if (ENVIRONMENT != 'production'): ?>
					<li class="dropdown-open">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">
							<?php echo ENVIRONMENT; ?> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu">
							<li><a href="http://localhost:8888/WhoYouMeet/<?=uri_string()?>">local</a></li>
							<li><a href="http://dev.whoyoumeet.com/<?=uri_string()?>">dev</a></li>
							<li><a href="http://beta.whoyoumeet.com/<?=uri_string()?>">beta</a></li>
							<li><a href="http://whoyoumeet.com/<?=uri_string()?>">production</a></li>
							<li class="divider"></li>
							<li class="ajax"><a href="<?php echo base_url(); ?>debug">session</a></li>
						</ul>
					</li>
				<?php endif; ?>

			</ul>
		</div> <!-- /.navbar-inner -->
	</div> <!-- /.navbar -->
</nav>

<script>
// Makes loading faster via ajax (only main content loads)
	var FirstTime = true;
	
	$(function() {
		$('.ajax a, a.ajax').live('click', function(e) {
			href = $(this).attr('href');
			iMeet_page = false;
			history.pushState('', '', href);
			
			<?php /* if pushState is not supported, function will not continue */ ?>
			$('#loading').show();
			
			loadContent(href);

			e.preventDefault();
		});

		window.onpopstate = function(event) {
			if (FirstTime) {
				FirstTime = false;
			} else {
				$('#loading').show();
				loadContent(location.pathname);
			}
		};
		
		iMeet_page_init();

	});

   
	function loadContent(path){
		$.ajax({
			url: path,
			type: 'POST',
			data: {cont: '1'},
			success: function(response) {			
				// Splits response to title, content name and actual content
				data = response.split('_____');
				try {
					$('.modal').modal('hide');
				} catch(err) {}
				
				try {
					$('.container').html(data[2]);
				} catch(err) {}

				$(document).attr('title', data[0]);
				// Show active menu in bar
				$('nav li').removeClass('active');
				$('nav li.content_' + data[1]).addClass('active');

				<?php if ( ! $this->session->userdata('logged_in')): ?>
				if (data[1] == 'home') {
					$('nav').hide();
				} else {
					$('nav').show();
				}
				<?php endif; ?>
				$('#loading').hide();
				if ('ShareButtons' in window) {
					RestoreShareButtons();
				} else if ('twttr' in window) {
					twttr.widgets.load();
				}

				console.log(data[1] + '_page_init');
				init_func = window[data[1] + '_page_init'];
				if (typeof init_func === 'function') {
					init_func();
 				}
			}
		});
	}

	var iMeetPageTitle = "<?=my_page_title('page_iMeet_title')?>";


	function iMeet_page_init() {
		iMeetPageTitle = "<?=my_page_title('page_iMeet_title')?>";
		iMeet_page = true;

		if ($('#person-popup-empty').length == 0) {
			$('#show_person').modal('show');
		}

		$('.person-ajax a, a.person-ajax').click(function(e) {
			ajaxPersonClick(e, this, true);
		});

		$('#add-person-form').click(function(e) {
			href = $(this).attr('href');

			$('#show_person').html($('#add_person').html());
			$('#show_person').modal('show');
			$('#new-person-ajax').click(function(e) {
				$('#new-person-ajax').hide();
				ajaxPersonClick(e, this, false);
			});
			e.preventDefault();
		});

		$('#show_person').on('hidden', function () {
			onClosePopup();
		})
	}


	function onClosePopup() {
		$('#show_person').html($('#empty_show_person').html());

		if (iMeet_page) {
			try {
				history.pushState('', '', '/i/iMeet');
				$(document).attr('title', iMeetPageTitle);
			} catch(err) {
				window.location.href = '/i/iMeet';
			}
		}
	}


	function ajaxPersonClick(e, link, showPopup) {
		href = $(link).attr('href');
		history.pushState('', '', href);

		<?php /*
				If pushState is not supported (pre-HTML5 browser, e.g. IE9),
				function will not continue, the link will be just opened.
		*/ ?>
		if (showPopup) {
			$('#show_person').modal('show');
		}
		$('.loading_person').show();
		loadPerson(href);

		e.preventDefault();
	}

	function loadPerson(path){
		$.ajax({
			url: path,
			type: 'POST',
			data: {cont: 'popup'},
			success: function(response) {
				// Splits response to title, content name and actual content
				data = response.split('_____');
				$(document).attr('title', data[0]);

				$('#show_person').html(data[2]);

				$('.loading_person').hide();

				if ('twttr' in window) {
					twttr.widgets.load();
				}
			}
		});
	}

</script>


<div class="container">