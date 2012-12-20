<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/view_template.php
 * 
 * All webpages are loaded via this template
 *
 * Variables passed:
 *   $title - page title
 *   $content - part of the view name that has to be loaded. Also determines the active element of navbar
 *
 */

$cont = $this->input->post('cont');

if ($cont == '1' || $cont == 'popup') {
	// This is an ajax request to send only the content (without header, footer and navbar)
	// Makes navigation within application much faster
	/* echo $this->load->view('view_' . $content, true);*/
	
	echo $title, '_____', $content, '_____';
	echo $this->load->view('view_' . $content, true);

} else {

	$this->load->view('includes/view_header', array('title' => $title));
	$this->load->view('includes/view_navbar', array('content' => $content));
	$this->load->view('view_' . $content);
	$this->load->view('includes/view_footer');

}
/* End of file /application/views/view_template.php */