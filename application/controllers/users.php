<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/controllers/users.php
 *
 * "Users" controller class - main controller of the application
 *---------------------------------------------------------------
 *
 * Class functions:
 *
 *	1.	public function index()
 *		Redirects to home page
 *	2.	public function username()
 *		404 redirects here, shows public user profile by his twitter username
 *	3.	public function id($username = '')
 *		Shows public user profile by user id
 *	4.	public function social($social_network, $username = '')
 *		Shows public user profile by any social network username
 *	4a. public function twitter($username = '')
 *		Shows user profile by Twitter username
 *	4b. public function tw($username = '')
 *		Shows user profile by Twitter username - alias
 *	4c. public function linkedin()
 *		Shows user profile by Linkedin username
 *	4d. public function in()
 *		Shows user profile by Linkedin username - alias
 *	4e. public function facebook($username = '')
 *		Shows user profile by Facebook username
 *	4f. public function fb($username = ''
 *		Shows user profile by Facebook username - alias
 *
 *
 */

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main controller URL: application_domain/users
 */
class Users extends CI_Controller {


	/**
	 * 1. Redirects to home page - maybe should show the list of users instead
	 */
	public function index(){
		// Usernames have to be processed here later
		// maybe should show the list of users
		redirect('/');
	}


	/**
	 * 2. 404 redirects here, shows public user profile by his twitter username
	 * If there is no such user should show twitter page instead with invite button
	 */
	public function username() {
		$this->social('twitter', uri_string());
	}

	/**
	 * 3. Shows public user profile by user id
	 *    Also accepts twitter username
	 */
	public function id($user_str = '') {
		if ($user_str == '') {
			redirect('/');
		} else {
			$current_user_id = $this->session->userdata('id');
			$user_id = intval($user_str);
			$this->load->model('model_users');
			if ($user_id == $current_user_id) {
				// opening current user profile instead
				redirect(user_profile_url());
			} else {
				if ($user_id == 0) {
					// can be some string instead of number
					// trying to get user by twitter username
					$user = $this->model_users->get_any_user($current_user_id, $user_str, true);
				} else {
					$user = $this->model_users->get_any_user($current_user_id, $user_id);
				}

				if ($user) {
					$this->load->view('includes/view_template', array(
						'user' => $user,
						'content' => 'user', // loads 'view_user.php'
						'title' => (isset($user->reason) ? my_page_title('page_meetMe_user_title', $user->fullname) : my_page_title('page_user_title', $user->fullname)),
						'public' => true
					));
				} else {
					// no such user, maybe should redirect to the users list
					redirect('/');
				}

			}
		}
	}


	/**
	 * 4. Shows public user profile by any social network username
	 * @param type $social_network
	 * @param type $username
	 */
	public function social($social_network, $username = '') {
		if ($username) {
			$current_user_id = $this->session->userdata('id');
			$this->load->model('model_users');
			$user = $this->model_users->get_any_user($current_user_id, $username, true, $social_network);
			if ($user) {
				if ($user->id == $current_user_id) {
					$this->load->view('includes/view_template', array(
						'user' => $user,
						'content' => 'profile', // loads 'view_profile.php'
						'title' => my_page_title('page_myProfile_title')
					));

				} else {
					$this->load->view('includes/view_template', array(
						'user' => $user,
						'content' => 'user', // loads 'view_user.php'
						'title' => (isset($user->reason) ? my_page_title('page_meetMe_user_title', $user->fullname) : my_page_title('page_user_title', $user->fullname)),
						'public' => true
					));
				}
			} else {
				// no such user, maybe should show the user of the social network instead
				redirect('/');
			}
		} else {
			redirect('/');
		}
	}


	/**
	 * 4a. Shows user profile by Twitter username
	 */
	public function twitter($username = '') {
		$this->social('twitter', $username);
	}
	
	
	/**
	 * 4b. Shows user profile by Twitter username - alias
	 */
	public function tw($username = '') {
		$this->twitter($username);
	}

	
	/**
	 * 4c. Shows user profile by Linkedin username
	 */
	public function linkedin() {
		$username = str_replace('users/linkedin/', '', uri_string());
		$this->social('linkedin', $username);
	}


	/**
	 * 4d. Shows user profile by Linkedin username - alias
	 */
	public function in() {
		$username = str_replace('users/in/', '', uri_string());
		$this->social('linkedin', $username);
	}


	/**
	 * 4e. Shows user profile by Facebook username
	 */
	public function facebook($username = '') {
		$this->social('facebook', $username);
	}


	/**
	 * 4f. Shows user profile by Facebook username - alias
	 */
	public function fb($username = '') {
		$this->facebook($username);
	}


}


/* End of file /application/controllers/users.php */