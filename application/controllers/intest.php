<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 *---------------------------------------------------------------
 * File /application/controllers/intest.php
 *
 * Test pages for in_connect.php library
 *---------------------------------------------------------------
 *
 */


if ( !defined('BASEPATH') ) exit('No direct script access allowed');


class Intest extends CI_Controller {

	public function index() {
		
		$this->load->library('in_connect');

		if ($this->in_connect->in_authorised) {
			echo 'Already connected</br>';
		} else {
			echo '<a href="'.base_url().'intest/connect">Connect to LinkedIn</a></br>';
		}
		echo '<a href="'.base_url().'intest/clear">Clear session data</a></br>';
		echo 'Session data:<br/><pre>';
		print_r($this->session->all_userdata());
		echo '</pre>';
	}


	public function connect() {
		$this->load->library('in_connect');
		$this->in_connect->in_redirect('/intest/callback');
	}


	public function callback() {
		$this->load->library('in_connect');
		$ok=$this->in_connect->in_process_callback();
		if ($ok) {
			redirect('/intest/success');
		} else {
			echo 'Could not connect to Linkedin<br/>';
			echo $this->in_connect->in_message;
		}
	}


	public function success() {
		$this->load->library('in_connect');
		$ok = $this->in_connect->in_get_user_profile();
		echo 'Successfully conencted to Linkedin ...<br />';
		if ($ok) {


			echo 'Token expires in: ', $this->in_connect->in_access_token['oauth_expires_in'], '<br/>';

			//$datetime = new DateTime;
			//$datetime = $datetime->add(new DateInterval('PT'.$this->in_connect->in_access_token['oauth_expires_in'].'S'));

			echo 'Time now: ' . time() . '<br />';
			echo 'Time Token expires: ' . (time() + $this->in_connect->in_access_token['oauth_expires_in']) . '<br />';


			$pub_url = preg_replace('/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/', '', $this->in_connect->in_user->publicProfileUrl);

			$pub_url = preg_replace('/(\?.*)/', '', $pub_url);

			echo 'Linkedin "username": "', $pub_url, '"<br/>';

			echo 'Connected user profile:<br /><pre>';
			print_r($this->in_connect->in_user);
			echo '</pre><br />';
			$pic = $this->in_connect->in_get_user_pictures();
			if ($pic) {
				echo 'Picture:<br /><pre>';
				print_r($pic);
				echo '</pre><br />';
				echo '<img src="' . $pic->values[0] . '" /><br/>';
			}
			echo '<a href="'.base_url().'intest/">Do it again</a><br />';

		} else {
			echo '... but cannot get user data<br />';
			echo '<a href="'.base_url().'intest/">Try again</a><br />';
			echo $this->in_connect->in_message;
		}
		echo 'User profile by public url ():<br /><pre>';
		$info = $this->in_connect->in_get_person_info('http://www.linkedin.com/pub/stephen-page/4/413/b6b');
		print_r($info);
		echo '</pre>';
		echo '<img src="' . $info->pictureUrl . '" /><br/>';

		$pub_url = preg_replace('/(\w{1,5}\:\/\/)?((\w*\.){1,3}\w*\/)?/', '', $info->publicProfileUrl);

		$pub_url = preg_replace('/(\?.*)/', '', $pub_url);

		echo 'Linkedin "username": "', $pub_url, '"<br/>';


	}


	public function failure() {

	}


	public function clear() {
		$this->load->library('in_connect');
		$this->in_connect->in_clear_session_data();
		redirect('/intest');
	}


	public function check() {

		$url = 'http://linkedin.com/in/iyfluydutysdytd';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_exec($ch);

		$info = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		echo '<pre>';
		print_r($info);
		echo '</pre>';

		curl_close($ch);

	}

}

?>