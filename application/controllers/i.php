<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/controllers/i.php
 * 
 * "I" controller class - main controller of the application
 *---------------------------------------------------------------
 *
 * Class functions:
 *  1. public function index() - opens home page
 *  2. public function home() - loads home page (view_home.php via view_template.php)
 *  3. public function iMeet($action, $param) - actions with with the list of people user wants to meet
 *  4. private function iMeet_form($person, $new) - loads form to add or edit a person user wants to meet
 *  5. private function iMeet_person_from_form($user_id, $person_id) - prepares for validation and copies form
 *  5a.function facebook_validation($fb) - callback that checks if Facebook profile exists
 *  5b.function twitter_validation($tw) - callback that checks if Twitter profile exists
 *  5c.function linkedin_validation($in) - callback that checks if LinkedIn profile exists
 *  5d.public function check_social ($social_network) - processes Ajax requests - checks if twitter, linkedin or facebook user exists
 *	5e.private function choose_best_person_picture(&$person) - chooses best profile picture
 *  7. public function meetMe() - list of people who want to meet user - not implemented yet
 *  8. public function profile($action) - actions with user profile
 *  8a.function validate_email($email = '', $old_email = '') - callback that validates email after profile editing
 *  8b.private function choose_best_profile_picture(&$user) - chooses best profile picture
 *  9. public function profile_facebook() - redirect point for facebook profile connection
 *  9a.public function profile_twitter() - redirect point for twitter profile connection
 *  9b.public function profile_linkedin() - redirect point for linkedin profile connection
 * 10. private function settings($user) - shows form to edit user profile (view_settings.php via view_template.php)
 * 11. private function resend_verification_email($user, $key) - sends verification email to user
 * 12. private function change_password_form($user) - shows form to change user password
 *
 */
 
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Main controller URL: application_domain/i
 */
class I extends CI_Controller {


	/**
	 * 1. Opens home page
	 */
	public function index(){
		$this->home();
	}


	/**
	 * 2. Loads home page (view_home.php via view_template.php)
	 */
	public function home(){ // loads view_home via template
		$this->load->view('includes/view_template', array(
			'content' => 'home', // loads 'view_home.php'
			'title' => lang('page_home_title')
		));
	}


	/**
	 * 3. Actions with the list of people user wants to meet (via model_imeet.php)
	 *    Security of this function relies only on user id stored in the cookie: $this->session->userdata('id').
	 *    The seesion cookie should be encrypted via /application/config/config.php (it is not at the moment)
	 *    
	 *    Parameters $action and $param are passed via request URL: /i/iMeet/$action/$param
	 *    $action values:
	 *      '' - shows the list of people (not users) the current user wants to meet
	 *      'new' - full form to add new person
	 *      'add' - validate and add new person to table 'peopletomeet' when added from popup via model_imeet.php
	 *		'add_form' - validate and add new person when addded from full form
	 *      'add_user' - copy a user to a person record when adding from any user profile page
	 *      'edit' - form to edit person details. $param - person id
	 *      'update' - validate after editing and update person. $param - person id
	 *      'delete' - delete person from the table. $param - person id
	 *
	 *    All actions can be performed only if the person is in the table of current user.
	 *    It is checked by matching user id stored in the session cookie and userid field in 'peopletomeet' table.
	 *    If the session cookie is not encrypted (or session stored in the database), the security is compromised.
	 */
	public function iMeet($action = '', $param = ''){ // loads view_iMeet via template

		if ( $this->session->userdata('logged_in') ) {
			$this->load->model('model_imeet');
			$user_id = $this->session->userdata('id');
			switch ($action) {
			
			case '': // list of people I (user) want to meet
				$list = $this->model_imeet->get_iMeet_list($user_id);
				$this->load->view('includes/view_template',
					array(
						'list' => $list,
						'content' => 'iMeet', // loads 'view_iMeet.php'
						'title' => my_page_title('page_iMeet_title') // defined in MY_language_helper
					));
				break;
				
			case 'person': // shows one person users wants to meet
				if ( $this->session->userdata('logged_in') ) {
					$person = $this->model_imeet->get_personToMeet($user_id, $param);
					if ($person) {
						$this->load->model('model_users');
						if ($person->usertomeetid) {
							// checking if this person connected to a user who wants to meet me (current user)
							$user_to_meet = $this->model_users->get_meetMe_user($user_id, $person->usertomeetid);
							$meet_me = $user_to_meet && $user_to_meet->id == $person->usertomeetid;
						} else {
							$meet_me = false;
						}

						// checking if it is an ajax request from iMeet page
						$cont = $this->input->post('cont');
						if ($cont == 'popup') {
							// ajax request from page to show person
							$this->load->view('includes/view_template', array(
								'person' => $person,
								'meet_me' => $meet_me,
								'content' => 'person', // loads 'view_person.php'
								'title' => my_page_title('page_iMeet_person_title', $person->fullname)
							));
						} else {
							// not popup ajax - showing person on top of "I want to meet" list now
							$list = $this->model_imeet->get_iMeet_list($user_id);
							$this->load->view('includes/view_template',
								array(
									'list' => $list,
									'person' => $person,
									'meet_me' => $meet_me,
									'content' => 'iMeet', // loads 'view_iMeet.php'
									'title' => my_page_title('page_iMeet_person_title', $person->fullname),
									'popup_content' => 'person'
								));
						}
					} else {
						redirect('/i/iMeet');
					}
				} else {
					redirect('/');
				}
				break;
				
			case 'new': // form to add a new person to meet
				$this->iMeet_form();
				break;

			case 'add': // adds user from popup, fields validated via Ajax, but we validate them just in case

				$this->form_validation->set_rules('linkedin_username', 'LinkedIn Profile', 'trim|xss_clean');
				$this->form_validation->set_rules('twitter_username', 'Twitter Profile', 'trim|xss_clean');
				$this->form_validation->set_rules('reason', 'Reason to meet', 'trim|xss_clean');

				if ($this->form_validation->run()) {

					$new_person = new dbPersonToMeet;
					set_all_info_from_social($new_person, 'twitter', $this->input->post('twitter_username'), true);
					set_all_info_from_social($new_person, 'linkedin', $this->input->post('linkedin_username'), true);
					set_all_info_from_social($new_person, 'facebook', $this->input->post('facebook_username'), true);
					$this->choose_best_person_picture($new_person, true);

					$new_person->reason = $this->input->post('reason');

					if ($new_person->fullname) {
						// adding person to meet
						$person_id = $this->model_imeet->add_personToMeet($user_id, $new_person);

						if ( $person_id ) {
							redirect('/i/iMeet/person/' . $person_id);
						} else {
							// could not add person
							$this->session->set_flashdata('error', 'Oops. We could not add person. Please try again.');
							redirect('/i/iMeet');
						}
					} else {
						// could not retirive information from twitter and linkedin
						$this->session->set_flashdata('alert', 'We could not retrieve information about the person. Please try again.');
						redirect('/i/iMeet');
					}

				} else {
					// did not validate form, validation errors will be shown
					$this->iMeet();
				}
				break;

			case 'add_form': // validate and add a new person to meet
				$new_person = new dbPersonToMeet;
				$this->iMeet_person_from_form($user_id, $new_person);
				if ( $this->form_validation->run() ) {

					set_all_info_from_social($new_person, 'twitter', $this->input->post('twitter_username'), true);
					set_all_info_from_social($new_person, 'linkedin', $this->input->post('linkedin_username'), true);
					set_all_info_from_social($new_person, 'facebook', $this->input->post('facebook_username'), true);
					$this->choose_best_person_picture($new_person, true);

					// adding person to meet
					$ok = $this->model_imeet->add_personToMeet($user_id, $new_person);

					if ( $ok ) {
						redirect('/i/iMeet');
					} else {
						// could not add person
						$this->session->set_flashdata('error', 'Oops. We could not add person. Please try again.');
						$this->iMeet_form($new_person);
					}
				} else {
					// did not validate form, validation errors will be shown
					$this->iMeet_form($new_person);
				}
				break;

			case 'add_user': // validate and add a person to meet by copying user
				$this->session->set_userdata('test', 'we are here 4');
				$this->form_validation->set_rules('reason', lang('form_iMeet_reason_field'), 'trim|xss_clean');
				$previous_page = $this->input->server('HTTP_REFERER');
				if ( $this->form_validation->run() ) {
					$user_to_meet_id = $this->input->post('user_to_meet_id');
					$this->load->model('model_users');
					$user_to_meet = $this->model_users->get_any_user($user_id, $user_to_meet_id);
					if ($user_to_meet) {
						$new_person = new dbPersonToMeet;
						$new_person->copy_from_user($user_to_meet);
						$new_person->reason = $this->input->post('reason');
						$new_person->usertomeetid = $user_to_meet_id;

						$ok = $this->model_imeet->add_personToMeet($user_id, $new_person);
						if ( $ok ) {
							redirect('/i/iMeet');
						} else {
							// could not add person
							$this->session->set_flashdata('error', 'Oops. We could not add person. Please try again.');
							redirect($previous_page);
						}
					} else {
						// could not retrieve user
						$this->session->set_flashdata('error', 'Oops. We could not add person. Please try again.');
						redirect($previous_page);
					}
				} else {
					// did not validate form, validation errors will be shown
					redirect($previous_page);
				}
				break;

			case 'edit': // edit person I want to meet
				$person = $this->model_imeet->get_personToMeet($user_id, $param);
				if ($person) {
					$this->iMeet_form($person, false);
				} else {
					// No such person, wrong id was passed
					redirect('/i/iMeet');
				}
				break;

			case 'update': // validate and update person after editing
				$old_person = $this->model_imeet->get_personToMeet($user_id, $param);
				if ($old_person) {
					$person = new dbPersonToMeet;
					$person->copy($old_person);
					$this->iMeet_person_from_form($user_id, $person, $param);

					if ( $this->form_validation->run() ) {

						set_all_info_from_social($person, 'twitter', $this->input->post('twitter_username'));
						set_all_info_from_social($person, 'linkedin', $this->input->post('linkedin_username'));
						set_all_info_from_social($person, 'facebook', $this->input->post('facebook_username'));
						$this->choose_best_person_picture($person);

						// updating person to meet
						$ok = $this->model_imeet->update_personToMeet($user_id, $param, $person);

						if ( $ok ) {
							redirect('/i/iMeet/person/' . $param);
						} else {
							// can't update
							$this->session->set_flasdata('error', 'Oops. We could not update person. Please try again.');
							$this->iMeet_form($person, false);
						}
					} else {
						// can't validate - validation error will be shown;
						$this->iMeet_form($person, false);
					}
				} else {
					// No such person, wrong id was passed
					redirect('/i/iMeet');
				}
				break;

			case 'delete': // delete person I want to meet
				$this->model_imeet->delete_personToMeet($user_id, $param);
				redirect('/i/iMeet');
				break;

			default:
				redirect('/i/iMeet');
			}
		} else redirect('/');
	}
	

	/**
	 * 4. Loads form to add or edit a person user wants to meet
	 *    $person - person information (dbPersonToMeet object, defined in model_imett.php)
	 *    $new - TRUE to add, FALSE to edit
	 */
	private function iMeet_form($person = '', $new = true) {
		$this->load->model('model_imeet');
		$user_id = $this->session->userdata('id');
		$cont = $this->input->post('cont');
		if ($cont == 'popup') {
			// ajax request from page to show form
			$this->load->view('includes/view_template', array(
					'person' => $person,
					'new_person' => $new,
					'content' => 'iMeet_form', // loads 'view_iMeet_form.php'
					'title' => ($new ? my_page_title('page_iMeet_addperson_title') : my_page_title('page_iMeet_editperson_title'))
			));
		} else {
			// showing form on top of "I want to meet" list now
			$list = $this->model_imeet->get_iMeet_list($user_id);
			$this->load->view('includes/view_template', array(
					'list' => $list,
					'person' => $person,
					'new_person' => $new,
					'content' => 'iMeet', // loads 'view_iMeet.php'
					'title' => ($new ? my_page_title('page_iMeet_addperson_title') : my_page_title('page_iMeet_editperson_title')),
					'popup_content' => 'iMeet_form'
			));
		}
	}


	/**
	 * 5. Prepares for validation and copies form to dbPersonToMeet object (it is returned)
	 */
	private function iMeet_person_from_form($user_id, &$person, $person_id = 0) {
		$this->form_validation->set_rules('fullname', lang('form_iMeet_fullname_field'), 'required|trim|xss_clean');
		$this->form_validation->set_rules('location', lang('form_iMeet_location_field'), 'trim|xss_clean');
		$this->form_validation->set_rules('web', lang('form_iMeet_web_field'), 'trim|xss_clean');
		$this->form_validation->set_rules('bio', lang('form_iMeet_bio_field'), 'trim|xss_clean');
		$this->form_validation->set_rules('email', lang('form_iMeet_email_field'), 'trim|xss_clean|valid_email');
		$this->form_validation->set_rules('twitter_username', lang('form_iMeet_twitter_field'), 'trim|xss_clean|callback_twitter_validation');
		$this->form_validation->set_rules('linkedin_username', lang('form_iMeet_linkedin_field'), 'trim|xss_clean|callback_linkedin_validation');
		$this->form_validation->set_rules('facebook_username', lang('form_iMeet_facebook_field'), 'trim|xss_clean|callback_facebook_validation');
		$this->form_validation->set_rules('reason', lang('form_iMeet_reason_field'), 'trim|xss_clean');

		$this->form_validation->set_message('twitter_validation', lang('form_iMeet_twitter_validation'));
		$this->form_validation->set_message('linkedin_validation', lang('form_iMeet_linkedin_validation'));
		$this->form_validation->set_message('facebook_validation', lang('form_iMeet_facebook_validation'));

		$person->id = $person_id;
		$person->userid = $user_id;
		$person->fullname = $this->input->post('fullname');
		$person->location = $this->input->post('location');
		$person->web = $this->input->post('web');
		$person->bio = $this->input->post('bio');
		$person->email = $this->input->post('email');

		$person->reason = $this->input->post('reason');
	}



	/**
	 * 5a. Callback function (cannot be private) that checks if Facebook profile of a person exists
	 */
	function facebook_validation($fb) {
		if ($fb) {
			return check_social_profile('facebook', $fb);
		} else {
			return true;
		}
	}


	/**
	 * 5b. Callback function (cannot be private) that checks if Twitter profile of a person exists
	 */
	function twitter_validation($tw) {
		if ($tw) {
			return check_social_profile('twitter', $tw);
		} else {
			return true;
		}
	}


	/**
	 * 5c. Callback function (cannot be private) that checks if LinkedIn profile of a person exists
	 */
	function linkedin_validation($in) {
		if ($in) {
			return check_social_profile('linkedin', $in);
		} else {
			return true;
		}
	}


	/**
	 * 5d. Processes Ajax requests - checks if twitter, linkedin or facebook users exists
	 */
	public function check_social ($social_network = '') {
		$username = $this->input->post($social_network);
		echo check_social_profile($social_network, $username); // function from "social" helper
	}


	/**
	 * 5e. Chooses best profile picture
	 */
	private function choose_best_person_picture(&$person, $new = false) {

		if ($person->twitter_img_url
				   && ! (preg_match('/default\_profile\_images/', $person->twitter_img_url) && ($person->linkedin_img_url || $person->facebook_img_url))) {
			if ($person->picture_url != $person->twitter_img_url || $new) {
				$person->big_picture_url = get_social_big_picture_url('twitter', $person->twitter_username); // resolve_url('https://api.twitter.com/1/users/profile_image?screen_name=' . $person->twitter_username . '&size=bigger');
			}
			$person->picture_url = $person->twitter_img_url;

		} elseif ($person->linkedin_img_url) {
			if ($person->picture_url != $person->linkedin_img_url || $new) {
				// getting the big picture
				$big_picture = get_social_big_picture_url('linkedin', $person->linkedin_username);
				if ($big_picture) {
					$person->big_picture_url = $big_picture;
				} else {
					$person->big_picture_url = $person->linkedin_img_url;
				}
			}
			$person->picture_url = $person->linkedin_img_url;

		} elseif ($person->facebook_img_url) {
			$person->picture_url = $person->facebook_img_url;
			$person->big_picture_url = get_social_big_picture_url('facebook', $person->facebook_username); // 'https://graph.facebook.com/' . $person->facebook_username . '/picture?type=normal';

		} else {
			$person->picture_url = '';
			$person->big_picture_url = '';
		}

	}


	/**
	 * 7. List of people who want to meet user
	 *   $action:
	 *      '' - list of people
	 *      'user' - one user who wants to meet current user, $param - user id
	 */
	public function meetMe($action = '', $param = ''){ // loads view_meetMe or view_user via template
		if ( $this->session->userdata('logged_in') ) {
			$user_id = $this->session->userdata('id');
			$this->load->model('model_users');

			switch ($action) {

				case '':
					$list = $this->model_users->get_meetMe_list($user_id);

					$this->load->view('includes/view_template',
						array(
							'list' => $list,
							'content' => 'meetMe', // loads 'view_meetMe.php'
							'title' => my_page_title('page_meetMe_title')
						)
					);
					break;

				case 'user':
					$user = $this->model_users->get_meetMe_user($user_id, $param);
					if ($user) {
						$this->load->view('includes/view_template', array(
							'user' => $user,
							'content' => 'user', // loads 'view_user.php'
							'title' => my_page_title('page_meetMe_user_title', $user->fullname),
							'public' => false
						));
					} else {
						// no such user as passed via $param
						redirect('/i/meetMe');
					}

					break;
			
				default:
					redirect('/i/meetMe');
			}

		} else redirect('/');
	}


	/**
	 * 8. Actions with user profile
	 *    Security of this function relies only on user id stored in the cookie: $this->session->userdata('id').
	 *    The seesion cookie should be encrypted via /application/config/config.php (it is not at the moment)
	 *    
	 *    $action parameter is passed via request URL: /i/profile/$action/
	 *    $action values:
	 *      '' - shows the user's profile (view_profile.php)
	 *      'edit' - form to edit user profile
	 *      'update' - validate and update user's profile
	 *		'verify' - verify email
	 *      'password' - change password, $param = 'validate' for form validation
	 *		'facebook' - connect/disconnect Facebook profile
	 *      'twitter' - connect/disconnect Twitter profile
	 *      'linkedin' - connect/disconnect LinkedIn profile
	 */
	public function profile($action = '', $param = ''){ // show, edit and validate&save profile
		if ( $this->session->userdata('logged_in') ) {

			$this->load->model('model_users');
			$user_id = $this->session->userdata('id');
			$user = $this->model_users->get_user($user_id);

			$previous_page = $this->input->server('HTTP_REFERER');

			switch ($action) {
			case '': // show profile
				if (user_profile_url() != base_url() . 'i/profile') {
					redirect (user_profile_url());
				}
				$this->load->view('includes/view_template', 
					array(
							'user' => $user,
							'content' => 'profile', // loads 'view_profile.php'
							'title' => my_page_title('page_myProfile_title')
					));
				break;

			case 'edit': // edit profile
				$this->settings($user);
				break;

			case 'update': // validate & update profile
				$this->form_validation->set_rules('fullname', lang('form_profile_fullname_field'), 'required|trim|xss_clean');
				$this->form_validation->set_rules('email', lang('form_profile_email_field'), ($user->password ? 'required|' : '') .
						'trim|valid_email|xss_clean|callback_validate_email' . ( $user->email ? '['.$user->email.']' : '' )); // validate_email() is called when validation is run
				$this->form_validation->set_rules('location', lang('form_profile_location_field'), 'trim|xss_clean');
				$this->form_validation->set_rules('web', lang('form_profile_web_field'), 'trim|xss_clean');
				$this->form_validation->set_rules('bio', lang('form_profile_bio_field'), 'trim|xss_clean');
				$this->form_validation->set_rules('interested_in', lang('form_profile_interestedin_field'), 'trim|xss_clean');

				$updated_user = new dbFullUser;
				$updated_user->copy($user);
				$updated_user->location = $this->input->post('location');
				$updated_user->web = $this->input->post('web');
				$updated_user->bio = $this->input->post('bio');
				$updated_user->interested_in = $this->input->post('interested_in');

				if ( $this->form_validation->run() ) {
					$updated_user->email = $this->input->post('email');
					$updated_user->verified = ($updated_user->email != $user->email ? false : $user->verified );
					$updated_user->fullname = $this->input->post('fullname');

					if ( $this->model_users->update_user($user_id, $updated_user) ) {
						// profile updated, checking if email changed and sending verification email
						if ( $updated_user->email != $user->email ) {
							// old keys are deleted so that only new email can be verified
							$this->model_users->delete_keys($user_id);

							// new key is generated
							$key = $this->model_users->unique_key($user_id);
							if( $this->resend_verification_email($updated_user, $key) ) {
								// verification email sent
								$this->session->set_flashdata('success', my_lang('msg_success_verification_msg_sent', $updated_user->email));
							} else {
								// recovery email not sent
								$this->session->set_flashdata('error', my_lang('msg_error_cant_send_verification_msg'));
							}
						}

						// also saving updated user data in session
						$user_session_data = new dbUser;
						$user_session_data->copy($updated_user);
						$this->session->set_userdata($user_session_data);

						redirect(user_profile_url());
					} else {
						// Could not update user, open form with original data
						$this->settings($user);
					}

				} else {
					// Did not validate form, open form with changed data, but fullname and email will be original
					$this->settings($updated_user);
				}
				break;

			case 'verify': // verify email (in case user didn't verify it previously)
				$key = $this->model_users->unique_key($user_id);
				if( $this->resend_verification_email($user, $key) ) {
					// verification email sent
					$this->session->set_flashdata('success', my_lang('msg_success_verification_msg_sent', $user->email));
				} else {
					// recovery email not sent
					$this->session->set_flashdata('error', my_lang('msg_error_cant_send_verification_msg'));
				}
				redirect($previous_page);
				break;

			case 'password': // change password form and validation/action
				if ($param = '') {
					$this->change_password_form();
				} elseif ($param = 'validate') {
					$this->form_validation->set_rules('old_password', my_lang('form_password_old_password_field'), 'trim|xss_clean' . ($user->password ? '|required' : ''));
					$this->form_validation->set_rules('password', my_lang('form_password_password_field'), 'required|matches[c_password]|trim|xss_clean');
					$this->form_validation->set_rules('c_password', my_lang('form_password_c_password_field'), 'required|trim|xss_clean');

					if ( $this->form_validation->run() ) {
						$ok = $this->model_users->change_user_password($user_id, $this->input->post('old_password'), $this->input->post('password'));
						if ($ok) {
							$this->session->set_flashdata('success', my_lang('msg_success_passwd_changed'));
							redirect(user_profile_url());
						} else {
							$this->session->set_flashdata('alert', my_lang('msg_alert_passwd_wrong'));
							redirect('/i/profile/password');
						}
					} else {
						$this->change_password_form();
					}
				}
				break;

			case 'facebook': // connect/disconnect facebook profile to user's profile
				if ( $user->facebook_id ) {
					// facebook connected, disconnect
					if ( $user->email && $user->password) {
						// user registered via email/password, disconnecting
						$user->facebook_id = 0;
						$user->facebook_name= '';
						$user->facebook_username = '';
						$this->model_users->update_user($user->id, $user);

						$this->choose_best_profile_picture($user);

						redirect('/i/profile/edit');
					} else {
						// no email/password, cannot disconnect facebook
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_disconnect_no_email', 'Facebook'));
						redirect('/i/profile/edit');
					}
				} else {
					// facebook not connected, connect facebook
					$this->load->library('fbconnect');

					$this->session->set_userdata('previous_page', $previous_page);

					$ok = $this->fbconnect->fbredirect('/i/profile_facebook');
					if ( ! $ok) {
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_connect', 'Facebook'));
						redirect($previous_page);
					}
				}
				break;

			case 'twitter': // connect/disconnect twitter profile to user's profile
				if ( $user->twitter_id ) {
					// twitter connected, disconnect
					if ( $user->email && $user->password) {
						// user registered via email/password, "disconnecting"
						$user->twitter_id = 0;
						$user->twitter_token = '';
						$user->twitter_token_secret = '';
						$user->twitter_name= '';
						$user->twitter_username = '';
						$user->twitter_img_url = '';
						$user->twitter_verified = false;

						$this->choose_best_profile_picture($user);

						// updating user record
						$this->model_users->update_user($user->id, $user);

						// clearing twitter session data
						$this->load->library('twconnect');
						$this->twconnect->twclear_session_data();

						// clearing twitter username in session
						$this->session->unset_userdata('twitter_username');

						redirect('/i/profile/edit');
					} else {
						// no email/password, cannot disconnect twitter
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_disconnect_no_email', 'Twitter'));
						redirect('/i/profile/edit');
					}
				} else {
					// twitter not connected, connect twitter
					$this->load->library('twconnect');

					$this->session->set_userdata('previous_page', $previous_page);
					$ok = $this->twconnect->twredirect('/i/profile_twitter');
					if ( ! $ok) {
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_connect', 'Twitter'));
						$this->twconnect->twclear_session_data();
						redirect($previous_page);
					}
				}
				break;

			case 'linkedin': // connect/disconnect linkedin profile to user's profile
				if ( $user->linkedin_id ) {
					// linkedin connected, disconnect
					if ( $user->email && $user->password) {
						// user registered via email/password, "disconnecting"
						$user->linkedin_id = '';
						$user->linkedin_token = '';
						$user->linkedin_token_secret = '';
						$user->linkedin_token_expires = 0;
						$user->linkedin_name= '';
						$user->linkedin_username = '';
						$user->linkedin_img_url = '';

						$this->choose_best_profile_picture($user);

						// updating user record
						$this->model_users->update_user($user_id, $user);

						// clearing linkedin session data
						$this->load->library('in_connect');
						$this->in_connect->in_clear_session_data();

						redirect($previous_page);
					} else {
						// no email/password, cannot disconnect linkedin
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_disconnect_no_email', 'LinkedIn'));
						redirect($previous_page);
					}
				} else {
					// LinkedIn not connected, connect LinkedIn
					$this->load->library('in_connect');

					$this->session->set_userdata('previous_page', $previous_page);
					$ok = $this->in_connect->in_redirect('/i/profile_linkedin');
					if ( ! $ok) {
						$this->session->set_flashdata('alert', my_lang('msg_alert_social_cant_connect', 'LinkedIn'));
						$this->in_connect->in_clear_session_data();
						redirect('/i/profile/edit');
					}
				}
				break;

			default:
				// some wrong path after /i/profile
				redirect(user_profile_url());
			}
		} else redirect('/');
	}


	/**
	 * 8a. Callback function (cannot be private) that validates email after profile editing making sure it is not used by other user but the current one
	 */
	function validate_email($email = '', $old_email = '') {
		if ($email == '') {
			return true;
		} else {
			$this->form_validation->set_message('validate_email',
					my_lang('form_profile_email_validation', $email));

			$email_count = $this->model_users->check_email($email);
			if ( $email <> $old_email || $old_email == '' ) {
				return $email_count === 0;
			} else {
				return $email_count === 1;
			}
		}
	}


	/**
	 * 8b. Chooses best profile picture
	 */
	private function choose_best_profile_picture(&$user) {

		if ($user->linkedin_img_url) {
			if ($user->picture_url != $user->linkedin_img_url) {
				// getting the big picture
				$big_picture = get_social_big_picture_url('linkedin', $user->linkedin_username, true); // sorry for this hack with true
				if ($big_picture) {
					$user->big_picture_url = $big_picture;
				} else {
					$user->big_picture_url = $user->linkedin_img_url;
				}
			}
			$user->picture_url = $user->linkedin_img_url;
		}
		elseif ( $user->twitter_img_url
				   && ! (preg_match('/default\_profile\_images/', $user->twitter_img_url) && $user->facebook_img_url) )
		{
			
			
			$user->picture_url = $user->twitter_img_url;
			$user->big_picture_url = get_social_big_picture_url('twitter', $user->twitter_username); //resolve_url('https://api.twitter.com/1/users/profile_image?screen_name=' . $user->twitter_username . '&size=bigger');

		} elseif ($user->facebook_img_url) {
			$user->picture_url = $user->facebook_img_url;
			$user->big_picture_url = get_social_big_picture_url('facebook', $user->facebook_username); //'https://graph.facebook.com/' . $user->facebook_username . '/picture?type=normal';

		} else {
			$user->picture_url = '';
			$user->big_picture_url = '';
		}

		$this->session->set_userdata('picture_url', $user->picture_url);
	}


	/**
	 * 9. Redirect point for facebook profile connection
	 */
	public function profile_facebook(){
		$previous_page = $this->session->userdata('previous_page');
		$this->session->unset_userdata('previous_page');

		if ( ! $previous_page) {
			$previous_page = '/';
		}

		$this->load->library('fbconnect');
		$fb_user = $this->fbconnect->user;
		if ($fb_user) {
			// facebook authenticated!
			$this->load->model('model_users');
			$second_user = $this->model_users->get_facebook_user($fb_user);
						
			if ( $second_user ) {
				// cannot connect, there is another user connected to this facebook profile!
				$this->session->set_flashdata('alert', my_lang('msg_alert_social_another_user', 'Facebook'));
				redirect($previous_page);
			} else {
				// connecting Facebook!
				$user_id = $this->session->userdata('id');
				$user = $this->model_users->get_user($user_id);
				$user->facebook_id = $fb_user['id'];
				$user->facebook_name= $fb_user['name'];
				$user->facebook_username = $fb_user['username'];
				$user->facebook_img_url = 'https://graph.facebook.com/' . $user->facebook_username . '/picture';
				/*
				 *
				 * Maybe copy first and last name from facebook as well...
				 *
				 */

				$this->choose_best_profile_picture($user);

				$this->model_users->update_user($user->id, $user);
				redirect($previous_page);
			}
		} else {
			// facebook DID NOT authenticate!
			$this->session->set_flashdata('alert', my_lang('msg_alert_social_connect_declined', 'Facebook'));
			redirect($previous_page);
		}
	}



	/**
	 * 9a. Redirect point for twitter profile connection
	 */
	public function profile_twitter(){
		$previous_page = $this->session->userdata('previous_page');
		$this->session->unset_userdata('previous_page');

		if ( ! $previous_page) {
			$previous_page = '/';
		}

		$this->load->library('twconnect');
		$ok = $this->twconnect->twprocess_callback();
		if ($ok) {
			// twitter authenticated!
			$this->load->model('model_users');
			$second_user = $this->model_users->check_twitter_user( $this->twconnect->tw_user_id );
			if ( $second_user ) {
				// cannot connect, there is another user connected to this twitter profile!
				$this->twconnect->twclear_session_data();
				$this->session->set_flashdata('alert', my_lang('msg_alert_social_another_user', 'Twitter'));
				redirect($previous_page);
			} else {
				// connecting Twitter!
				$user_id = $this->session->userdata('id');
				$user = $this->model_users->get_user($user_id);

				$user->twitter_id = $this->twconnect->tw_user_id;
				$user->twitter_token = $this->twconnect->tw_access_token['oauth_token'];
				$user->twitter_token_secret = $this->twconnect->tw_access_token['oauth_token_secret'];
				$user->twitter_username = $this->twconnect->tw_user_name;

				// Not the most effisient way - the library is reloaded to create a new Twconnect (TwitterOAuth) object with the access code that is now available
				$this->load->library('twconnect');
				$this->twconnect->twaccount_verify_credentials();

				$user->twitter_name= $this->twconnect->tw_user_info->name;
				$user->twitter_img_url = $this->twconnect->tw_user_info->profile_image_url;
		
				$user->twitter_verified = $this->twconnect->tw_user_info->verified;

				$this->choose_best_profile_picture($user);

				// updating user with twitter data
				$this->model_users->update_user($user->id, $user);

				// set twitter username in session
				$this->session->set_userdata('twitter_username', $user->twitter_username);

				redirect($previous_page);
			}
		} else {
			// twitter DID NOT authenticate!
			$this->session->set_flashdata('alert', my_lang('msg_alert_social_connect_declined', 'Twitter'));
			redirect($previous_page);
		}
	}


	/**
	 * 9b. Redirect point for LinkedIn profile connection
	 */
	public function profile_linkedin(){
		$previous_page = $this->session->userdata('previous_page');
		$this->session->unset_userdata('previous_page');

		if ( ! $previous_page) {
			$previous_page = '/';
		}

		$this->load->library('in_connect');
		$ok = $this->in_connect->in_process_callback();

		$in_user = $this->in_connect->in_user;

		if ($ok && $in_user) { // LinkedIn authenticated and we have user data
			$this->load->model('model_users');
			$second_user = $this->model_users->check_linkedin_user( $in_user->id );
			if ( $second_user ) {
				// cannot connect, there is another user connected to this LinkedIn profile!
				$this->in_connect->in_clear_session_data();
				$this->session->set_flashdata('alert', my_lang('msg_alert_social_another_user', 'LinkedIn'));
				redirect($previous_page);
			} else {
				// connecting LinkedIn!
				$user_id = $this->session->userdata('id');
				$user = $this->model_users->get_user($user_id);

				$user->linkedin_id = $in_user->id;

				// saving token - it will expire and there is no logic yet to track it
				$user->linkedin_token = $this->in_connect->in_access_token['oauth_token'];
				$user->linkedin_token_secret = $this->in_connect->in_access_token['oauth_token_secret'];

				// calculating expiration time (since UNIX epoch 1970/1/1 00:00:00) (linkedin returns expiration period in seconds)
				$user->linkedin_token_expires = time() + $this->in_connect->in_access_token['oauth_authorization_expires_in'];

				if ($in_user->publicProfileUrl) {
				// cutting the domain part away (it will cut any domain away), because 'linkedin_username' is the path part of the public URL (I just didn't know then...)
					$in_username = preg_replace('/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/', '', $in_user->publicProfileUrl);

					// cutting any parameters that can be there - maybe this is excessive
					$in_username = preg_replace('/(\?.*)/', '', $in_username);
					$user->linkedin_username = $in_username;
				}

				$user->linkedin_name= (isset($in_user->formattedName) ? $in_user->formattedName : '');
				$user->linkedin_img_url = (isset($in_user->pictureUrl) ? $in_user->pictureUrl : '');

				$this->choose_best_profile_picture($user);

				// updating user with LinkedIn data
				$this->model_users->update_user($user->id, $user);

				redirect($previous_page);
			}
		} else {
			// LinkedIn DID NOT authenticate!
			$this->session->set_flashdata('alert', my_lang('msg_alert_social_connect_declined', 'LinkedIn'));
			redirect($previous_page);
		}
	}


	/**
	 * 10. Shows form to edit user profile (view_settings.php via view_template.php)
	 */
	private function settings($user) { // loads view_settings via template
		$this->load->view('includes/view_template', array(
			'user' => $user,
			'content' => 'settings', // loads 'view_settings.php'
			'title' => my_page_title('page_myProfile_edit_title')
		));
	}


	/**
	 * 11. Sends verification email to user
	 */
	private function resend_verification_email($user, $key) {
		$this->load->language('emails');

		$this->load->library('email', array('mailtype' => 'html'));
		$this->email->from('team@whoyoumeet.com', my_lang('email_from_WYM'));
		$this->email->to($user->email);
		$this->email->subject(my_lang('email_resend_verification_subject'));
		$message  = my_lang('email_hi_user' , $user->fullname);
		$message .= my_lang('email_resend_verification_thanks');
		$message .= my_lang('email_resend_verification_link', base_url() . 'signup/verify_email/' . $key);
		$message .= my_lang('email_WYM_signature');

		$this->email->message($message);
		return $this->email->send();
	}


	/**
	 * 12. Shows form to change user password (view_change_password.php via view_template.php)
	 */
	private function change_password_form() { //
		// show change password form
		$this->load->view('includes/view_template',
			array(
				'content' => 'change_password',  // view_change_password.php is loaded
				'title' => my_page_title('page_myProfile_change_passwd_title')
			));
	}

}

/* End of file /application/controllers/i.php */