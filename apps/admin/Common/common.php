<?php
function formatsize($fileSize) {
	$size = sprintf("%u", $fileSize);
	if($size == 0) {
		return("0 Bytes");
	}
	$sizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
	return round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}

//判断是否项目
	function isProject($gid) {
		$flag  = 0;
		$pro = D('Group', 'group') -> field('id') -> where('deputyorgid is not NULL') -> select();
		$pro = getSubByKey($pro, 'id');
		if (in_array($gid, $pro)) {
			$flag  = 1;
		}
		return $flag;
	}