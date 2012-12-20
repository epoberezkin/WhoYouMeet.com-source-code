<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/helpers/MY_url_helper.php
 *
 * Extends url helper
 *---------------------------------------------------------------
 *
 * 1. function resolve_url($url) - Resolves all URL redirections
 * 2. function url_http_code($url) - Checks HTTP request code of the page, used to determine if public LinkedIn profile exist
 * 3. function my_auto_link - Converts more cases of links in text to clickable links
 * 4. function show_line_breaks($text = '') - Makes line breaks added in <textarea> visible
 * 5. function debug_var($var = 'no variable passed') - Prints varible to session so it is shown when any page that shows alerts is loaded (view_alerts.php)
 * 6. function user_profile_url($user = false) - Profile URL of the current or of any iser
 * 7. function connected_user_profile_url($person) - Profile URL of the user connected to a person (social profiles should be pre-retrieved from users table)
 */


/**
 * 1. Resolves all URL redirections
 * @param string $url - URL to resolve
 * @return string - final URL after all redirects
 */
function resolve_url($url){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_NOBODY, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_exec($ch);

	$url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

	curl_close($ch);

	return $url;
}


/**
 * 2. Checks HTTP request code of the page, used to determine if public LinkedIn profile exist
 * @param string $url - URL to check
 * @return int - HTTP code
 */
function url_http_code($url) {
	if ($url != '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);

		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return $code;

	} else {
		return false;
	}
}


/**
 * 2a. Loads info from URL
 * @param string $url - URL to check
 * @return object - retrieved data
 */
function url_get_contents($url) {
	if ($url != '') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 7);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if ($code == 200) {
			return $data;
		} else {
			return false;
		}

	} else {
		return false;
	}
}


/**
 * 3. Converts more cases of links in text to clickable links
 * In addition to auto_link() helper converts Twiiter names and domains without "www." and without "http://"
 * @param string $text
 * @return string
 */
function my_auto_link($text = '') {
	$text = auto_link($text);
	$text = preg_replace('/(^|\s)@([a-z0-9_]{1,20})($|\s)/i', '$1<a href="http://twitter.com/$2" target="_blank">@$2</a>$3', $text);
	$text = preg_replace('/(^|\s)([a-z0-9_\-]{1,20})\.(com|eu|co\.uk|net|org|gov|gov\.uk)($|\s)/i', '$1<a href="http://$2.$3" target="_blank">$2.$3</a>$4', $text);
	return $text;
}


/**
 * 4. Makes line breaks added in <textarea> visible
 * @param string $text
 * @return string
 */
function show_line_breaks($text = '') {
	return preg_replace('/\n\r?/', '</p><p>', $text);
}


/**
 * 5. Sets varible to session so it is shown when any page that shows alerts is loaded (view_alerts.php)
 * @param type $var
 */
function debug_var($var = 'no variable passed') {
	$ci =& get_instance();
	$ci->session->set_flashdata('error', '<pre>' . print_r($var, true) . '</pre>');
}


/**
 * 6. Profile URL of the current or of any iser
 * @param type $user
 * @return type
 */
function user_profile_url($user = false /* default - current user */) {
	if ($user) {
		if ($user->twitter_username) {
			return base_url() . $user->twitter_username;
		} elseif ($user->facebook_username) {
			return base_url() . 'users/fb/' . $user->facebook_username;
		} elseif ($user->linkedin_username) {
			return base_url() . 'users/in/' . $user->linkedin_username;
		} else {
			return base_url() . 'users/id/' . $user->id;
		}
	} else {
		$ci =& get_instance();
		$username = $ci->session->userdata('twitter_username');
		if ($username) {
			return base_url() . $username;
		} else {
			return base_url() . 'i/profile';
		}
	}
}


/**
 * 7. Profile URL of the user connected to a person (social profiles should be pre-retrieved from users table)
 * @param type $person
 * @return type
 */
function connected_user_profile_url($person) {
	if ($person->usertomeetid) {
		if (isset($person->usertomeet_twitter_username) && $person->usertomeet_twitter_username) {
			return base_url() . $person->usertomeet_twitter_username;
		} elseif (isset($person->usertomeet_facebook_username) && $person->usertomeet_facebook_username) {
			return base_url() . 'users/fb/' . $person->usertomeet_facebook_username;
		} elseif (isset($person->usertomeet_linkedin_username) && $person->usertomeet_linkedin_username) {
			return base_url() . 'users/in/' . $person->usertomeet_linkedin_username;
		} else {
			return base_url() . 'users/id/' . $person->usertomeetid;
		}
	} else {
		return base_url();
	}
}

/* End of file /application/helpers/MY_url_helper.php */