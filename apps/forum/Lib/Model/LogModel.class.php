<?php 
class LogModel extends Model{
	var $tableName = 'forum_log';
	function put($uid,$class,$content){
		$this->uid     = $uid;
		$this->fid   = intval($class);
		$this->content = $content;
		$this->ip 	= get_client_ip();
		$this->cTime   = time();
		unset($_REQUEST['act']);
		unset($_REQUEST['mod']);
		unset($_REQUEST['app']);
		unset($_REQUEST['hash']);
		unset($_REQUEST['token']);
		$this->param   = serialize($_REQUEST);
		
		$result = $this->add();
		return $result;
	}
	
	function addLog($uid,$class,$content,$map){
		$this->uid     = $uid;
		$this->fid   = intval($class);
		$this->content = $content;
		$this->ip 	= get_client_ip();
		$this->cTime   = time();
		$this->param  = serialize($map);
		$result = $this->add();
		return $result;
	}
}
?>