<?php

/**
 * This file is used in conjunction with the 'LinkedIn' class, demonstrating 
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
  define('DEMO_GROUP', '4010474');
  define('DEMO_GROUP_NAME', 'Simple LI Demo');
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
      
    case 'createPost':
      /**
       * Handle create post requests.
       */
      
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
   
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_POST['title'])) {
	      $response = $OBJ_linkedin->createPost(DEMO_GROUP, $_POST['title'], (empty($_POST['summary']) ? '' : $_POST['summary']));
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error creating post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error creating post: The title must be specified.";
      }
    	break;
    	
    case 'deletePost':
      /**
       * Handle delete post requests.
       */
      
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
   
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty ($_GET['nPostId'])) {
	      $response = $OBJ_linkedin->deletePost($_GET['nPostId']);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error deleting/flagging post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error deleting/flagging post: The post id must be specified.";
      }
    	break;
        
    case 'flagPost':
      /**
       * Handle flag post requests.
       */
       
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
   
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if((!empty($_GET['nPostId'])) && (!empty($_GET['nType']))) {
      	if($_GET['nType'] == 'job' || $_GET['nType'] == 'promotion') {
  	      $response = $OBJ_linkedin->flagPost($_GET['nPostId'], $_GET['nType']);
  	      if($response['success'] === TRUE) {
  	      	header('Location: ' . $_SERVER['PHP_SELF']);
  	      } else {
  	      	echo "Error flagging post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
  	      }
      	} else {
      		echo "Error flagging post: The given type must be one of: job, promotion.";
      	}
      } else {
      	echo "Error flagging post: The post id and flag type must be specified.";
      }
    	break;
      
    case 'followPost':
      /**
       * Handle follow post requests.
       */
      
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
   
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nPostId'])) {
	      $response = $OBJ_linkedin->followPost($_GET['nPostId'], TRUE);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error following post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error following post: The post id must be specified.";
      }
    	break;
    	
    case 'joinGroup':
      /**
       * Handle group join requests.
       */

      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      $response = $OBJ_linkedin->joinGroup($_GET['nGroupId']);
      if($response['success'] === TRUE) {
      	header('Location: ' . $_SERVER['PHP_SELF']);
      } else {
      	echo "Error joining group: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
      }
    	
   	  break;
   	
    case 'leaveGroup':
      /**
       * Handle group leave requests.
       */
	    
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      $response = $OBJ_linkedin->leaveGroup($_GET['nGroupId']);
      if($response['success'] === TRUE) {
      	header('Location: ' . $_SERVER['PHP_SELF']);
      } else {
      	echo "Error leaving group: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
      }
   	  break;
   	      
    case 'likePost':
      /**
       * Handle like post requests.
       */
       
	    // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nPostId'])) {
	      $response = $OBJ_linkedin->likePost($_GET['nPostId'], TRUE);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error liking post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error liking post: The post id must be specified.";
      }
    	break;
    	
    case 'removeSuggestedGroup':
      /**
       * Handle remove suggested group requests.
       */
       
	    // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nGroupId'])) {
	      $response = $OBJ_linkedin->removeSuggestedGroup($_GET['nGroupId']);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error removing suggested group: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error removing suggested group: The group id must be specified.";
      }
    	break;
    	
    case 'unlikePost':
      /**
       * Handle unlike post requests.
       */
       
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
      
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nPostId'])) {
	      $response = $OBJ_linkedin->likePost($_GET['nPostId'], FALSE);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error unliking post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error unliking post: The post id must be specified.";
      }
    	break;
    	
    case 'unfollowPost':
      /**
       * Handle unfollow post requests.
       */
      
      // check the session
      if(!oauth_session_exists()) {
        throw new LinkedInException('This script requires session support, which doesn\'t appear to be working correctly.');
      }
   
      $OBJ_linkedin = new LinkedIn($API_CONFIG);
      $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
      if(!empty($_GET['nPostId'])) {
	      $response = $OBJ_linkedin->followPost($_GET['nPostId'], FALSE);
	      if($response['success'] === TRUE) {
	      	header('Location: ' . $_SERVER['PHP_SELF']);
	      } else {
	      	echo "Error unfollowing post: <br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, TRUE) . "</pre>";
	      }
      } else {
      	echo "Error unfollowing post: The post id must be specified.";
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
          <title>Simple-LinkedIn Demo &gt; Groups</title>
          
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
          <h1><a href="/demo.php">Simple-LinkedIn Demo</a> &gt; <a href="<?php echo $_SERVER['PHP_SELF'];?>">Groups</a></h1>
          
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
          	// user is already connected
            $OBJ_linkedin = new LinkedIn($API_CONFIG);
            $OBJ_linkedin->setTokenAccess($_SESSION['oauth']['linkedin']['access']);
            $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
            
            // check if the viewer is a member of the test group
           	$response = $OBJ_linkedin->group(DEMO_GROUP, ':(relation-to-viewer:(membership-state))');
          	if($response['success'] === TRUE) {
          		$result         = new SimpleXMLElement($response['linkedin']);
          		$membership     = $result->{'relation-to-viewer'}->{'membership-state'}->code;
          		$in_demo_group  = (($membership == 'non-member') || ($membership == 'blocked')) ? FALSE : TRUE;
	            ?>
	            <ul>
	              <li><a href="#manage">Manage LinkedIn Authorization</a></li>
	              <li><a href="../demo.php#application">Application Information</a></li>
	              <li><a href="../demo.php#profile">Your Profile</a></li>
	              <li><a href="#groups">Groups API</a>
	                <ul>
	                  <li><a href="#groupMemberships">Group Memberships</a></li>
	                  <li><a href="#groupsSuggested">Suggested Groups</a></li>
                    <li><a href="#manageGroup">Manage '<?php echo DEMO_GROUP_NAME;?>' Group Membership</a></li>
	                  <?php 
	                  if($in_demo_group) {
	                    ?>
		                  <li><a href="#groupSettings">Group Settings</a></li>
		                  <li><a href="#groupPosts">Group Posts</a></li>
		                  <li><a href="#createPost">Create a Group Post</a></li>
			                <?php 
		                }
		                ?>
		              </ul>
		            </li>
		          </ul>
		          <?php 
    				} else {
    					echo "Error retrieving group membership information: <br /><br />RESPONSE:<br /><br /><pre>" . print_r ($response, TRUE) . "</pre>";
    				}
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
            ?>
            <form id="linkedin_revoke_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="revoke" />
              <input type="submit" value="Revoke Authorization" />
            </form>
            
            <hr />
          
            <h2 id="groups">Groups API:</h2>
                      
            <h3 id="groupMemberships">Groups Memberships:</h3> 
            
            <p>Groups you are a member of:</p>
            
            <?php 
            $response = $OBJ_linkedin->groupMemberships();
      			if($response['success'] === TRUE) {
      				$groups = new SimpleXMLElement($response['linkedin']);
      				if((int)$groups['total'] > 0) {
        				foreach($groups as $group) {
        					$gid         = $group->group->id;
                  $group_name  = $group->group->name;
          				?>
          				<div style=""><span style="font-weight: bold;"><?php echo $group_name;?></span>
          				<div style="margin: 0.5em 0 1em 2em;">
                    <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo LINKEDIN::_GET_TYPE;?>=leaveGroup&amp;nGroupId=<?php echo $gid;?>#groupMemberships">Leave</a>
                  </div>
                  <?php 
        				}
      				} else {
      				  // no group memberships
                echo '<div>You are not currently the member of any groups.</div>';
      				}
      			} else {
      			  // request failed
      			  echo "Error retrieving groups memberships: <br /><br />RESPONSE:<br /><br /><pre>" . print_r ($response, TRUE) . "</pre>";
      			}
      			?>
      			
      			<hr />
      			
      			<h3 id="groupsSuggested">Suggested Groups:</h3>
            
            <p>Groups that LinkedIn thinks you might be interested in:</p>
            
            <?php 
            $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
            $response = $OBJ_linkedin->suggestedGroups();
            if($response['success'] === TRUE) {
            	$suggested = new SimpleXMLElement($response['linkedin']);
            	$count = 1;
            	foreach($suggested as $group) {
            		$gid		    = (string)$group->id;
            		$group_name = $group->name;
            		$group_open = ($group->{'is-open-to-non-members'} == 'true') ? TRUE : FALSE;
              	?>
              	<div><span style="font-weight: bold;"><?php echo $group_name . ' (' . (($group_open) ? '' : 'not') . ' open to non-members)';?></span></div>
              	<?php 
                if($count == 1) {
                	$response = $OBJ_linkedin->group($gid, ':(id,name,short-description,relation-to-viewer:(membership-state,available-actions),is-open-to-non-members,category,contact-email)');
                	if($response['success'] === TRUE) {
                		echo '<h4>Group Details:</h4>
                          <pre>' . print_r(new SimpleXMLElement($response['linkedin']), TRUE) . '</pre>';
                	} else {
                		// request failed
                		echo "Error retrieving groups detailed information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response, true) . "</pre>";
                	}
                }
                ?>
  	            <div style="margin: 0.5em 0 1em 2em;">
                  <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo LINKEDIN::_GET_TYPE;?>=joinGroup&amp;nGroupId=<?php echo $gid;?>#groupsSuggested">Join</a>
                  <a href="<?php echo $_SERVER['PHP_SELF'];?>?<?php echo LINKEDIN::_GET_TYPE;?>=removeSuggestedGroup&amp;nGroupId=<?php echo $gid;?>#groupsSuggested">Remove</a>
    	       		</div>
    	       		<?php
	            	$count++;
            	}
            } else {
      			  // request failed
      			  echo "Error retrieving groups suggestions: <br /><br />RESPONSE:<br /><br /><pre>" . print_r ($response, TRUE) . "</pre>";
      			}
            ?>
            
            <hr />
            
      			<h3 id="manageGroup">Manage '<?php echo DEMO_GROUP_NAME;?>' Group Membership</h3>
      			
      			<?php 
      			if($in_demo_group) {
      			  // viewer is a member of the test group
      			  ?>						
      			  <form id="linkedin_connect_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
      		      <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="leaveGroup" />
      		      <input type="hidden" name="nGroupId" id="nGroupId" value="<php echo DEMO_GROUP;?>" />
      		  	  <input type="submit" value="Leave '<?php echo DEMO_GROUP_NAME;?>' Group" />
              </form>
      			
      			  <hr />
      			  
      			  <h3 id="groupSettings">Group Settings for '<?php echo DEMO_GROUP_NAME;?>' Group</h3>
      			  
      				<?php 
      				$response = $OBJ_linkedin->groupSettings(DEMO_GROUP, ':(show-group-logo-in-profile,contact-email,email-digest-frequency,email-announcements-from-managers,allow-messages-from-members,email-for-every-new-post)');
      				if($response['success'] === TRUE) {
      					$settings      = new SimpleXMLElement($response['linkedin']);
      					$show_logo     = $settings->{'show-group-logo-in-profile'};
      					$digest        = $settings->{'email-digest-frequency'}->code;
      					$announcements = $settings->{'email-announcements-from-managers'};
      					$messages      = $settings->{'allow-messages-from-members'};
      					$new_post      = $settings->{'email-for-every-new-post'};
        				?>
      					<div>Show Logo in Profile: <?php echo $show_logo;?></div>
      					<div>Email for Every New Post: <?php echo $new_post;?></div>
      					<div>Email Announcements from Managers: <?php echo $announcements;?></div>
                <div>Allow messages from Group Members: <?php echo $messages;?></div>
      					<div>Digest Email Frequency: <?php echo $digest;?></div>
        				<?php 
      				} else {
      				  // request failed
      					echo "Error retrieving group settings:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";                	
      				}
      				?>
				
				      <hr />
				
				      <h3 id="groupPosts">'<?php echo DEMO_GROUP_NAME;?>' Group Discussions</h3>
				      
				      <?php
              $post_cats = array('discussion', 'job', 'promotion'); 
				      foreach($post_cats as $post_cat) {
				        echo '<h4>' . ucfirst($post_cat) . 's</h4>';
				        
				        $OBJ_linkedin->setResponseFormat(LINKEDIN::_RESPONSE_XML);
  				      $count = 1;
    			      $response = $OBJ_linkedin->groupPosts(DEMO_GROUP, ':(id,creator:(first-name,last-name),title,summary)?order=recency&category=' . $post_cat);
  					    if($response['success'] === TRUE){
      						$posts = new SimpleXMLElement($response['linkedin']);
      						if((int)$posts['total']) {
      						  echo '<ol>';
      							foreach($posts as $post) {
      		            echo '<li>';
      		
          						$pid        = (string)$post->id;
      								$title      = $post->title;
      								$summary    = $post->summary;
      								$first_name = $post->creator->{'first-name'};
      								$last_name  = $post->creator->{'last-name'};
      								$creator    = $first_name . ' ' . $last_name;
      								$response   = $OBJ_linkedin->groupPost((string)$pid, ":(relation-to-viewer:(is-liked,is-following))");
      								if($response['success'] === TRUE) {
      									$post_info = new SimpleXMLElement($response['linkedin']);
      									$like      = ($post_info->{'relation-to-viewer'}->{'is-liked'} == 'true') ? TRUE : FALSE;
      									$follow    = ($post_info->{'relation-to-viewer'}->{'is-following'} == 'true') ? TRUE : FALSE;
      									
      									?>
      		              <div><?php echo '<span style="font-weight: bold;">Title:</span> ' . $title;?></div>
                        <div><?php echo '<span style="font-weight: bold;">Summary:</span> ' . $summary;?></div>
                        <div><?php echo '<span style="font-weight: bold;">Creator:</span> ' . $creator;?></div>
                        <div><?php echo '<span style="font-weight: bold;">Post ID:</span> ' . $pid;?></div>
       								
                        <?php 
        								if($count == 1) {
        									$response = $OBJ_linkedin->groupPostComments($pid, ':(id,text,creator:(first-name,last-name))');
        									if($response['success'] === TRUE) {
        	            			echo '<h2>Comments:</h2><pre>' . print_r(new SimpleXMLElement($response['linkedin']), TRUE) . '</pre>';	
        									} else {
        										echo "Error retrieving group post comments:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";                	
        									}
        								}
        								?>
    
                        <div style="margin: 0.5em 0 1em 2em;">
          								<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=' . (($like) ? 'unlikePost' : 'likePost') . '&nPostId='.$pid;?>#groupPosts"><?php echo ($like) ? 'Unlike' : 'Like';?></a>
          								<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=' . (($follow) ? 'unfollowPost' : 'followPost') . '&nPostId='.$pid;?>#groupPosts"><?php echo ($follow) ? 'Unfollow' : 'Follow';?></a>
          								<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=flagPost&nType=job&nPostId='  . $pid;?>#groupPosts">Flag as Job</a>
          								<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=flagPost&nType=promotion&nPostId=' . $pid;?>#groupPosts">Flag as Promotion</a>
          								<a href="<?php echo $_SERVER['PHP_SELF'] . '?' . LINKEDIN::_GET_TYPE . '=deletePost&nPostId=' . $pid;?>#groupPosts">Delete/Flag as Inappropriate</a>
        								</div>
      									<?php
      								} else {
      								  // request failed
      									echo "Error retrieving additional post information:<br /><br />RESPONSE:<br /><br /><pre>" . print_r($response) . "</pre>";                	
      								}
      								$count++;
      								
      								echo '</li>';
      							}
      							echo '</ol>';
      						} else {
      							echo 'There are no ' . $post_cat . 's.';
      						}
      					}
    					}
    					?>
			
        		  <hr />
        		  
              <h3 id="createPost">Create a Post in the '<?php echo DEMO_GROUP_NAME;?>' Group:</h3>
              	
			        <form id="create_post_form" action="<?php echo $_SERVER['PHP_SELF'];?>#createPost" method="post">
	              <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="createPost" />
	  
	              <div style="font-weight: bold;">Title:</div>            
	              <input type="text" name="title" id="title" length="255" maxlength="255" style="display: block; width: 400px;" />
	              
	              <div style="font-weight: bold;">Summary:</div>
	              <textarea name="summary" id="summary" rows="4" style="display: block; width: 400px;"></textarea>
	              <input type="submit" value="Create Post" />
  
              </form>
				      <?php 
			      } else {
			        // user isn't a member
		          ?>
		          <hr />

        			<form id="linkedin_join_form" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get">
                <input type="hidden" name="<?php echo LINKEDIN::_GET_TYPE;?>" id="<?php echo LINKEDIN::_GET_TYPE;?>" value="joinGroup" />
                <input type="submit" value="Join '<?php echo DEMO_GROUP_NAME;?>' Group" />
              </form>
              <?php
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