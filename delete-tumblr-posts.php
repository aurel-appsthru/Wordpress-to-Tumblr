<?php
if ( !file_exists( "config.php") ) 
{
	echo "Can't find the config file ! Rename and edit the config.sample.php file";
	exit;
}
require_once("config.php");

$request = array(
			'email' => $tumblr_email,
			'password' => $tumblr_password,
			'group' => $tumblrGroup,
			'num' =>  50
		);

$request_data = http_build_query( $request ) ;


do 
{
	// retrieve a chunk of items
	
	$c = curl_init($tumblrUrl.'/api/read');
	curl_setopt($c, CURLOPT_POST, true);
	curl_setopt($c, CURLOPT_POSTFIELDS, $request_data);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
	$result = curl_exec($c);
	$status = curl_getinfo($c, CURLINFO_HTTP_CODE);

	echo "\n";

	if ($status == 200) {
		echo "Success : posts retrieved !\n";

	} else if ($status == 403) {
		echo 'Bad email/password\n';
		exit();
	} else {
		
		echo "Error: $result\n";
		exit();
	}
	
	$xml = simplexml_load_string( $result );
		
	$total = $xml->posts->attributes()->total;
		 
	echo "Total items to delete : $total \n";	
	//if( $total == 0 ) exit();
	
	$posts = $xml->xpath('posts/post');
	 
	foreach( $posts as $post )
	{
		echo "Delete item " . $post->attributes()->id."\n";
	
		$postid = $post->attributes()->id;
	
		$request = array(
			'email' => $tumblr_email,
			'password' => $tumblr_password,
			'group' => $tumblrGroup,
			'post-id' =>  "$postid"
		);
	
		$request_data = http_build_query( $request ) ;	
	
		$c = curl_init('http://www.tumblr.com/api/delete');
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $request_data);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		$result = curl_exec($c);
		$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
	
		if ($status == 200) {
			echo "DELETE Success! \n";
		}
		else if ($status == 403) {
			echo 'Bad email/password\n';
		} else {
			echo "Error: $result\n";
		}

	}

}
while ( $total > 0 );

curl_close($c);
 


