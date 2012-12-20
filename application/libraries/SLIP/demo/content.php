<?php

/**
 * This file is used in conjunction with the 'Simple-LinkedIn' class, demonstrating 
 * the basic functionality and usage of the library.
 * 
 * COPYRIGHT:
 *   
 * Copyright (C) 2011, fiftyMission Inc.
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a 
 * copy of this software and associated documentation files (the "Software"), 
 * to deal in the Software without restriction, including without limitation 
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, 
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.  
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING 
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS 
 * IN THE SOFTWARE.  
 *
 * SOURCE CODE LOCATION:
 * 
 *   http://code.google.com/p/simple-linkedinphp/
 *    
 * REQUIREMENTS:
 * 
 * 1. You must have cURL installed on the server and available to PHP.
 * 2. You must be running PHP 5+.  
 *  
 * QUICK START:
 * 
 * There are two files needed to enable LinkedIn API functionality from PHP; the
 * stand-alone OAuth library, and the Simple-LinkedIn library. The latest 
 * version of the stand-alone OAuth library can be found on Google Code:
 * 
 *   http://code.google.com/p/oauth/
 * 
 * The latest versions of the Simple-LinkedIn library and this demonstation 
 * script can be found here:
 * 
 *   http://code.google.com/p/simple-linkedinphp/
 *   
 * Install these two files on your server in a location that is accessible to 
 * this demo script. Make sure to change the file permissions such that your 
 * web server can read the files.
 * 
 * Next, make sure the path to the LinkedIn class below is correct.
 * 
 * Finally, read and follow the 'Quick Start' guidelines located in the comments
 * of the Simple-LinkedIn library file.   
 *
 * @version 3.2.0 - November 8, 2011
 * @author Paul Mennega <paul@fiftymission.net>
 * @copyright Copyright 2011, fiftyMission Inc. 
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License 
 */

/**
 * Session existance check.
 * 
 * Helper function that checks to see that we have a 'set' $_SESSION that we can
 * use for the demo.   
 */ 
function oauth_session_exists() {
  if((is_array($_SESSION)) && (array_key_exists('oauth', $_SESSION))) {
    return TRUE;
  } else {
    return FALSE;
  }
}

try {
  // include the LinkedIn class
  require_once('../linkedin_3.2.0.class.php');
  
  // start the session
  if(!session_start()) {
    throw new LinkedInException('This script requires session support, which appears to be disabled according to session_start().');
  }
  
  // display constants
  $API_CONFIG = array(
    'appKey'       => '<your application key here>',
	  'appSecret'    => '<your application secret here>',
	  'callbackUrl'  => NULL 
  );
  define('CONNECTION_COUNT', 20);
  define('PORT_HTTP', '80');
  define('PORT_HTTP_SSL', '443');
  define('UPDATE_COUNT', 10);

  // set index
  $_REQUEST[LINKEDIN::_GET_TYPE] = (isset($_REQUEST[LINKEDIN::_GET_TYPE])) ? $_REQUEST[LINKEDIN::_GET_TYPE] : '';
  switch($_REQUEST[LINKEDIN::_GET_TYPE]) {
    case 'initiate':
      /**
       * Handle user initiated LinkedIn connection, create the LinkedIn object.
       */
        
      // check for the correct http protocol (i.e. is this script being served via http or https)
      if($_SERVER['HTTPS'] == 'on') {
        $protocol = 'https';
      } else {
        $protocol = 'http';
      }
      
      // set the callback url
      $API_CONFIG['callbackUrl'] = $protocol . '://' . $_SERVER['SERVER_NAME'] . ((($_SERVER['SERVER_PORT'] != PORT_HTTP) || ($_SERVER['SERVER_PORT'] != PORT_HTTP_SSL)) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=initiate&' . LINKEDIN::_GET_RESPONSE . '=1';
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      
      // check for response from LinkedIn
      $_GET[LINKEDIN::_GET_RESPONSE] = (isset($_GET[LINKEDIN::_GET_RESPONSE])) ? $_GET[LINKEDIN::_GET_RESPONSE] : '';
      if(!$_GET[LINKEDIN::_GET_RESPONSE]) {
        // LinkedIn hasn't sent us a response, the user is initiating the connection
        
        // send a request for a LinkedIn access token
        $response = $OBJ_linkedin->retrieveTokenRequest();
        if($response['success'] === TRUE) {
          // store the request token
          $_SESSION['oauth']['linkedin']['request'] = $response['linkedin'];
          
          // redirect the user to the LinkedIn authentication/authorisation page to initiate validation.
          header('Location: ' . LINKEDIN::_URL_AUTH . $response['linkedin']['oauth_token']);
        } else {
          // bad token request
          echo "Request token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
        }
      } else {
        // LinkedIn has sent a response, user has granted permission, take the temp access token, the user's secret and the verifier to request the user's real secret key
        $response = $OBJ_linkedin->retrieveTokenAccess($_SESSION['oauth']['linkedin']['request']['oauth_token'], $_SESSION['oauth']['linkedin']['request']['oauth_token_secret'], $_GET['oauth_verifier']);
        if($response['success'] === TRUE) {
          // the request went through without an error, gather user's 'access' tokens
          $_SESSION['oauth']['linkedin']['access'] = $response['linkedin'];
          
          // set the user as authorized for future quick reference
          $_SESSION['oauth']['linkedin']['authorized'] = TRUE;
            
          // redirect the user back to the demo page
          header('Location: ' . $_SERVER['PHP_SELF']);
        } else {
          // bad token access
          echo "Access token retrieval failed:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
        }
      }
      break;
      
    case 'revoke':
      /**
       * Handle authorization revocation.
       */
                    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      $response = $OBJ_linkedin->revoke();
      if($response['success'] === TRUE) {
        // revocation successful, clear session
        session_unset();
        $_SESSION = array();
        if(session_destroy()) {
          // session destroyed
          header('Location: ' . $_SERVER['PHP_SELF']);
        } else {
          // session not destroyed
          echo "Error clearing user's session";
        }
      } else {
        // revocation failed
        echo "Error revoking user's token:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
      }
      break;  
      
    case 'share':
      /**
       * Handle sharing.
       */
                    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      
      // prepare content for sharing
      $content = array();
      if(!empty($_POST['scomment'])) {
        $content['comment'] = $_POST['scomment'];
      }
      if(!empty($_POST['stitle'])) {
        $content['title'] = $_POST['stitle'];
      }
      if(!empty($_POST['surl'])) {
        $content['submitted-url'] = $_POST['surl'];
      }
      if(!empty($_POST['simgurl'])) {
        $content['submitted-image-url'] = $_POST['simgurl'];
      }
      if(!empty($_POST['sdescription'])) {
        $content['description'] = $_POST['sdescription'];
      }
      if(!empty($_POST['sprivate'])) {
        $private = TRUE;
      } else {
        $private = FALSE;
      }
      
      // share content
      $response = $OBJ_linkedin->share('new', $content, $private);
      if($response['success'] === TRUE) {
        // status has been updated
        header('Location: ' . $_SERVER['PHP_SELF']);
      } else {
        // an error occured
        echo "Error sharing content:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
      }
      break;
      
    case 'updateNetwork':
      /**
       * Handle network updates.
       */
                    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      $response = $OBJ_linkedin->updateNetwork($_POST['updateNetwork']);
      if($response['success'] === TRUE) {
        // status has been updated
        header('Location: ' . $_SERVER['PHP_SELF']);
      } else {
        // an error occured
        echo "Error posting network update:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
      }
      break;    
      
    default:
      // nothing being passed back, display demo page
      
      // check PHP version
      if(version_compare(PHP_VERSION, '5.0.0', '<')) {
        throw new LinkedInException('You must be running version 5.x or greater of PHP to use this library.'); 
      } 
      
      // check for cURL
      if(extension_loaded('curl')) {
        $curl_version = curl_version();
        $curl_version = $curl_version['version'];
      } else {
        throw new LinkedInException('You must load the cURL extension to use this library.'); 
      }
      ?>
      <!DOCTYPE html>
      <html lang="en">
        <head>
          <title>Simple-LinkedIn Demo &gt; Content</title>
          
          <meta charset="utf-8" />
          <meta name="viewport" content="width=device-width" />
          <meta name="description" content="A demonstration page for the Simple-LinkedIn PHP class." />
          <meta name="keywords" content="simple-linkedin,php,linkedin,api,class,library" />
          
          <style>
            body {font-family: Courier, monospace; font-size: 0.8em;}
            footer {margin-top: 2em; text-align: center;}
            pre {font-family: Courier, monospace; font-size: 0.8em;}
          </style>
        </head>
        <body>
          <h1><a href="/demo.php">Simple-LinkedIn Demo</a> &gt; <a href="<?php echo $_SERVER['PHP_SELF'];?>">Content</a></h1>
          
          <p>Copyright 2010 - 2011, Paul Mennega, fiftyMission Inc. &lt;paul@fiftymission.net&gt;</p>
          
          <p>Released under the MIT License - http://www.opensource.org/licenses/mit-license.php</p>
          
          <p>Full source code for both the Simple-LinkedIn class and this demo script can be found at:</p>
          
          <ul>
            <li><a href="http://code.google.com/p/simple-linkedinphp/">http://code.google.com/p/simple-linkedinphp/</a></li>
          </ul>          

          <hr />
          
          <p style="font-weight: bold;">Demo using: Simple-LinkedIn v<?php echo LINKEDIN::_VERSION;?>, cURL v<?php echo $curl_version;?>, PHP v<?php echo phpversion();?></p>
          
          <ul>
            <li>Please note: The Simple-LinkedIn class requires PHP 5+</li>
          </ul>
          
          <hr />
          
          <?php
          $_SESSION['oauth']['linkedin']['authorized'] = (isset($_SESSION['oauth']['linkedin']['authorized'])) ? $_SESSION['oauth']['linkedin']['authorized'] : FALSE;
          if($_SESSION['oauth']['linkedin']['authorized'] === TRUE) {
            ?>
            <ul>
              <li><a href="#manage">Manage LinkedIn Authorization</a></li>
              <li><a href="../demo.php#application">Application Information</a></li>
              <li><a href="../demo.php#profile">Your Profile</a></li>
              <li><a href="#content">Creating / Sharing Content</a>
                <ul>
                  <li><a href="#contentUpdate">Post Network Update</a></li>
                  <li><a href="#contentShare">Post Share</a></li>
                </ul>
              </li>
            </ul>
            <?php
          } else {
            ?>
            <ul>
              <li><a href="#manage">Manage LinkedIn Authorization</a></li>
            </ul>
            <?php
          }
          ?>
          
          <hr />
          
          <h2 id="manage">Manage LinkedIn Authorization:</h2>
          <?php
          if($_SESSION['oauth']['linkedin']['authorized'] === TRUE) {
            // user is already connected
            ?>
            <form id="linkedin_revoke_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="revoke" />
              <input type="submit" value="Revoke Authorization" />
            </form>
            
            <hr />

            <h2 id="content">Creating / Sharing Content</h2>
            
            <h3 id="contentUpdate">Post Network Update:</h3>
            <form id="linkedin_nu_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="updateNetwork" />
              <textarea name="updateNetwork" id="updateNetwork" rows="4" style="display: block; width: 400px;"></textarea>
              <input type="submit" value="Post Network Update" />
            </form>
            
            <hr />
            
            <h3 id="contentShare">Post Share:</h3>
            <form id="linkedin_share_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="share" />
              <div style="font-weight: bold;">Comment:</div>
              <textarea name="scomment" id="scomment" rows="4" style="display: block; width: 400px;"></textarea>
              
              <div style="font-weight: bold;">Title:</div>            
              <input type="text" name="stitle" id="stitle" length="255" maxlength="255" style="display: block; width: 400px;" value="" />
              
              <div style="font-weight: bold;">Content Url:</div>            
              <input type="text" name="surl" id="surl" length="255" maxlength="255" style="display: block; width: 400px;" value="" />
              
              <div style="font-weight: bold;">Content Picture Url:</div>            
              <input type="text" name="simgurl" id="simgurl" length="255" maxlength="255" style="display: block; width: 400px;" value="" />
              
              <div style="font-weight: bold;">Description:</div>
              <textarea name="sdescription" id="sdescription" rows="4" style="display: block; width: 400px;"></textarea>
              
              <input type="submit" value="Post Content" /><input type="checkbox" value="1" name="sprivate" id="sprivate" checked="checked" /><label for="sprivate">share with your connections only</label>
            </form>
            
            <?php
          } else {
            // user isn't connected
            ?>
            <form id="linkedin_connect_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="initiate" />
              <input type="submit" value="Connect to LinkedIn" />
            </form>
            <?php
          }
          ?>
          <footer>
            <div>Copyright 2010 - 2011, fiftyMission Inc. (Paul Mennega &lt;<a href="mailto:paul@fiftymission.net">paul@fiftymission.net</a>&gt;)</div>
            <div>Released under the MIT License - <a href="http://www.opensource.org/licenses/mit-license.php">http://www.opensource.org/licenses/mit-license.php</a></div>
          </footer>
        </body>
      </html>
      <?php
      break;
  }
} catch(LinkedInException $e) {
  // exception raised by library call
  echo $e->getMessage();
}

?>