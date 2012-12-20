<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/models/model_imeet.php
 * 
 * Model_imeet model class
 *
 * Handles database access to 'peopletomeet' table in the database
 * Used by i controller (main application controller) to display and edit people user wants to meet
 *
 * All class functions rely on controller to check if the user is logged in, they do no security check here.
 * Maybe it should be done here as well to improve security.
 *
 *
 * Classes
 *  1. dbPersonToMeet - all information about the person, matches fields in 'peopletomeettable'.
 *
 * Model_imeet class functions:
 *  2. get_iMeet_list($user_id) - retrieves and returns the list of people for a user
 *  3. public function add_personToMeet($user_id, $new_person) - adds person to meet to the user's list
 *  3a.private function connected_user_id($person) - finds user connected to a person by matching social profiles
 *  3b.private function connected_user_conditions($person) - puts together conditions for SQL query to filter out users who are connected to a person
 *  4. public function delete_personToMeet($user_id, $person_id) - deletes person to meet from the user's list
 *  5. public function get_personToMeet($user_id, $person_id) - retrieves and returns one person to meet
 *  6. public function update_personToMeet($user_id, $person_id, $person) - updates person to meet
 *
 */


/**
 * 1. All information about the person, matches fields in 'peopletomeettable'
 */
class dbPersonToMeet {
	var $id = 0;
	var $created = 0; // when the person was added, being implemented
	var $order = 0;  // reserved for future use to reorder people in the list
	var $userid = 0;
	var $usertomeetid = 0;
	var $private = false; // person user wants to meet will not see user, not currently used
	var $hidden = false; // user who I want to meet has hidden me from his "Who wants to meet me list", not currently used
	var $fullname = '';
	var $picture_url = '';
	var $big_picture_url = '';
	var $firstname = ''; // not used yet
	var $lastname = ''; // not used yet
	var $location = '';
	var $bio = '';
	var $email = '';

	/* LinkedIn info fields */
	var $linkedin_id = ''; // user id is a key (string) in LinkedIn, not a number
	var $linkedin_username = ''; // actually, it is path part of public url, so can be 'in/epoberezkin' or 'pub/stephen-page/4/413/b6b'
	var $linkedin_name = '';
	var $linkedin_img_url = '';

	/* Twitter info fields */
	var $twitter_id = 0;
	var $twitter_username = '';
	var $twitter_name = '';
	var $twitter_img_url = '';
	var $twitter_verified = false;

	/* Facebook info fields */
	var $facebook_id = 0;
	var $facebook_username = '';
	var $facebook_name = '';
	var $facebook_img_url = '';
	var $facebook_gender = '';

	var $web = '';
	var $reason = '';
	var $last_updated = 0; // when the record was created or last updated, managed by MySQL

	public function copy($data) { // $data is not necessary this class, but the same fields
		$this->id = $data->id;
		$this->created = $data->created; // when the person was added, being implemented
		$this->order = $data->order;
		$this->userid = $data->userid;
		$this->usertomeetid = $data->usertomeetid;
		$this->private = $data->private;
		$this->hidden = $data->hidden;
		$this->fullname = $data->fullname;
		$this->picture_url = $data->picture_url;
		$this->big_picture_url = $data->big_picture_url;
		$this->firstname = $data->firstname;
		$this->lastname = $data->lastname;
		$this->location = $data->location;
		$this->bio = $data->bio;
		$this->email = $data->email;

		/* LinkedIn info fields */
		$this->linkedin_id = $data->linkedin_id;
		$this->linkedin_username = $data->linkedin_username;
		$this->linkedin_name = $data->linkedin_name;
		$this->linkedin_img_url = $data->linkedin_img_url;

		/* Twitter info fields */
		$this->twitter_id = $data->twitter_id;
		$this->twitter_username = $data->twitter_username;
		$this->twitter_name = $data->twitter_name;
		$this->twitter_img_url = $data->twitter_img_url;
		$this->twitter_verified = $data->twitter_verified;

		/* Facebook info fields */
		$this->facebook_id = $data->facebook_id;
		$this->facebook_username = $data->facebook_username;
		$this->facebook_name = $data->facebook_name;
		$this->facebook_img_url = $data->facebook_img_url;
		$this->facebook_gender = $data->facebook_gender;

		$this->web = $data->web;
		$this->reason = $data->reason;
		$this->last_updated = $data->last_updated; // when the record was created or last updated, managed by MySQL
		return $this;
	}

	public function copy_from_user($data) { // $data is not necessary this class, but the same fields
		$this->created = $data->created; // when the person was added, being implemented
		$this->private = $data->private;
		$this->fullname = $data->fullname;
		$this->picture_url = $data->picture_url;
		$this->big_picture_url = $data->big_picture_url;
		$this->firstname = $data->firstname;
		$this->lastname = $data->lastname;
		$this->location = $data->location;
		$this->bio = $data->bio;
		$this->email = $data->email;

		/* LinkedIn info fields */
		$this->linkedin_id = $data->linkedin_id;
		$this->linkedin_username = $data->linkedin_username;
		$this->linkedin_name = $data->linkedin_name;
		$this->linkedin_img_url = $data->linkedin_img_url;

		/* Twitter info fields */
		$this->twitter_id = $data->twitter_id;
		$this->twitter_username = $data->twitter_username;
		$this->twitter_name = $data->twitter_name;
		$this->twitter_img_url = $data->twitter_img_url;
		$this->twitter_verified = $data->twitter_verified;

		/* Facebook info fields */
		$this->facebook_id = $data->facebook_id;
		$this->facebook_username = $data->facebook_username;
		$this->facebook_name = $data->facebook_name;
		$this->facebook_img_url = $data->facebook_img_url;
		$this->facebook_gender = $data->facebook_gender;

		$this->web = $data->web;
		$this->last_updated = $data->last_updated; // when the record was created or last updated, managed by MySQL
		return $this;
	}
}

class Model_imeet extends CI_Model {


	/**
	 * 2. Retrieves and returns the list of people for a user
	 */
	public function get_iMeet_list($user_id) {
		$sql = 'SELECT `peopletomeet`.*, `users`.`twitter_username` AS `usertomeet_twitter_username`, ';
		$sql .= '`users`.`facebook_username` AS `usertomeet_facebook_username`, ';
		$sql .= '`users`.`linkedin_username` AS `usertomeet_linkedin_username` ';
		$sql .= 'FROM `peopletomeet` ';
		$sql .= 'LEFT JOIN `users` ON `peopletomeet`.`usertomeetid` = `users`.`id` ';
		$sql .= 'WHERE peopletomeet.userid = ' . $user_id;
		$sql .= ' ORDER BY `peopletomeet`.`order` DESC';
		
		$query = $this->db->query($sql);

		if ( $query->num_rows() > 0 ) {
			return $query->result();
		} else return false;
	}


	/**
	 * 3. Adds person to meet to the user's list
	 */
	public function add_personToMeet($user_id, $new_person) {
		$new_person->userid = $user_id; // it's already the same, just in case I forgot somewhere :)

		$query = $this->db->query('SELECT MAX(`order`) as `max_order` FROM `peopletomeet`	WHERE `userid`=' . $user_id);

		// This is for future rearranging and for displaying last edited person on top
		$max_order = $query->row()->max_order;

		if (is_null($max_order)) {
			$new_person->order = 0;
		} else {
			$new_person->order = $max_order + 128;
		}

		// before inserting person we have to check if there is a connected user
		$new_person->usertomeetid = $this->connected_user_id($new_person);

		// now inserting person
		$ok =  $this->db->insert('peopletomeet', $new_person);
		$person_id = $this->db->insert_id();

		if ($ok) {
			return $person_id;
		} else {
			return false;
		}
	}


	/**
	 * 3a. Finds connected user by matching social profiles
	 * Always returns number, 0 if no user is connected
	 */
	private function connected_user_id($person) {
		$conditions = $this->connected_user_conditions($person);
		if ($conditions) {
			$sql = 'SELECT `users`.`id`, `users`.`twitter_id`, `users`.`twitter_username`, ';
			$sql .= '`users`.`linkedin_id`, `users`.`linkedin_username`, ';
			$sql .= '`users`.`facebook_id`, `users`.`facebook_username` ';
			$sql .= 'FROM `users` WHERE (' . $conditions . ')';
			$query = $this->db->query($sql);

			switch ($query->num_rows()) {
				case 0:
					// no user is connected, return current value or 0?
					return 0;
					break;
				case 1:
					// exactly one user is connected
					return $query->row()->id;
					break;
				default:
					// more than one user returned, we should choose one
					// if data is not corrupt there can be no more than 3 users returned
					// (because social profiles should be unique), but it is not assumed here
					$connected_id = $connected_linkedin = $connected_facebook = 0;
					foreach ($query->result() as $user_row) {
						// twitter, then linkedin, then facebook
						if ($user_row->twitter_id == $person->twitter_id ||
							$user_row->twitter_username == $person->twitter_username)
						{
							// twitter has priority
							$connected_id = $user_row->id;
							break; // first matched twitter is enough

						}
						elseif ( ( ! $connected_linkedin) &&
							($user_row->linkedin_id == $person->linkedin_id ||
							$user_row->linkedin_username == $person->linkedin_username))
						{
							$connected_linkedin = $user_row->id; //just remember it

						}
						elseif ( ( ! $connected_facebook) &&
							($user_row->facebook_id == $person->facebook_id ||
							$user_row->facebook_username == $person->facebook_username))
						{
							$connected_facebook = $user_row->id; //just remember it
						}
					}

					if ($connected_id) {
						return $connected_id;
					} elseif ($connected_linkedin) {
						return $connected_linkedin;
					} elseif ($connected_facebook) {
						return $connected_facebook; // only can be here if there are 2 facebook connected users (data is corrupt)
					} else {
						return 0; // we shouldn't be here as something matched
					}
			}
		} else {
			// return what it was without changing
			return $person->usertomeetid;
		}
	}


	/**
	 * 3b. Puts together conditions for SQL query to filter out users who are connected to a person
	 */
	private function connected_user_conditions($person) {
		// conditions to find out who wants to meet current user.
		// it is similar to meetMe_conditions() from model_users but no match by id and
		// the table is different so it is kept separately to avoid confusion

		$conditions = ''; // no match by id

		// matching linkedin profiles
		if ($person->linkedin_id) {
			$conditions .= "`users`.`linkedin_id` = '" . $person->linkedin_id . "'";
			$conditions .= " OR `users`.`linkedin_username` = '" . $person->linkedin_username . "'";
		}

		// or matching twitter profiles
		if ($person->twitter_id) {
			// checking if there is something already in $conditions (can be linkedin)
			if ($conditions != ''){
				$conditions .= ' OR ';
			}
			$conditions .= '`users`.`twitter_id` = ' . $person->twitter_id;
			$conditions .= " OR `users`.`twitter_username` = '" . $person->twitter_username . "'";
		}

		// or matching facebook profiles
		if ($person->facebook_id) {
			// checking if there is something already in $conditions (can be linkedin and/or twitter)
			if ($conditions != ''){
				$conditions .= ' OR ';
			}
			$conditions .= '`users`.`facebook_id` = ' . $person->facebook_id;
			$conditions .= " OR `users`.`facebook_username` = '" . $person->facebook_username . "'";
		}

		return $conditions;
	}


	/**
	 * 4. Deletes person to meet from the user's list
	 */
	public function delete_personToMeet($user_id, $person_id) {
		return $this->db->where('id', $person_id)->where('userid',$user_id)->delete('peopletomeet');
	}


	/**
	 * 5. Retrieves and returns one person to meet
	 */
	public function get_personToMeet($user_id, $person_id) {
		// $query = $this->db->where('id', $person_id)->where('userid',$user_id)->get('peopletomeet');
		
		$sql = 'SELECT `peopletomeet`.*, `users`.`twitter_username` AS `usertomeet_twitter_username`, ';
		$sql .= '`users`.`facebook_username` AS `usertomeet_facebook_username`, ';
		$sql .= '`users`.`linkedin_username` AS `usertomeet_linkedin_username` ';
		$sql .= 'FROM `peopletomeet` ';
		$sql .= 'LEFT JOIN `users` ON `peopletomeet`.`usertomeetid` = `users`.`id` ';
		$sql .= 'WHERE `peopletomeet`.`userid` = ' . $user_id . ' AND `peopletomeet`.`id` = ' . $person_id;
		
		$query = $this->db->query($sql);

		if ($query->num_rows() === 1) {
			// $person = new dbPersonToMeet;
			// $person->copy($query->row());
			return $query->row();
		} else return false;
	}


	/**
	 * 6. Updates person to meet
	 */
	public function update_personToMeet($user_id, $person_id, $person) {
		$person->userid = $user_id; // it's already the same, just in case I forget somewhere :)

		// This is for future rearranging and for displaying last edited person on top
		$query = $this->db->query('SELECT MAX(`order`) as `max_order` FROM `peopletomeet`	WHERE `userid`=' . $user_id);

		$max_order = $query->row()->max_order;

		// before updating person we have to check if there is a connected user
		$person->usertomeetid = $this->connected_user_id($person);

		if (is_null($max_order)) {
			$person->order = 0;
		} else {
			$person->order = $max_order + 128;
		}

		return $this->db->where('id', $person_id)->where('userid',$user_id)->update('peopletomeet', $person);
	}
}

/* End of file /application/models/model_imeet.php */