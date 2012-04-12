<?php

// The full path to the XML file you exported from Wordpress
$xmlFile = 'wordpress-export.xml';

// Your tumblr log in details
$tumblr_email = 'mylogin';
$tumblr_password = 'mypassword';

// Tumblr URL (e.g. http://yourname.tumblr.com)
$tumblrUrl = 'http://mytumblr.tumblr.com';

// If a post from Wordpress is a draft, do you want it posted as private so you // have it available? True if so, False to ignore drafts
$publishDraftAsPrivate = true;

// Full path to a file that is writable, so that a log of current URL on your // wordpress blog to new URL on your tumblr can be written (good for redirects // to preserve links, etc)
$logFile = 'log.csv.txt';

// When the blog is a secondary blog. Keep empty if primary blog 
$tumblrGroup = 'mytumblr.tumblr.com';

//Remove keywords in post title
$removeTitleKeywords = array(  );

// the category which represent post type ( Article, Video, Lien )
$tumblogWPCategory = "tumblog";

$tumblogWPCategoryTermsMatches = array(
	"regular"	=> array("Article","Code"), 
	"video"		=> array("Video"),
	"link"		=> array("Lien"),
	"quote"		=> array("Citation"),
	"photo"		=> array("Image"),
	);

$relocateBodyImages = array( "http://www.myblog.com/wp-content/uploads/" => "http://media.myblog.com/tumblelog/");

// do not send data to Tumblr, first see the result in the console 
$testMode = false;

// to prevent saturation and ban from Tumblr
$rateLimit = 2; // seconds
