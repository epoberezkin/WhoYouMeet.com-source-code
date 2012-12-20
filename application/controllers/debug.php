<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/controllers/debug.php
 *
 * Debug controller class
 *---------------------------------------------------------------
 *
 *
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');


/**
 * Shows session data, path: /debug
 */
class Debug extends CI_Controller {


	public function index() { // debug
		if ( ENVIRONMENT == 'production' ) {
			redirect('/');
		} else {
			$this->load->view('includes/view_template',
				array(
					'content' => 'debug',  // view_debug.php is loaded
					'title' => 'WYM: Debug'
				));
		}
	}

}

/* End of file /application/controllers/login.php */