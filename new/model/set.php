<?php

	function xCopy($source,$destination,$child){
		if(!is_dir($source)){
			echo ("Error:the $source is not a direction");
			return 0;
		}
		if(!is_dir($destination)){
			mkdir($destination,0777);
		}
		$handle = dir($source);
		while($entry=$handle->read()){
			if(($entry!=".")&&($entry!="..")){
				if(is_dir($source."/".$entry)){
					if($child)
						xCopy($source.'/'.$entry,$destination.'/'.$entry,$child);
				}else{
					copy($source.'/'.$entry,$destination.'/'.$entry);
				}
			}
		}
		return 1;
	}


	//创建文件目录结构
	function createFolder($path){
		if (!file_exists($path)){
			createFolder(dirname($path));    //return to lastpath
			mkdir($path, 0777); //if windows, seems set 0777 is useless
		}
	}
    
	
	$configfile = array(
			"v3",
			"tenc",
			"components/".'0f7bd68b296e4a508396fbe90dfade64'
		);
	$destin = 'gift';
	createFolder($destin);
	foreach($configfile as $value){
		if (!xCopy($value, $destin,true)) {
			echo "failed to copy $value...\n";
		}
	}
?>