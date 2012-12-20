<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/includes/view_footer.php
 *
 * Footer. Included last in every page of the application
 *
 */
?>

</div> <!-- .container -->

	<!-- Google analytics code -->
	<script type="text/javascript">

	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', 'UA-36397991-2']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();

	</script>

	<script type="text/javascript">
		$('body').on('touchstart.dropdown', '.dropdown-menu', function (e) { e.stopPropagation() })
	</script>
	
<script type='text/javascript'>

var _ues = {
host:'whoyoumeet.userecho.com',
forum:'16116',
lang:'en',
tab_icon_show:false,
tab_corner_radius:5,
tab_font_size:20,
tab_image_hash:'ZmVlZGJhY2sgJiBoZWxw',
tab_chat_hash:'Y2hhdA%3D%3D',
tab_alignment:'right',
tab_text_color:'#E6F5FF',
tab_text_shadow_color:'#00000055',
tab_bg_color:'#4CA5E0',
tab_hover_color:'#52B2F2',
tab_top:'75%'
};

(function() {
    var _ue = document.createElement('script'); _ue.type = 'text/javascript'; _ue.async = true;
    _ue.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.userecho.com/js/widget-1.4.gz.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(_ue, s);
  })();

</script>

</body>
</html>
