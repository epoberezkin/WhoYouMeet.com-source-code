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
  define('DEFAULT_JOB_SEARCH', 'Engineering');
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
      
      case 'bookmarkJob':
      /**
       * Handle job 'bookmarks'.
       */
                    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nJobId'])) {
        $response = $OBJ_linkedin->bookmarkJob($_GET['nJobId']);
        if($response['success'] === TRUE) {
          // job 'bookmarked'
          header('Location: ' . $_SERVER['PHP_SELF']);
        } else {
          // problem with 'bookmark'
          echo "Error 'bookmarking' job:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
        }
      } else {
        echo "You must supply a job ID to 'bookmark' a job.";
      }
      break;
      
      case 'unbookmarkJob':
      /**
       * Handle job 'unbookmarks'.
       */
                    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nJobId'])) {
        $response = $OBJ_linkedin->unbookmarkJob($_GET['nJobId']);
        if($response['success'] === TRUE) {
          // job 'unbookmarked'
          header('Location: ' . $_SERVER['PHP_SELF']);
        } else {
          // problem with 'unbookmark'
          echo "Error 'unbookmarking' job:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre><br /><br />LINKEDIN OBJ:<br /><br /><pre>" . print_r($OBJ_linkedin, TRUE) . "</pre>";
        }
      } else {
        echo "You must supply a job ID to 'unbookmark' a job.";
      }

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
          <title>Simple-LinkedIn Demo &gt; Jobs</title>
          
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
          <h1><a href="/demo.php">Simple-LinkedIn Demo</a> &gt; <a href="<?php echo $_SERVER['PHP_SELF'];?>">Jobs</a></h1>
          
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
              <li><a href="#jobs">Jobs API</a>
                <ul>
                  <li><a href="#jobsBookmarked">Bookmarked Jobs</a></li>
                  <li><a href="#jobsSuggested">Suggested Jobs</a></li>
                  <li><a href="#jobsSearch">Jobs Search</a></li>
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
            $OBJ_linkedin = new LinkedIn($API_CONFIG);
            $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
            ?>
            <form id="linkedin_revoke_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="revoke" />
              <input type="submit" value="Revoke Authorization" />
            </form>
            
            <hr />
            
            <h2 id="jobs">Jobs API:</h2>
            
            <h3 id="jobsBookmarked">Bookmarked Jobs:</h3>

            <p>Jobs that you currently have bookmarked:</p>
            
            <?php
            $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
            $response = $OBJ_linkedin->bookmarkedJobs();
            if($response['success'] === TRUE) {
              $bookmarked = new SimpleXMLElement($response['linkedin']);
              if((int)$bookmarked['total'] > 0) {
                $jobs = $bookmarked->{'job-bookmark'};
                foreach($jobs as $job) {
                  $jid    = $job->job->id;
                  $title  = $job->job->position->title;
                  $company = $job->job->company->name;
                  ?>
                  <div style=""><span style="font-weight: bold;"><?php echo $title.": ".$company;?></span></div>
                  <div style="margin: 0.5em 0 1em 2em;">
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo LINKEDIN::_GET_TYPE;?>=unbookmarkJob&amp;nJobId=<?php echo $jid;?>">Unbookmark</a>
                  </div>
                  <?php
                }
              } else {
                // no bookmarked jobs
                echo '<div>You have yet to bookmark any jobs.</div>';
              }
            } else {
              // request failed
              echo "Error retrieving followed companies:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";
            }
            ?>
            
            <hr />
            
            <h3 id="jobsSuggested">Suggested Jobs:</h3>

            <p>Jobs that LinkedIn thinks you might be interested in:</p>
            
            <?php
            $field_selectors = ':(id,customer-job-code,active,posting-date,expiration-date,posting-timestamp,company:(id,name),position:(title,location,job-functions,industries,job-type,experience-level),skills-and-experience,description-snippet,description,salary,job-poster:(id,first-name,last-name,headline),referral-bonus,site-job-url,location-description)';
            $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
            $response = $OBJ_linkedin->suggestedJobs(":(jobs:(id,company,position:(title)))");
            if($response['success'] === TRUE) {
              $suggested  = new SimpleXMLElement($response['linkedin']);
              $jobs       = $suggested->jobs;
              if((int)$jobs['total'] > 0) {
              	$job   = $jobs->job;
              	$count = 1;
                foreach($job as $job) {
                  $jid          = $job->id;
                  $title        = (string)$job->position->title;
                  $company      = $job->company->name;
                  $poster       = $job->{'job-poster'};
                  $description  = $job->{'description-snippet'};
                  $location     = $job->{'location-description'};
                  ?>
                  <div style=""><span style="font-weight: bold;"><?php echo $title.": ".$company;?></span></div>
                  <?php 
                  if($count == 1) {
                  	$response = $OBJ_linkedin->job((string)$jid, $field_selectors);
                  	if($response['success'] === TRUE) {
                  		echo '<h4>Job Details:</h4>
                            <pre>' . print_r(new SimpleXMLElement($response['linkedin']), TRUE) . '</pre>';
                  	} else {
                  		// request failed
                  		echo "Error retrieving jobs detailed information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, true) . "</pre>";
                  	}
                  }
                  ?>
                  <div style="margin: 0.5em 0 1em 2em;">
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo LINKEDIN::_GET_TYPE;?>=bookmarkJob&amp;nJobId=<?php echo $jid;?>">Bookmark</a>
                  </div>
                  <?php
                  $count++;
                }
              } else {
                // no jobs suggested
                echo '<div>There are no suggested jobs for you at this time.</div>';
              }
            } else {
              // request failed
              echo "Error retrieving suggested companies:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";
            }
            ?>
            
            <hr />
 
            <h3 id="jobsSearch">Job Search (by Relationship):</h3>
            
            <?php
            $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_JSON); 
            $keywords = (isset($_GET['keywords'])) ? $_GET['keywords'] : DEFAULT_JOB_SEARCH;	
            ?>
      			<form action="<?php echo $_SERVER['PHP_SELF']?>#jobsSearch" method="get">
      				Search by Keywords: <input type="text" name="keywords" value="<?php echo $keywords;?>" /><input type="submit" value="Search" />
      			</form>
			
            <?php
          	$query    = '?keywords=' . urlencode($keywords) . '&sort=R';
          	$response = $OBJ_linkedin->searchJobs($query); 
          	if($response['success'] === TRUE) {
            	echo "<pre>" . print_r($response['linkedin'], TRUE) . "</pre>";
          	} else {
            	// request failed
            	echo "Error retrieving job search results:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";                
          	}
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