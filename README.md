# cache


//set in config file

$config['cache_folder']=$config['documentroot']."/cache";

$config['cache'] = true;


then in the area in your app you want to cache
$html = $cache->fetch();

if ( !$stuffToCache )
{//start non-cache content
	html = this and that;
	$cache->save($stuffToCache);
}//end non-cached content

echo $stuffToCache; 

