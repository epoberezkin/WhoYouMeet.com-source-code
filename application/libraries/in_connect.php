<?php

/**
 * Eugene Poberezkin, 2012.
 *
 * License: CC BY 3.0 (http://creativecommons.org/licenses/by/3.0/)
 *
 * This license means you can do almost whatever you like with it
 * attributing it to the original source.
 *
 *---------------------------------------------------------------
 * File /application/libraries/inconnect.php
 * 
 * Inconnect library for Codeigniter - for LinkedIn OAuth
 *
 * In connect library simplifies LinkedIn login/signup/access in CodeIgniter
 *---------------------------------------------------------------
 *
 * Encapsulates Simple-LinkedIn PHP v 3.2.0 library Paul Mennega (http://www.linkedin.com/in/paulmennega).
 * This library can be downloaded here - http://code.google.com/p/simple-linkedinphp/
 *
 * Simple-LinkedIn PHP library should be located in /application/libraries/SLIP/ folder
 *
 * This library in its turn needs OAuth library available here - http://code.google.com/p/oauth/
 * Put OAuth.php file in the same folder
 *
 * 
 * Everything is wrong below
 *
 *
 * Class variables
 * $in_ci - CI session
 * $in_config - LinkedIn configuration
 * $in_authorised - boolean, true if user is authorised
 * $in_user - the whole bunch of info about user
 *      in_get_user_profile() should be called to set it
 * $in_request_token - temporary request token, array
 * $in_access_token - permanent access token for the currently logged in user.
 *		It should be stored in the database.
 *		At least one of the previous two variables will be null.
 * $in_message - Error messages, not currectly used in application
 *
 *
 * Class functions:
 *	1.	public function In_connect()
 *		In_connect class constructor
 *		Creates Linkedin object for all 3 stages of authentication process
 *
 *	2.	public function in_redirect()
 *		redirects to Linkedin for authetication
 *
 *	3.	public function in_process_callback()
 *		requests permanent access token after callback
 *
 *	4.	public function in_revoke_authorization()
 *		revokes authorization
 *
 *	5.	public function in_get_user_profile()
 *		Downloads user profile
 *
 *	6.	public function in_get_user_pictures()
 *		Downloads URLs of user pictures
 *
 *	7.	public function in_get_person_info($pub_url)
 *		Donwloads public profile of any user
 *
 *	7a.	public function in_get_person_pictures($pub_url)
 *		Downloads URLs of any person pictures
 *
 *	8.	public function in_clear_session_data()
 *		Clears LinkedIn related session data
 *		
 */

include(APPPATH.'libraries/SLIP/linkedin_3.2.0.class.php');

class In_connect extends LinkedIn
{
	public $in_ci = null;
	public $in_config = null;
	public $in_authorised = false;
	public $in_user = null;
	public $in_request_token = null;
	public $in_access_token = null;
	public $in_message = '';

	/**
	 * 1. In_connect class constructor
	 *    Checks if we have access or request tokens in session
	 */
	public function In_connect() {
		/* load configuration from /application/config/linkedin.php file */
		$ci =& get_instance();
		$ci->config->load('linkedin', true);
		$config = $ci->config->item('linkedin');

		$config['callbackUrl'] = site_url($config['callbackUrl']);

		parent::__construct($config);

		$this->in_config = $config;

		$this->setResponseFormat(self::_RESPONSE_JSON);

		/* Save CodeIgniter instance for future use */
		$this->in_ci = $ci;

		/* getting linkedin state from user session data */
		$this->in_authorised = $ci->session->userdata('in_authorised');

		if ( $this->in_authorised) {
			// get access token from session
			$this->in_access_token = $ci->session->userdata('in_access_token');

			if ( (isset($this->in_access_token['oauth_token']) && isset ($this->in_access_token['oauth_token_secret'])) ) {
				// we have access token - we set them for LinkedIn object
				$this->setTokenAccess($this->in_access_token);

				// retrieving user information from session in case we have it
				$this->in_user = $ci->session->userdata('in_user');

			} else {
				// we do not have access token, it means we are not authorised...
				$this->in_authorised = false;
				$ci->session->set_userdata('in_authorised', false);

				// ... and we clear access token whatever it was
				$this->in_access_token = null;
				$ci->session->unset_userdata('in_access_token');
			}
		} else {
			// we are not authorised - set in into session, maybe it was empty
			$ci->session->set_userdata('in_authorised', false);

			// maybe we have request token? getting it from session
			$this->in_request_token = $ci->session->userdata('in_request_token');

			if ( ! (isset($this->in_request_token['oauth_token']) && isset ($this->in_request_token['oauth_token_secret'])) ) {
				// we do not have request token, we clear it whatever it was
				$this->in_request_token = null;
				$ci->session->unset_userdata('in_request_token');
			}
		}
	}


	/**
	 * 2. Redirects to LinkedIn for authorisation
	 * $callback_path - path in applicaiton to return after authentication
	 */
	public function in_redirect($callback_path = '') {
		if ( ! $this->in_authorised) {

			// set the callback url
			if ($callback_path === '') {
				$this->setCallbackUrl($this->in_config['callbackUrl']);
			} else {
				$this->setCallbackUrl(site_url($callback_path));
			}

			$response = $this->retrieveTokenRequest();
			if($response['success'] === true) {
				// store the request token
				$this->in_request_token = $response['linkedin'];
				$this->in_ci->session->set_userdata('in_request_token', $response['linkedin']);

				// redirect the user to the LinkedIn authentication/authorisation page to initiate validation.
				redirect(parent::_URL_AUTH . $this->in_request_token['oauth_token']);

			} else {
				$this->in_request_token = null;
				$this->in_ci->session->unset_userdata('in_request_token');

				$this->in_message = 'Cannot get request token. Try again.';
				return false;
			}
		} else {
			// Already authorized
			$this->in_message = 'LinkedIn is already authorized.';
			return false;
		}
	}


	/**
	 * 3. Processes data after control has been returned to application from LinkedIn
	 */
	public function in_process_callback() {

		$ci =& get_instance();

		// LinkedIn has sent a response, user has granted permission, take the temp access token, the user's secret and the verifier to request the user's real secret key
		$response = $this->retrieveTokenAccess($this->in_request_token['oauth_token'], $this->in_request_token['oauth_token_secret'], $_GET['oauth_verifier']);
		if($response['success'] === true) {
			// the request went through without an error, gather user's 'access' tokens
			$this->in_access_token = $response['linkedin'];
			$this->in_ci->session->set_userdata('in_access_token', $response['linkedin']);

			// set acess token and response format so we can continue using the same library object without reloading
			$this->setTokenAccess($this->in_access_token);
          	$this->setResponseFormat(self::_RESPONSE_JSON);

			// set the user as authorized for future quick reference
			$this->in_authorised = true;
			$this->in_ci->session->set_userdata('in_authorised', true);

			// get user information
			$this->in_get_user_profile();

			if ($this->in_user) {
				$this->in_ci->session->set_userdata('in_user', $this->in_user);
			}

			return true;

		} else {
			// bad access token, maybe not authorised
			$this->in_access_token = null;
			$this->in_ci->session->unset_userdata('in_access_token');

			$this->in_authorised = false;
			$this->in_ci->session->set_userdata('in_authorised', false);

			return false;
		}
	}


	/**
	 * 4. Revokes authorization
	 */
	public function in_revoke_authorization() {
		if ($this->in_authorised) {
			$response = $this->revoke();

			if($response['success'] === TRUE) {
				// revocation successful, clear session
				$this->in_clear_session_data();

				$this->in_request_token = null;
				$this->in_access_token = null;
				$this->in_authorised = false;
				$this->in_user = null;

				return true;
			} else {
				// revocation failed
				$this->in_last_connection_success = false;

				return false;
			}
		}
	}
	

	/**
	 * 5. Downloads user profile
	 * @return object - user profile or false
	 */
	public function in_get_user_profile() {
		if ($this->in_authorised) {
			$response = $this->profile('~:(id,first-name,last-name,formatted_name,headline,location:(name),picture-url,public-profile-url,site-standard-profile-request,primary-twitter-account,phone-numbers,bound-account-types)');
			if($response['success'] === true) {
				$this->in_user = json_decode($response['linkedin']);

				return $this->in_user;
			} else {
				// request failed
				$this->in_user = null;

				$this->in_message = 'Cannot get user profile. Please try again.';
				return false;
			}
		} else {
			$this->in_message = 'LinkedIn is not authorised.';
			return false;
		}
	}


	/**
	 * 6. Downloads URLs of user pictures
	 * @return object - user pictures URLs or false
	 */
	public function in_get_user_pictures() {
		if ($this->in_authorised) {
			$response = $this->profile('~/picture-urls::(original)');
			if($response['success'] === true) {

				return json_decode($response['linkedin']);
			} else {
				// request failed

				$this->in_message = 'Cannot get user pictures. Please try again.';
				return false;
			}
		} else {
			$this->in_message = 'LinkedIn is not authorised.';
			return false;
		}
	}


	/**
	 * 7. Donwloads public profile of any user
	 * @param string $pub_url - public profile URL
	 * @return object - user info or false
	 */
	public function in_get_person_info($pub_url) {

		if ( ! $this->in_authorised) {
			$access_token = array (
								'oauth_token' => $this->in_config['userKey'],
								'oauth_token_secret' => $this->in_config['userSecret']
							);
			$this->setTokenAccess($access_token);
		}

		$response = $this->profile('url='.urlencode($pub_url).':(id,first-name,last-name,formatted_name,headline,location:(name),picture-url,public-profile-url,site-standard-profile-request)');
		
		if($response['success'] === true) {
			return json_decode($response['linkedin']);
		} else {
			// request failed

			$this->in_message = 'Cannot get person\'s information. Please try again.';
			return false;
		}
	}


	/**
	 * 7a. Downloads URLs of any person pictures
	 * @param string $pub_url - public profile URL
	 * @return object - person pictures URLs or false
	 */
	public function in_get_person_pictures($pub_url) {

		if ( ! $this->in_authorised) {
			$access_token = array (
								'oauth_token' => $this->in_config['userKey'],
								'oauth_token_secret' => $this->in_config['userSecret']
							);
			$this->setTokenAccess($access_token);
		}

		$response = $this->profile('url='.urlencode($pub_url).'/picture-urls::(original)');
		if($response['success'] === true) {
			return json_decode($response['linkedin']);
		} else {
			// request failed
			$this->in_message = 'Cannot get person pictures. Please try again.';
			return false;
		}
	}


	/**
	 * 8. Clears LinkedIn related session data
	 */
	public function in_clear_session_data() {
		$this->in_ci->session->set_userdata('in_authorised', false);
		$this->in_ci->session->unset_userdata('in_authorized'); //
		$this->in_ci->session->unset_userdata('in_access_token');
		$this->in_ci->session->unset_userdata('in_request_token');
		$this->in_ci->session->unset_userdata('in_user');
	}

}

/* End of file /application/libraries/inconnect.php */