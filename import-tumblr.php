<?php

if ( !file_exists( "config.php") ) 
{
	echo "Can't find the config file ! Rename and edit the config.sample.php file";
	exit;
}

require_once("config.php");

if( $logFile != "")
	file_put_contents($logFile,""); // to erase previous log file

include("functions.shortcodes.php");



// deals with [caption] shortcodes
add_shortcode("caption", "caption_handle");
function caption_handle( $atts , $content )
{
	$ret =  $content;
	return $ret;	
}

// deals with [sourcecode] shortcodes
add_shortcode("sourcecode", "sourcecode_handle");
function sourcecode_handle( $atts , $content )
{
	$prettylang = "";
	if ( $atts["language"] == "css") 
		$prettylang = "lang-css";
		
	$ret =  "<pre class=\"prettyprint $prettylang\"><code>".$content."</code></pre>";

	return $ret;
}


if (file_exists($xmlFile)) {

	$xml = @simplexml_load_file($xmlFile,"SimpleXMLElement",LIBXML_NOWARNING);

} else {

	echo "Can't open the Wordpress export file at $xmlFile ";

}

if (isset($xml)) {

	$nodes = $xml->xpath('/rss/channel/item');

	$count = 0;

	while(list( , $node) = each($nodes)) {


		//if ( $count > 2 ) break;//filter for debug
		$count++;

		//$post_type = 'regular';//default post type

		$post_title = ($node->title);
		$post_title = str_replace("%20"," ",$post_title);
		
		foreach ( $removeTitleKeywords as $kw )
		{
			$post_title = str_replace($kw,"",$post_title);
		}
		
		$post_title = trim($post_title);

		$content = $node->children("http://purl.org/rss/1.0/modules/content/");
		$post_body = (string)$content->encoded;

		$wp = $node->children("http://wordpress.org/export/1.1/");
 		
		$post_date =  $wp->post_date_gmt;
		
		$slug = (string)$wp->post_name;
		
		$categories = $node->category;
		
		$type = "";
		$tags = "";
		foreach ( $categories as $category ) 
		{
			foreach ( $category->attributes() as $attr => $value  ) 
			{				
				//define post type
				if( $attr == "domain" && $value == $tumblogWPCategory )
				{
					$type = $category;
				}
				
				//grab tags 
				if( $attr == "domain" && $value == "post_tag" )
				{
					$tags .= " \"".$category."\",";
				}				
			}
		}
		
		//if ( $type != "Code") continue;// filter for debug

		
		//apply shortcode substitution		
		$out = do_shortcode( $post_body );			
		$post_body = $out;			
		
		//relocate images
		$post_body = relocateImages( $post_body );
		
		//remove the excerpt keyword
		$post_body = str_replace("<!--more-->"," ",$post_body);
				
		
		if( in_array ( $type, $tumblogWPCategoryTermsMatches["video"] ))
		{
			$video = "";
			
			foreach ( $wp->postmeta as $postmeta  ) 
			{
				if( $postmeta->meta_key == "video-embed" )
				{					
					$video = (string)$postmeta->meta_value;
				}
			}		
			
			$post_body = "<p><strong>".$post_title."</strong></p><p>" .$post_body."</p>";
			$request_type = array(
			'type'=> 'video',
			'embed'=>$video,
			'caption'=>$post_body,
			);
		}
		elseif( in_array ( $type, $tumblogWPCategoryTermsMatches["link"] ))
		{			
			foreach ( $wp->postmeta as $postmeta  ) 
			{
				if( $postmeta->meta_key == "link-url" )
				{					
					$url = (string)$postmeta->meta_value;
				}
			}
			
			$request_type = array(
			'type'=> 'link',
			'name'=>$post_title,
			'url'=>$url,
			'description'=>$post_body,
			);
		}
		elseif( in_array ( $type, $tumblogWPCategoryTermsMatches["quote"] ))
		{
			$source = "";
			$url = "";
			
			foreach ( $wp->postmeta as $postmeta  ) 
			{
				
				if( $postmeta->meta_key == "quote-copy" )
				{					
					$quote = (string) $postmeta->meta_value;					 
				}
				
				if( $postmeta->meta_key == "quote-author" )
				{					
					$author = (string) $postmeta->meta_value;	
					
					$source = $author;
				}
				
				if( $postmeta->meta_key == "quote-url" )
				{					
					$url = (string) $postmeta->meta_value;										
				}
			}
			if( $url  !=  "" )
				$source = "<a href=\"$url\" >$source</a>"; 
			
			
			// ajoute une class source pour séparer le commentaire et l'auteur 
			if($post_body  !=  ""  )
				$source = $post_body ."<p><span class='source'>".  $source ."</span></p>"; 
			
			
			$request_type = array(
			'type'=> 'quote',
			'quote'=>$quote,
			'source'=>$source
			);
			
			
		}
		 
		
		elseif( in_array ( $type, $tumblogWPCategoryTermsMatches["photo"] ))
		{					
			
			foreach ( $wp->postmeta as $postmeta  ) 
			{
				//print_r( $postmeta );
				if( $postmeta->meta_key == "image" )
				{					
					$search = $postmeta->meta_value ;
					
					$c = preg_match_all( '/src="([^"]*)"/', $search, $matches);	 
					
					if( $c <= 0 )
					{
						//echo "image via src";
						$image = (string) "$search";
						
					}
					else 
					{
						//echo "image via regex";
						$image = $matches[1][0] ;
					}	
				}
			}
			
			$post_body = "<p><strong>".$post_title."</strong></p><p>" .$post_body."</p>";
			
			$request_type = array(
			'type'=> 'photo',
			'source'=>$image,
			'caption'=>$post_body,
			'click-through-url'=> "" 
			);
 
		}
		
		else //default
		//if( in_array ( $type, $tumblogWPCategoryTermsMatches["regular"] ))
		{						
						
			// deals with the "code" category
			$source = "";
			foreach ( $wp->postmeta as $postmeta  ) 
			{
				if( $postmeta->meta_key == "source-code" )
				{					
					$source = (string) $postmeta->meta_value;										
				}
			}
			// insert source code at the end 
			if( $source != "" ) 
				$post_body .= "\n<p><pre class=\"prettyprint\"><code>$source</code></pre></p>";
			
			$request_type = array(
			'type'=> 'regular',
			'title'=>$post_title,
			'body'=>$post_body );			
		}
		

		$private = 0;
		if ($wp->status != "publish") {

			if (!$publishDraftAsPrivate) {
				continue;
			}
			$private = 1;
		}

		
		$request = array(
			'email' => $tumblr_email,
			'password' => $tumblr_password,
			'generator'=> 'Appsthru.com - Worpress to Tumblr',
			'private'=>$private,
			'date' => "$post_date",
			'tags' => $tags,
			'slug' => $slug,
			
		);
		
		if ($tumblrGroup != "" )
			$request["group"] = $tumblrGroup ; 

		
		echo "\n-------------------\n";
		echo "$post_title\n";
		echo "Slug : $slug\n";
		
		//print_r( $request_type );
		//echo "\nTAGS : ". $tags."\n";
		
		$request = array_merge( $request, $request_type ) ;
		$request_data = http_build_query( $request ) ; 

		
		if( ! $testMode ) :
		
			$c = curl_init('http://www.tumblr.com/api/write');
			curl_setopt($c, CURLOPT_POST, true);
			curl_setopt($c, CURLOPT_POSTFIELDS, $request_data);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec($c);
			$status = curl_getinfo($c, CURLINFO_HTTP_CODE);
	
			curl_close($c);
			
			if ($status == 201) {
				echo "Success! Post ID: $result\n";
				if( $logFile != "" )
					$res = file_put_contents($logFile,$node->link . " ; " . $tumblrUrl . "/post/" . $result."\r\n", FILE_APPEND);
			} else if ($status == 403) {
				echo 'Bad email/password';
			} else {
				echo "Error: $result\n";
			}
		
		endif;
		
		sleep( $rateLimit ); // waiting 


	}

}


function relocateImages( $content )
{
	global $relocateBodyImages;
	foreach ( $relocateBodyImages as $source => $dest )
	{
		$content = str_replace($source,$dest,$content);
	}
	return $content ; 
}



