# cache


//set in config file

$config['cache_folder']=$config['documentroot']."/cache";

$config['cache'] = true;



$html = $cache->fetch();

if ( !$html )

{//start non-cache content

	html = this and that;
  
	$cache->save($html);
  
}//end non-cached content

echo $html; 

