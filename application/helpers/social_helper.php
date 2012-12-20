<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/helpers/social_helper.php
 *
 * Functions to check, load and copy social profile to users/people to meet
 *---------------------------------------------------------------
 *
 *
 *	1.	function check_social_profile($social_network = '', $username = '')
 *		Checks if social profile exist
 *
 *	2.	function get_social_profile($social_network, $username = '')
 *		Downloads social profile
 *
 *	2a.	get_social_big_picture_url($social_network, $username = '')
 *		Retrieves url of the big user picture
 *
 *	3.	function copy_all_social_info(&$target_UP, $source_UP)
 *		Copies all social profiles information of either person or user
 *
 *	4.	function clear_social_info(&$user_or_person, $social_network)
 *		Clears social profile information
 *
 *	5.	function set_all_info_from_social(&$user_or_person, $social_network, $username = '', $new = false)
 *		Downloads social profile and sets all (or only social) user or person information from it
 *
 */


/**
 * 1. Checks if social profile exist
 * @param string $social_network - 'twitter', 'linkedin' or 'facebook'
 * @param string $username - facebook username, twitter screen_name or linkedin public profile path
 * @return boolean - true if exists, false if not
 */
function check_social_profile($social_network = '', $username = '') {
	if ($username) {
		switch ($social_network) {
			case 'twitter':
				$tw_data = url_get_contents('https://api.twitter.com/1/users/show.json?screen_name=' . $username);

				if ($tw_data) {
					$tw_data = json_decode($tw_data);
					return isset($tw_data->id) && isset($tw_data->screen_name);
				} else {
					// we could not check Twitter data via API, we will check them via web
					$code = url_http_code('http://twitter.com/' . $username);
					return $code == 200;
				}
				break;

			case 'linkedin':
				$code = url_http_code('http://linkedin.com/' . $username);
				return $code == 200;
				break;

			case 'facebook':
				$fb_graph = url_get_contents('https://graph.facebook.com/' . $username);

				if ($fb_graph) {
					$fb_graph = json_decode($fb_graph);
					return isset($fb_graph->id) && isset($fb_graph->first_name);
				} else {
					return false;
				}
				break;

			default:
				return false;
		}
	} else {
		return false;
	}
}


/**
 * 2. Downloads social profile
 * @param string $social_network - see 1.
 * @param string $username - see 1.
 * @return object - social profile information
 */
function get_social_profile($social_network, $username = '') {
	if ($username) {
		switch ($social_network) {
			case 'twitter':
				// Retrieving Twitter data via API
				$tw_data = url_get_contents('https://api.twitter.com/1/users/show.json?screen_name=' . $username);
				if ($tw_data) {
					$tw_data = json_decode($tw_data);
				}
				if (isset($tw_data->id) && isset($tw_data->screen_name)) {
					return $tw_data;
				} else {
					// we could not get Twitter data via API, we will get them from webpage
					$tw_page = url_get_contents('http://twitter.com/' . $username);
					if ($tw_page) {
						/*
						 * Exctracting:
						 *  id, screen_name, name, profile_image_url, verified
						 *  description (- difficult to extract, it has urls, maybe later), location, url
						 */

						// to extract profile_image_url (twitter_img_url)
						$tw_page_pattern = '/\<img\s+src\s*\=\s*\"(?P<profile_image_url>[^\"]+)\"\s+alt\s*\=\s*\"[^\"]+\"\s+class\s*\=\s*\"[^\"]*avatar[^\"]*\"[^\>]*\>\s*\<\/a\>';

						// to extract screen_name (twitter_username) and twitter_id
						$tw_page_pattern .= '\s*\<div\s+class\s*\=\s*\"[^\"]*profile\-card\-inner[^\"]*\"\s+data\-screen\-name\s*\=\s*\"(?P<screen_name>[^\"]+)\"\s+data\-user\-id\s*=\s*\"(?P<id>[0-9]+)\"\>';

						// to extract full name (twitter_name)
						$tw_page_pattern .= '\s*\<h1\s+class\s*\=\s*\"[^\"]*fullname[^\"]*\"\>\s*(?P<name>[^\<]+)\s*\<\/h1\>';

						$tw_page_pattern .= '/'; // finish pattern

						preg_match($tw_page_pattern, $tw_page, $data);

						// Extracting twitter data from match results
						$tw_data = null;
						$tw_data->id = (isset($data['id']) ? $data['id'] : '');
						$tw_data->screen_name = (isset($data['screen_name']) ? $data['screen_name'] : '');
						$tw_data->name = (isset($data['name']) ? $data['name'] : '');
						$tw_data->profile_image_url = (isset($data['profile_image_url']) ? $data['profile_image_url'] : '');

						// to extract location and url
						$tw_page_pattern .= '\s*\<p\s+class\=\"[^\"]*location\-and\-url[^\"]*\"\>';
						$tw_page_pattern .= '\s*\<span\s+class\=\"[^\"]*location[^\"]*\"\>\s*(?P<location>[^\<]+)\s*\<\/span\>';
						$tw_page_pattern .= '(\s*\<span\s+class\=\"[^\"]*divider[^\"]*\"\>[^\<]*\<\/span\>)?';
						$tw_page_pattern .= '\s*\<span\s+class\=\"[^\"]*url[^\"]*\"\>';
						$tw_page_pattern .= '\s*\<a[^\<]*href\=\"(?P<url>[^\"]+)\"\>[^\<]*\<\/a\>';
						$tw_page_pattern .= '/'; // finish pattern

						preg_match($tw_page_pattern, $tw_page, $data);

						// Extracting more twitter data from match results
						$tw_data->location = (isset($data['location']) ? $data['location'] : '');
						$tw_data->url = (isset($data['url']) ? $data['url'] : '');

						if ($tw_data->id && $tw_data->screen_name) {
							if (ENVIRONMENT == 'dev') {
								$ci =& get_instance();
								$ci->session->set_flashdata('success', 'Hoo-ya! Twitter API was not responding so we got data from the webpage :)');
							}
							return $tw_data;
						} else {
							// no twitter id or no screen_name
							return false;
						}
					} else {
						// cannot read webpage or 404 (no user or no connection)
						return false;
					}
				}
				break;

			case 'linkedin':
				// get linkedin information
				$ci =& get_instance();
				$ci->load->library('in_connect');

				// getting person's information - should check later if user is authenticated
				$in_user = $ci->in_connect->in_get_person_info('http://www.linkedin.com/' . $username);
				if ($in_user) {
					return $in_user;
				} else {
					return false;
				}
				break;

			case 'facebook':
				$fb_graph = url_get_contents('https://graph.facebook.com/' . $username);
				if ($fb_graph) {
					$fb_graph = json_decode($fb_graph);
				}
				if (isset($fb_graph->id) && isset($fb_graph->first_name)) { // make sure it's a person, not some object
					return $fb_graph;
				} else {
					return false;
				}
				break;

			default:
				return false;
		}
	} else {
		return false;
	}
}


/**
 * 2a. Retrieves url of the big user picture
 * @param string $social_network - see 1.
 * @param string $username - see 1.
 * @param bool $current_user - should be true for current user if asking for LinkedIn pictures
 * @return string - big user picture url
 */
function get_social_big_picture_url($social_network, $username = '', $current_user = false) {
	if ($username) {
		switch ($social_network) {
			case 'twitter':
				$url = resolve_url('https://api.twitter.com/1/users/profile_image?screen_name=' . $username . '&size=bigger');
				if ($url) {
					return $url;
				} else {
					return false;
				}
				break;

			case 'linkedin':
				$ci =& get_instance();
				$ci->load->library('in_connect');
				if ($current_user) {  // sorry for this hack with $current_user
					$all_pictures = $ci->in_connect->in_get_user_pictures();
				} else {
					$all_pictures = $ci->in_connect->in_get_person_pictures('http://www.linkedin.com/' . $username);
				}
				if (isset($all_pictures->values[0])) {
					$url = $all_pictures->values[0];
					return $url;
				} else {
					// no big picture - using the same picture
					return false;
				}
				break;

			case 'facebook':
				$url = 'https://graph.facebook.com/' . $username . '/picture?type=normal';
				return $url;
				break;

			default:
				return false;
		}
	} else {
		return false;
	}
}


/**
 * 3. Copies all social profiles information of either person or user
 * @param object $target_UP - copies to this user or person
 * @param object $source_UP - from this user or person
 */
function copy_all_social_info(&$target_UP, $source_UP) { // UP = user or person
	// copy twitter info
	$target_UP->twitter_username = $source_UP->twitter_username;
	$target_UP->twitter_id = $source_UP->twitter_id;
	$target_UP->twitter_name = $source_UP->twitter_name;
	$target_UP->twitter_img_url = $source_UP->twitter_img_url;
	$target_UP->twitter_verified = $source_UP->twitter_verified;

	// copy linkedin info
	$target_UP->linkedin_username = $source_UP->linkedin_username;
	$target_UP->linkedin_id = $source_UP->linkedin_id;
	$target_UP->linkedin_name = $source_UP->linkedin_name;
	$target_UP->linkedin_img_url = $source_UP->linkedin_img_url;

	//copy facebook info
	$target_UP->facebook_username = $source_UP->facebook_username;
	$target_UP->facebook_id = $source_UP->facebook_id;
	$target_UP->facebook_name = $source_UP->facebook_name;
	$target_UP->facebook_img_url = $source_UP->facebook_img_url;
	$target_UP->facebook_gender = $source_UP->facebook_gender;
}


/**
 * 4. Clears social profile information
 * @param object $user_or_person
 * @param string $social_network - see 1.
 * @return boolean - always true
 */
function clear_social_info(&$user_or_person, $social_network) {
		switch ($social_network) {
			case 'twitter':
				$user_or_person->twitter_username = '';
				$user_or_person->twitter_id = '';
				$user_or_person->twitter_name = '';
				$user_or_person->twitter_img_url = '';
				$user_or_person->twitter_verified = '';
				return true;
				break;

			case 'linkedin':
				$user_or_person->linkedin_username = '';
				$user_or_person->linkedin_id = '';
				$user_or_person->linkedin_name = '';
				$user_or_person->linkedin_img_url = '';
				return true;
				break;

			case 'facebook':
				$user_or_person->facebook_username = '';
				$user_or_person->facebook_id = '';
				$user_or_person->facebook_name = '';
				$user_or_person->facebook_img_url = '';
				$user_or_person->facebook_gender = '';
				return true;
				break;

			default:
				return false;
		}
}


/**
 * 5. Downloads social profile and sets all (or only social) user or person information from it
 * @param object $user_or_person
 * @param string $social_network - see 1.
 * @param string $username - see 1.
 * @param boolean $new - true if it is a new user/person, or false when it is existing user/person
 * @return boolean
 */
function set_all_info_from_social(&$user_or_person, $social_network, $username = '', $new = false) {
	if ($username) {
		switch ($social_network) {
			case 'twitter':

				if ($username != $user_or_person->twitter_username) {
					// get twitter information
					$tw_data = get_social_profile($social_network, $username);

					if (isset($tw_data->id) && $tw_data->id && isset($tw_data->screen_name) && $tw_data->screen_name) {
						// profile exists
						$user_or_person->twitter_username = $username;

						$user_or_person->twitter_id = $tw_data->id;
						$user_or_person->twitter_name = (isset($tw_data->name) ? $tw_data->name : '');
						$user_or_person->twitter_img_url = (isset($tw_data->profile_image_url) ? $tw_data->profile_image_url : '');
						$user_or_person->twitter_verified = (isset($tw_data->verified) ? $tw_data->verified : '');

						if ($new) {
							// setting person info from twitter data
							if ( ($user_or_person->fullname == '') && isset($tw_data->name) ) {
								$user_or_person->fullname = $tw_data->name;
							}

							if ( ($user_or_person->bio == '') && isset($tw_data->description)) {
								$user_or_person->bio = $tw_data->description;
							}

							if ( ($user_or_person->location == '') && isset($tw_data->location)) {
								$user_or_person->location = $tw_data->location;
							}

							if ( ($user_or_person->web == '') && isset($tw_data->url)) {
								$user_or_person->web = resolve_url($tw_data->url);
							}

							$user_or_person->picture_url = $user_or_person->twitter_img_url;
						}
						return true;

					} else {
						// can't  get twitter data
						return false;
					}
				} else {
					// same twitter username, no overwrite
					return true;
				}
				break;

			case 'linkedin':

				if ($username != $user_or_person->linkedin_username) {
					// get linkedin information
					$in_user = get_social_profile($social_network, $username);

					if ($in_user) {
						// we have linkedin data
						$user_or_person->linkedin_username = $username;
						$user_or_person->linkedin_id = (isset($in_user->id) ? $in_user->id : '');
						$user_or_person->linkedin_name = (isset($in_user->formattedName) ? $in_user->formattedName : '');
						$user_or_person->linkedin_img_url = (isset($in_user->pictureUrl) ? $in_user->pictureUrl : '');

						if (($user_or_person->firstname == '') && isset($in_user->firstName)) {
							$user_or_person->firstname = $in_user->firstName;
						}

						if (($user_or_person->lastname == '') && isset($in_user->lastName)) {
							$user_or_person->lastname = $in_user->lastName;
						}

						if ($new) {
							// setting person info from linkedin data

							if ($user_or_person->fullname == '') {
								$user_or_person->fullname = (isset($in_user->formattedName) ? $in_user->formattedName : $user_or_person->firstname . ' ' . $user_or_person->lastname);
							}

							if (($user_or_person->bio == '') && isset($in_user->headline)) {
								$user_or_person->bio = $in_user->headline;
							}

							if (($user_or_person->location == '') && isset($in_user->location->name)) {
								$user_or_person->location = $in_user->location->name;
							}

							// can't get web address from linkedin yet - will come here later
							// $user->web = '';

							if ($user_or_person->picture_url == '') {
								$user_or_person->picture_url = $user_or_person->linkedin_img_url;
							}
						}

						return true;

					} else {
						// can't  get linkedin data
						return false;
					}
				} else {
					// same linkedin username, no overwrite
					return true;
				}
				break;

			case 'facebook':

				if ($username != $user_or_person->facebook_username) {
					// get facebook info

					$fb_graph = get_social_profile($social_network, $username);

					if (isset($fb_graph->id) && isset($fb_graph->first_name)) { // make sure it's a person, not some object
						$user_or_person->facebook_username = $username;

						$user_or_person->facebook_id = $fb_graph->id;
						$user_or_person->facebook_name = (isset($fb_graph->name) ? $fb_graph->name : '');
						$user_or_person->facebook_img_url = 'https://graph.facebook.com/' . $username . '/picture';
						$user_or_person->facebook_gender = (isset($fb_graph->gender) ? $fb_graph->gender : '');

						if ($user_or_person->firstname == '') {
							$user_or_person->firstname = $fb_graph->first_name;
						}

						if (($user_or_person->lastname == '') && isset($fb_graph->last_name)) {
							$user_or_person->lastname = $fb_graph->last_name;
						}

						if ($new) {
							if ($user_or_person->fullname == '') {
								$user_or_person->fullname = (isset($fb_graph->name) ? $fb_graph->name : $fb_graph->first_name . ' ' . $user_or_person->lastname);
							}

							if ($user_or_person->picture_url == '') {
								$user_or_person->picture_url = $user_or_person->facebook_img_url;
							}
						}

						return $fb_graph;
					} else {
						// can't  get facebook data
						return false;
					}
				} else {
					// same facebook username, no overwrite
					return true;
				}
				break;

			default:
				return false;
		}
	} elseif ( ! $new) {
		// username is empty, but overwrite is true - we will clear social fields
		// a hack, really, out of lazyness to create another function...
		clear_social_info($user_or_person, $social_network);
		return true;
	} else {
		return false;
	}
}


/* End of file /application/helpers/social_helper.php */