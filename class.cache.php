<?php


//usage 
/*
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

*/


class cache {
	private $hd_db_date;
	private $cache_file_name;
	private $cache_file_suffix;
	
	
	
	//set cache file name and find cif file database timestamp
	function __construct($cache_item = '', $query_array=false, $noComments=false) {
		$this->cache_file_suffix = '.cache';
		global $config;
		
		if (!$config['cache']){
			$this->purge();
			return;
		}
		$this->noComments= $noComments;
		$this->cache_item 	= $cache_item;
		$this->queryArray = $query_array;

		require_once('config/config.components.php');
		
		if (!isset($config['components'][$this->cache_item]['cachable'])	){	
			$this->cache_item_cachable = false;
		}
		else{
			$this->cache_item_cachable = $config['components'][$this->cache_item]['cachable'];
		}
						
		if (!$this->cache_item_cachable){return;}

		if (isset($config['cache'])){
			$cache_file_name = '';
			$filename = $this->getFileName();
			$this->cache_file_name = $config['cache_folder'].'/'.$this->cache_item.'^'.$filename.$this->cache_file_suffix;
			$this->expiry_timestamp = 	$this->get_expiry_timestamp();
		}
		
		global $purged;
		if (isset($purged)){
			$this->purge();
			$purged = true;
		}
			
			
	}
	
	
	function getFileName(){
		global $config;
		global $page;
		$deletes = array('time');
		if ($this->queryArray){$query_array = $this->queryArray;}
		else {$query_array = $page->query_array;}
		
		foreach ($deletes as $itemToDelFromQuery){
			unset($query_array[$itemToDelFromQuery])	;
		}		
		$fileName = hash('sha256',implode('',$query_array));
		return $fileName;

	}
	
	
	
	
	
	//fetch the cached file
	function fetch(){
		global $config;
		if (!$config['cache']){
			return false;
		}
		if (!$this->cache_item_cachable){
			return false;
		}
		//check file has expired
		if ($config['cache'] and !$this->item_expired()){
			if($this->noComments){
				$data = file_get_contents($this->cache_file_name);
			}
			else{
				$data = '<!--start cached content['.$this->cache_item.'] -->'.file_get_contents($this->cache_file_name).'<!--end cached content -->';
			}
			return $data;
		}else{		
			return false;		
		}
	}
	
	
	
	//save html cache file
	function save($data){	
		global $config;
		if ($config['cache'] and $this->cache_item_cachable){
			return file_put_contents($this->cache_file_name, $data);
		}
	}
	
	
	
	//delete all expired files
	function purge(){
		global $config;
		global $purged;
		if (!isset($purged)){$purged = false;}
		if ($purged){return;}
		
			$path = $config['cache_folder'].'/';
			$file_count = 0;
			if ($handle = opendir($path)) {
	    		while (false !== ($file = readdir($handle))) {
					
					if (is_file($path.$file)){$file_count++;}
					
					if ( $this->item_expired() and is_file($path . $file) and $path.$file == $this->cache_file_name) {
	           			unlink($path . $file);
	        		}
					if (!$config['cache'] and !$purged and is_file($path.$file) and strpos($path.$file, $this->cache_file_suffix)){
						//caching off purge everything
						unlink($path . $file);
					}
	    		}
	    	closedir($handle); 
			}
			if( $file_count == 0){$purged = true;}
	}
	
	
	function item_expired(){				
			if (!file_exists($this->cache_file_name)){ return true; }
			if ( filemtime($this->cache_file_name) < $this->expiry_timestamp){
				return true;
			}

			return false;		
	}
	
	
	function dataLastUpdated(){
		$db_resource = new db();
			$query = 'select `timestamp` from `day_totals` WHERE `runsOn` LIKE "'.$this->scopeEnd.'"'; 
			$db_resource->query($query );
			$db_results_array = $db_resource->fetch_array();
			$this->dataUpdated = $db_results_array[0]['timestamp'];
			return $this->dataUpdated;
	}
	
	
	function get_expiry_timestamp(){	
		global $config;
		//require_once($config['documentroot'].'/classes/class.db.php');
		return time()-$config['cache_lifetime'];
	}

	
}

?>