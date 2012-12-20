<?php
/**
 * Copyright 2012, Eugene Poberezkin. All rights reserved.
 * http://WhoYouMeet.com - here busy people choose who they meet
 *
 * File /application/views/includes/view_header.php
 *
 * Header. Included first in every page of the application
 *
 * Variable passed:
 *   $title - the title of the page (window)
 *
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Who You Meet: here busy people choose who they meet">
    <meta name="author" content="Who You Meet">

	<meta property="og:title" content="Who You Meet"/>
	<meta property="og:type" content="website"/>
	<meta property="og:url" content="http://whoyoumeet.com/"/>
	<meta property="og:image" content="http://beta.whoyoumeet.com/img/logo.png"/>
	<meta property="og:description" content="We help you meet more people you need!"/>

	<title><?=$title?></title>

	<link rel="shortcut icon" href="<?=base_url()?>favicon.ico" type="image/x-icon" />
	<link rel="icon" href="<?=base_url()?>favicon.ico" type="image/x-icon" />

	<link href="<?=base_url()?>css/bootstrap.css" rel="stylesheet">
	<!-- <link href="<?=base_url()?>css/bootstrap-responsive.css" rel="stylesheet"> -->
	<link href="<?=base_url()?>css/style.css" rel="stylesheet">

	<script src="<?=base_url()?>js/jquery-1.8.3.min.js"></script>

	<script src="<?=base_url()?>js/bootstrap.min.js"></script>

	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
	<!--[if lt IE 9]>
	<script src="<?=base_url()?>js/html5shiv.js"></script>
	<script src="<?=base_url()?>js/html5shiv-printshiv.js"></script>
	<![endif]-->
	
</head>
<body>

