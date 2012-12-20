<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/models/model_users.php
 * 
 * Model_users model class
 *
 * Handles database access to 'users' and 'user_keys' tables in the database
 * Used by Login and Signup controllers and by main controller (i) to display and edit user profile
 * User passwords do not go outside this model (at least they are not supposed to :)
 *
 *
 * Classes
 *  1. dbUser - main information about the user. This information is stored in the session cookie
 *  2. dbFullUser - same fields as user record in 'users' table
 *  3. dbUserKey - same fields as user record in 'user_keys' table. Not currently used
 *
 * Model_users class functions:
 *
 * "Meet me" page functions
 *  3. public function get_meetMe_list($user_id) - returns the list of people who want to meet a user
 *  3a.public function get_meetMe_user($current_user_id, $meemMe_user_id) - retrieves information about user who wants to meet another user
 *
 * Functions for login and signup
 *  4. public function login_user($email, $password) - logs user in
 *  5. public function unique_key() - generates a unique key for email verification
 *  5a.public function delete_keys($user_id) - delete all keys for a user - used when email is changed
 *  6. public function add_user($user, $key) - adds user and user key during sign-up
 *  6a. private function update_people_user_connections($user) - update usertomeetid field of people connected to a user
 *  6b. private function meet_WhoYouMeet_team($user) - add user to "I want to  meet" list of "Who You Meet team" user
 *
 *  7. public function verify_user($key) - verifies user
 *
 * Functions for password recovery
 *  7a.public function check_email($email) - checks if email is registered in database
 *  7b.public function get_email_user($email) - returns user information by email
 *
 * Retrieving user data - most commonly used
 *  8. public function get_user($id) - retrieves user information
 *  8a. public function get_any_user($current_user_id, $user_id) - retrieves any user information to display public profile
 *  8b. public function connected_personToMeet_id($current_user_id, $user_id) - ID of the person in the list of the current user connected to some other user
 *
 * Updating user
 *  9. public function update_user($id, $user_data) - updates user information
 *  9a.public function update_user_password($id, $password) - updates user password
 * 
 * Cheking and retrieving users by their social profiles
 * 10. public function get_facebook_user($fb_user) - get user by facebook id
 * 10a.public function check_facebook_user($fb_user) - checks if there is user with facebook id
 * 10b.public function get_twitter_user($tw_id) - get user by twitter id
 * 10c.public function check_twitter_user($tw_id) - check if there is user with twitter id
 * 10d.public function get_linkedin_user($in_id) - get user by linkedin id
 * 10e.public function check_linkedin_user($in_id) - check if there is user with linkedin id
 *
 * Logging in and signing up users via their social profiles
 * 11. public function login_facebook_user($fb_user) - logs in / signs up facebook user
 * 11a.public function login_twitter_user($tw_user_id) - logs in / signs up twitter user
 * 11b.public function login_linkedin_user($in_user, $in_access_token)
 *
 */


/**
 * 1. Main information about the user. This information is stored in the session cookie
 *    Password field is used internally in the model but set to blank when returned to controllers
 */
class dbUser {
	var $id = 0;
	var $created = 0; // when the user was created, being implemented
	var $last_login = 0; // datetime when the user last logged in, being implemented
	var $private = false; // not currently used
	var $email = '';
	var $verified = false;
	var $password = '';
	var $fullname = '';
	var $twitter_username = '';
	var $picture_url = '';

	public function copy($data) { // not necessary this class, but same fields
		$this->id = $data->id;
		$this->created = $data->created; // datetime when the user was created, being implemented
		$this->last_login = $data->last_login; // datetime when the user last logged in, being implemented
		$this->private = $data->private;
		$this->email = $data->email;
		$this->verified = $data->verified;
		$this->password = $data->password;
		$this->fullname = $data->fullname;
		
		/**
		 * Very important field !!! Also WhoYouMeet username! Sorry for that.
		 */
		$this->twitter_username = $data->twitter_username;
		/**
		 *
		 */

		$this->picture_url = $data->picture_url;
		return $this;
	}
}


/**
 * 2. Same fields as user record in 'users' table
 */
class dbFullUser extends dbUser {
	var $big_picture_url = '';
	var $firstname = ''; // not currently used
	var $lastname = ''; // not currently used
	var $bio = '';
	var $location = '';

	/* LinkedIn account fields */
	var $linkedin_id =''; // user id is a key (string) in LinkedIn, not a number
	var $linkedin_token = '';
	var $linkedin_token_secret = '';
	var $linkedin_token_expires = 0; // expiration date/time for user LinkedIn access token (one more problem to worry about!)
	var $linkedin_username = ''; // actually, it is path part of public url, so can be 'in/epoberezkin' or 'pub/stephen-page/4/413/b6b'
	var $linkedin_name = '';
	var $linkedin_img_url = '';

	/* Twitter account fields */
	var $twitter_id = 0;
	var $twitter_token = '';
	var $twitter_token_secret = '';
	var $twitter_name = '';
	var $twitter_img_url = '';
	var $twitter_verified = false;

	/* facebook accounts fields */
	var $facebook_id = 0;
	var $facebook_username = '';
	var $facebook_name = '';
	var $facebook_img_url = '';
	var $facebook_gender = '';
	
	var $web = '';
	var $interested_in = '';
	var $last_updated = 0; // when the record was created or last updated, managed by MySQL

	public function copy($data) { // not necessary this class, but same fields
		parent::copy($data);
		$this->big_picture_url = $data->big_picture_url;
		$this->firstname = $data->firstname;
		$this->lastname = $data->lastname;
		$this->bio = $data->bio;
		$this->location = $data->location;

		/* LinkedIn account fields */
		$this->linkedin_id = $data->linkedin_id;
		$this->linkedin_token = $data->linkedin_token;
		$this->linkedin_token_secret = $data->linkedin_token_secret;
		$this->linkedin_token_expires = $data->linkedin_token_expires;
		$this->linkedin_name = $data->linkedin_name;
		$this->linkedin_username = $data->linkedin_username;
		$this->linkedin_img_url = $data->linkedin_img_url;

		/* Twitter account fields */
		$this->twitter_id = $data->twitter_id;
		$this->twitter_token = $data->twitter_token;
		$this->twitter_token_secret = $data->twitter_token_secret;
		$this->twitter_name = $data->twitter_name;
		$this->twitter_img_url = $data->twitter_img_url;
		$this->twitter_verified = $data->twitter_verified;
		
		/* facebook accounts fields */
		$this->facebook_id = $data->facebook_id;
		$this->facebook_username = $data->facebook_username;
		$this->facebook_name = $data->facebook_name;
		$this->facebook_img_url = $data->facebook_img_url;
		$this->facebook_gender = $data->facebook_gender;

		$this->web = $data->web;
		$this->interested_in = $data->interested_in;
		$this->last_updated = $data->last_updated; // when the record was created or last updated, managed by MySQL

		return $this;
	}

}


/**
 * 3. Same fields as user record in 'user_keys' table. Not currently used
 */
class dbUserKey {
	var $id = 0;
	var $userid = 0;
	var $key = '';

	function __constructor($key) {
		$this->key = $key;
	}
}


/**
 * 
 *
 * Model_users class definition
 *
 *
 */
class Model_users extends CI_Model {

	public $blank_user;

	function __construct() {
		parent::__construct();
		$this->blank_user = array(
			'id' => '0',
			'private' => 'false', // not currently used
			'email' => ' ',
			'verified' => false,
			'password' => ' ',
			'fullname' => ' '
		);
	}


	/**
	 * 3. Retrieves and returns the list of people who want to meet a user
	 */
	public function get_meetMe_list($user_id) {

		$user = $this->get_user($user_id);

		// user can only see who wants to meet him if any of his social profiles connected

		$sql = 'SELECT `users`.`id`, `users`.`email`, `users`.`fullname`, `users`.`bio`, `users`.`picture_url`, ';
		$sql .= '`users`.`linkedin_username`, `users`.`linkedin_img_url`, ';
		$sql .= '`users`.`twitter_username`, `users`.`twitter_img_url`, `users`.`twitter_verified`, ';
		$sql .= '`users`.`facebook_username`, `users`.`web`, `users`.`interested_in`, `peopletomeet`.`reason` FROM `users` ';

		$sql .= 'JOIN `peopletomeet` ON `peopletomeet`.`userid` = `users`.`id` ';

		// conditions to find out who wants to meet current user
		$conditions = $this->meetMe_conditions($user);

		// do not show users who want to meet the current user without specific reason
		$sql .= 'WHERE (' . $conditions . ") ORDER BY `peopletomeet`.`id` DESC"; // AND peopletomeet.reason != ''

		$query = $this->db->query($sql);

		if ( $query->num_rows() > 0 ) {
			return $query->result();
		} else return false;

	}


	/**
	 * 3a. Retrieves information about user who wants to meet another user including his reason
	 *    Returns object copied from query
	 *    Password field is set to '***' or ''
	 *    Username = true if the user should be retrieved by username (= twitter username)
	 */
	public function get_meetMe_user($current_user_id, $meemMe_user_id_or_un, $get_by_username = false, $social_network = 'twitter') {
		$current_user = $this->get_user($current_user_id);

		// user can only see who wants to meet him if any of his social profiles connected

		$sql = 'SELECT `users`.*, `peopletomeet`.`reason` FROM `users` ';
		$sql .= 'JOIN `peopletomeet` ON `peopletomeet`.`userid` = `users`.`id` ';

		// conditions to find out if $meemMe_user_id wants to meet current user
		$conditions = $this->meetMe_conditions($current_user);

		if ($get_by_username) {
			$sql .= "WHERE (" . $conditions . ") AND `users`.`" . $social_network . "_username` = '" . $meemMe_user_id_or_un . "'";
		} else {
			$sql .= "WHERE (" . $conditions . ") AND `users`.`id` = " . $meemMe_user_id_or_un;
		}

		$query = $this->db->query($sql);

		if ( $query->num_rows() >= 1 ) {
			$user = new dbFullUser();
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');
			if ( $query->num_rows() > 1 ) {
				$user->reason = '<ol>';
				foreach ($query->result() as $user_record) {
					$user->reason .= '<li>' . $user_record->reason . '</li>';
				}
				$user->reason .= '</ol>';
			} else {
				$user->reason = $query->row()->reason;
			}
			return $user;
		} else return false;
	}


	/**
	 * 3b. Puts together conditions for SQL query to filter out users who want to meet current user ($user)
	 */
	private function meetMe_conditions($user, $match_by_id = true) {
		// conditions to find out who wants to meet current user
		if ($match_by_id) {
			$conditions = '`peopletomeet`.`usertomeetid` = ' . $user->id;
		} else {
			$conditions = '';
		}

		// matching linkedin profiles
		if ($user->linkedin_id) {
			if ($conditions != ''){
				$conditions .= ' OR ';
			}
			$conditions .= "`peopletomeet`.`linkedin_id` = '" . $user->linkedin_id . "'";
			$conditions .= " OR `peopletomeet`.`linkedin_username` = '" . $user->linkedin_username . "'";
		}

		// or matching twitter profiles
		if ($user->twitter_id) {
			// checking if there is something already in $conditions (can be linkedin)
			if ($conditions != ''){
				$conditions .= ' OR ';
			}
			$conditions .= '`peopletomeet`.`twitter_id` = ' . $user->twitter_id;
			$conditions .= " OR `peopletomeet`.`twitter_username` = '" . $user->twitter_username . "'";
		}

		// or matching facebook profiles
		if ($user->facebook_id) {
			// checking if there is something already in $conditions (can be linkedin and/or twitter)
			if ($conditions != ''){
				$conditions .= ' OR ';
			}
			$conditions .= '`peopletomeet`.`facebook_id` = ' . $user->facebook_id;
			$conditions .= " OR `peopletomeet`.`facebook_username` = '" . $user->facebook_username . "'";
		}
		
		return $conditions;
	}


	/**
	 * 4. Logs user in. Returns dbUser object or false if email/password combination is not found 
	 */
	public function login_user($email, $password) {
		$query = $this->db->where('email', $email)->where('password', md5($password))->get('users');
		
		if ( $query->num_rows() == 1 ) {
			$user = new dbUser;
			$user->copy($query->row());
			$user->password = '***';
			return $user;
		} else {
			return false;
		}
	}


	/**
	 * 5. Generates a unique key and stores it in 'user_keys' table (used for email verification)
	 */
	public function unique_key($user_id = 0) {
		do {
			$key = md5(uniqid());
			$query = $this->db->where('key', $key)->get('user_keys');
		} while ($query->num_rows() > 0); // makes sure the key in table is unique :))))

		if ($user_id) {
			// creating key to re-send verification email
			$this->db->insert('user_keys', array('userid' => $user_id, 'key' => $key));
		} else {
			// reserving key for a new user
			$this->db->set('key', $key)->insert('user_keys');
		}
		return $key;
	}


	/**
	 * 5a. Delete all keys for a user - used when email is changed
	 */
	public function delete_keys($user_id) {
		if ($user_id) {
			return $this->db->where('userid', $user_id)->delete('user_keys');
		} else {
			return false;
		}
	}


	/**
	 * 6. Adds user and user key during sign-up. Links user to email key.
	 *    User can use application immediately, but marked as unverified
	 *    Database structure and logic allow requesting many verification emails, any key will verify user
	 */
	public function add_user($user, $key) {
		$user->password = md5($user->password);
		$ok = $this->db->insert('users', $user); // add user

		if ($ok) {
			$user->id = $this->db->insert_id();
			$user->password = '***';

			// update key for email verification
			$this->db->where('key', $key)->set('userid',$user->id)->update('user_keys');

			// Add new user to the list of Who You Meet team user
			$this->meet_WhoYouMeet_team($user);

			// update people added by other users now connected to this new user
			$this->update_people_user_connections($user);

			return $user;
		} else {
			return false;
		}
	}


	/**
	 * 6a. Update usertomeetid field of people connected to a user
	 * @param type $user
	 */
	private function update_people_user_connections($user) {
		$conditions = $this->meetMe_conditions($user, false);
		if ($conditions) {
			$sql = 'UPDATE `peopletomeet` SET `usertomeetid` = ' . $user->id;
			$sql .= ' WHERE ' . $conditions;
			return $this->db->query($sql);
		} else {
			return 0;
		}
	}


	/**
	 * 6b. Add user to "I want to  meet" list of "Who You Meet team" user
	 * @param type $user
	 */
	private function meet_WhoYouMeet_team($user) {
		// Who You Meet team wants to meet every user to say thank you

		$query = $this->db->query('SELECT MAX(`order`) as `max_order` FROM `peopletomeet`	WHERE `userid` = 41');
		$max_order = $query->row()->max_order;
		if (is_null($max_order)) {
			$max_order = 0;
		} else {
			$max_order += 128;
		}

		$person = array(
				'order' => $max_order,
				'userid' => 41, // id of the Who You Meet team user
				'usertomeetid' => $user->id,
				'reason' => 'To say thank you for signing up to Who You Meet!',
				'fullname' => $user->fullname,
			);
		$this->db->insert('peopletomeet', $person);
	}


	/**
	 * 7. Verifies user.
	 *    Called by verify_email($key) of Signup controller when the link in email is clicked
	 *    Also used for password recovery in Login controller.
	 *    All verification keys for this user are deleted after verificaiton by any key
	 */
	public function verify_user($key) {
		$query = $this->db->where('key', $key)->get('user_keys'); // retrieving user id

		if ( $query->num_rows() == 1 ) {
			$user_key = $query->row();
			
			$success = $this->db->where('id', $user_key->userid)->set('verified', true)->update('users');
			if ( $success ) { // delete all keys for this user
				$this->db->where('userid', $user_key->userid)->delete('user_keys');
				return $user_key->userid;
			} else return false;
		} else return false;
	}


	/**
	 * 7a. Checks if email is registered in database.
	 */
	public function check_email($email) {
		$query = $this->db->where('email', $email)->get('users');
		return $query->num_rows(); // raise error if there are more than one
	}


	/**
	 * 7b. Retrieves user information by email
	 *    Returns dbFullUser object
	 *    Password field is set to blank
	 */
	public function get_email_user($email) {
		$query = $this->db->where('email', $email)->get('users');
		if ( $query->num_rows() === 1 ) {
			$user = new dbFullUser;
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');
			return $user;
		} else return false;
	}

	/**
	 * 8. Retrieves user information - should be called for the current user only
	 *    Returns dbFullUser object
	 *    Password field is set to '' (of not set) or '***' (if it is set)
	 */
	public function get_user($id_or_un, $get_by_username = false, $social_network = 'twitter') {
		if ($get_by_username) {
			$query = $this->db->where($social_network . '_username', $id_or_un)->get('users');
		} else {
			$query = $this->db->where('id', $id_or_un)->get('users');
		}
		if ( $query->num_rows() === 1 ) {
			$user = new dbFullUser;
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');
			return $user;
		} else {
			// no such user
			return false;
		}
	}


	/**
	 * 8a. Retrieves any user information to display public profile
	 *    Returns dbFullUser object + reason field (if the user does wants to meet current user)
	 *    Password field is always set to ''
	 *    Email is set to blank if user does not want to meet current user
	 */
	public function get_any_user($current_user_id, $user_id_or_un, $get_by_username = false, $social_network = 'twitter') {
		if ($current_user_id) {
			$user = $this->get_meetMe_user($current_user_id, $user_id_or_un, $get_by_username, $social_network);
		}
		if (isset($user) && $user) {
			// requested user wants to meet the current user
			$user->connected_person_id = $this->connected_personToMeet_id($current_user_id, $user->id);
			return $user;
		} else {
			// requested user does not want to meet the current user
			$user = $this->get_user($user_id_or_un, $get_by_username, $social_network);
			if ($user) {
				if ($user->id != $current_user_id) {
					$user->password = '';
					$user->email = '';
					$user->connected_person_id = $this->connected_personToMeet_id($current_user_id, $user->id);
				}
				return $user;
			} else {
				// no such user
				return false;
			}
		}
	}


	/**
	 * 8b. ID of the person in the list of the current user connected to some other user
	 * @param type $current_user_id
	 * @param type $user_id
	 * @return int ID in peopletomeet table or false
	 */
	public function connected_personToMeet_id($current_user_id, $user_id) {
		if ($current_user_id) {
			$sql = 'SELECT `peopletomeet`.`id` ';
			$sql .= 'FROM `peopletomeet` ';
			$sql .= 'WHERE `userid` = ' . $current_user_id;
			$sql .= ' AND `usertomeetid` = ' . $user_id;
			$sql .= ' ORDER BY `order` DESC';

			$query = $this->db->query($sql);

			if ($query->num_rows() >= 1) {
				return $query->row()->id;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}


	/**
	 * 9. Updates user information
	 *    $id - user id in a database, $user_data - dbFullUser object
	 */
	public function update_user($id, $user_data) {
		$user_array = (array) $user_data;

		// Password field should not be deleted in the database
		unset($user_array['password']);

		// update people added by other users now connected to this new user
		$this->update_people_user_connections($user_data);

		return $this->db->where('id', $id)->update('users', $user_array);
	}


	/**
	 * 9a. Updates user password
	 *    $id - user id in a database, $password - uncoded password
	 */
	public function update_user_password($id, $password) {
		return $this->db->where('id', $id)->set('password', md5($password))->update('users');
	}


	/**
	 * 9b. Changes user password checking the current one ($old_password - uncoded)
	 *    $id - user id in a database, $new_password - uncoded new password
	 */
	public function change_user_password($id, $old_password, $new_password) {
		$query = $this->db->select('password')->where('id', $id)->get('users');
		if ( $query->num_rows() === 1 ) {
			if ( md5($old_password) == $query->row()->password || ( ! $old_password && ! $query->row()->password)) {
				return $this->db->where('id', $id)->set('password', md5($new_password))->update('users');
			} else {
				//echo 'password did not match';
				return false;
			}
		} else {
			// user not found
			return false;
		}
	}


	/**
	 * 10. Get user by facebook id (the whole facebook user array is used as parameter)
	 */
	public function get_facebook_user($fb_user) {
		$query = $this->db->where('facebook_id', $fb_user['id'])->get('users');
		if ( $query->num_rows() === 1 ) {
			$user = new dbFullUser;
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');;
			return $user;
		} else return false;
	}


	/**
	 * 10a. Checks if there is user with facebook id (the whole facebook user array is used as parameter)
	 */
	public function check_facebook_user($fb_user) {
		$query = $this->db->where('facebook_id', $fb_user['id'])->get('users');
		return $query->num_rows() === 1;
	}


	/**
	 * 10b. Get user by twitter id
	 */
	public function get_twitter_user($tw_id) {
		$query = $this->db->where('twitter_id', $tw_id)->get('users');
		if ( $query->num_rows() === 1 ) {
			$user = new dbFullUser;
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');;
			return $user;
		} else return false;
	}


	/**
	 * 10c. Check if there is user with twitter id
	 */
	public function check_twitter_user($tw_id) {
		$query = $this->db->where('twitter_id', $tw_id)->get('users');
		return $query->num_rows() === 1;
	}


	/**
	 * 10d. Check if there is user with linkedin id
	 */
	public function get_linkedin_user($in_id) {
		$query = $this->db->where('linkedin_id', $in_id)->get('users');
		if ( $query->num_rows() === 1 ) {
			$user = new dbFullUser;
			$user->copy($query->row());
			$user->password = ($user->password ? '***' : '');;
			return $user;
		} else return false;
	}


	/**
	 * 10e. Check if there is user with linkedin id
	 */
	public function check_linkedin_user($in_id) {
		$query = $this->db->where('linkedin_id', $in_id)->get('users');
		return $query->num_rows() === 1;
	}


	/**
	 * 11. Logs in / signs up facebook user
	 */
	public function login_facebook_user($fb_user) {
		if ($fb_user) {
			$query = $this->db->where('facebook_id', $fb_user['id'])->get('users');
		
			if ( $query->num_rows() === 1 ) {
				// user found
				$user = new dbUser;
				$user->copy($query->row());
				$user->password = ($user->password ? '***' : '');
				return $user;

			} else {
				// no such user, signing up
				$user = new dbFullUser;
				$user->facebook_id = $fb_user['id'];
				$user->facebook_username = $fb_user['username'];
				$user->facebook_name = $fb_user['name'];
				$user->facebook_img_url = 'https://graph.facebook.com/' . $user->facebook_username . '/picture';
				$user->facebook_gender = $fb_user['gender'];
				$user->fullname = $fb_user['name'];
				$user->firstname = (isset($fb_user['first_name']) ? $fb_user['first_name'] : '');
				$user->lastname = (isset($fb_user['last_name']) ? $fb_user['last_name'] : '');
				$user->bio = (isset($fb_user['bio']) ? $fb_user['bio'] : '');
				$user->location = 
					(isset($fb_user['location']['name']) ? $fb_user['location']['name'] : '');

				$user->picture_url = $user->facebook_img_url;
				$user->big_picture_url = 'https://graph.facebook.com/' . $user->facebook_username . '/picture?type=normal';

				// Inserting user
				$query = $this->db->insert('users', $user); // we don't have id yet...
			
				$user->id = $this->db->insert_id();

				$this->meet_WhoYouMeet_team($user);

				return $user;

				if ( ! $ok) {
					// Cannot insert user
					// Cannot insert user
					return false;
				}
			}
		} else {
			// empty user passed as parameter
			return false;
		}
	}


	/**
	 * 11a. Logs in / signs up twitter user
	 */
	public function login_twitter_user($tw_user_id) {
		if ($tw_user_id) {
			$query = $this->db->where('twitter_id', $tw_user_id)->get('users');

			if ( $query->num_rows() === 1 ) {
				// user found
				$user = new dbUser;
				$user->copy($query->row());
				$user->password = ($user->password ? '***' : '');
				return $user;

			} else {
				// no such user, signing up
				$this->load->library('twconnect');
				$user = new dbFullUser;

				$user->twitter_id = $tw_user_id;
				$user->twitter_token = $this->twconnect->tw_access_token['oauth_token'];
				$user->twitter_token_secret = $this->twconnect->tw_access_token['oauth_token_secret'];
				$user->twitter_username = $this->twconnect->tw_user_name;

				$this->twconnect->twaccount_verify_credentials(); // this will get us user info

				if ($this->twconnect->tw_user_info) {
					// we have extended user info
					$user->fullname = $this->twconnect->tw_user_info->name;
					$user->bio = $this->twconnect->tw_user_info->description;
					$user->location = $this->twconnect->tw_user_info->location;
					$user->web = resolve_url($this->twconnect->tw_user_info->url);

					$user->twitter_name = $this->twconnect->tw_user_info->name;
					$user->twitter_img_url = $this->twconnect->tw_user_info->profile_image_url;
					$user->twitter_verified = $this->twconnect->tw_user_info->verified;

					$user->picture_url = $user->twitter_img_url;
					$user->big_picture_url = resolve_url('https://api.twitter.com/1/users/profile_image?screen_name=' . $user->twitter_username . '&size=bigger');

				} else {
					// we failed to get extended user info, but we will try to get it differently via public api request

					$ok = set_all_info_from_social($user, 'twitter', $user->twitter_username, true);

					if ( ! $ok) {
						// we do not have twitter data
						$user->fullname = $user->twitter_username;
					}
				}


 				// Inserting user
				$ok = $this->db->insert('users', $user); // we don't have id yet...

				$user->id = $this->db->insert_id();

				$this->meet_WhoYouMeet_team($user);

				return $user;

				if ( ! $ok) {
					// Cannot insert user
					return false;
				}

			} // end of else - no such user, signing user up
		} else {
			// no $tw_user_id passed
			return false;
		}

	} // function login_twitter_user()


	/**
	 * 11b. Logs in / signs up linkedin user
	 */
	public function login_linkedin_user($in_user, $in_access_token) {
		if ($in_user) {
			$query = $this->db->where('linkedin_id', $in_user->id)->get('users');

			if ( $query->num_rows() === 1 ) {
				// user found
				$user = new dbUser;
				$user->copy($query->row());
				$user->password = ($user->password ? '***' : '');
				return $user;

			} else {
				// no such user, signing up
				$this->load->library('in_connect');
				$user = new dbFullUser;

				$user->linkedin_id = $in_user->id;

				// saving token - it will expire and there is no logic yet to track it
				$user->linkedin_token = $in_access_token['oauth_token'];
				$user->linkedin_token_secret = $in_access_token['oauth_token_secret'];

				// calculating expiration time (since UNIX epoch 1970/1/1 00:00:00) (linkedin returns expiration period in seconds)
				$user->linkedin_token_expires = time() + $in_access_token['oauth_authorization_expires_in'];

				// cutting the domain part away (it will cut any domain away), because 'linkedin_username' is the path part of the public URL (I just didn't know then...)
				$in_username = preg_replace('/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/', '', $in_user->publicProfileUrl);

				// cutting any parameters that can be there - maybe this is excessive
				$in_username = preg_replace('/(\?.*)/', '', $in_username);
				$user->linkedin_username = $in_username;

				// setting user info from linkedin data
				$user->firstname = (isset($in_user->firstName) ? $in_user->firstName : '');
				$user->lastname = (isset($in_user->lastName) ? $in_user->lastName : '');

				$user->fullname = (isset($in_user->formattedName) ? $in_user->formattedName : $user->firstname . ' ' . $user->lastname);
				$user->bio = (isset($in_user->headline) ? $in_user->headline : '');
				$user->location = (isset($in_user->location->name) ? $in_user->location->name : '');

				// can't get we address yet - will come here later
				// $user->web = resolve_url($this->twconnect->tw_user_info->url);

				$user->linkedin_name = $user->fullname;
				$user->linkedin_img_url = (isset($in_user->pictureUrl) ? $in_user->pictureUrl : '');

				$user->picture_url = $user->linkedin_img_url;

				// getting the big picture
				$all_pictures = $this->in_connect->in_get_user_pictures();
				if (isset($all_pictures->values[0])) {
					$user->big_picture_url = $all_pictures->values[0];
				} else {
					// no big picture - using the same picture
					$user->big_picture_url = $user->picture_url;
				}

				// Inserting user
				$ok = $this->db->insert('users', $user);

				$user->id = $this->db->insert_id();

				$this->meet_WhoYouMeet_team($user);

				return $user;

				if ( ! $ok) {
					// Cannot insert user
					return false;
				}

			} // end of else - no such user, signing user up
		} else {
			// no $in_user passed
			return false;
		}

	} // function login_linkedin_user()


}

/* End of file /application/models/model_users.php */