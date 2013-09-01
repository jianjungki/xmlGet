<?php
class AdminLogModel extends Model {
	var $tableName = 'admin_log';
	
    function addLog($uid, $data, $temp, $type='admin'){
    	$map['uid']   = $uid;
    	$map['data']  = serialize($data);
    	$map['info']  = $_REQUEST['info'];
    	$map['temp']  = $temp;
    	$map['cTime'] = time();
    	$map['type']  = $type;
    	return $this->add($map);
    }
    
    function _tempList($list){
    	foreach ($list as &$v){
    		$v['data'] = $this->_tempOne($v);
    		if(empty($v['data'])){
    			unset($v);
    		}
    	}
    	
    	return $list;
    }
    
    function _tempOne($log){
    	static $_template;
    	if(empty($_template)){
    		$_template = require SITE_PATH.'/apps/forum/Lang/zh-cn/adminLog_temp.php';    	
    	}
    	
    	$temp = $_template[ $log['temp'] ];
    	if(empty($temp)){
    		return '';
    	}
    	
    	$actor = getUserName( $log['uid'] ).' ';
    	
    	$data = unserialize($log['data']);
    	if(empty($data)) {
    		return $actor.$temp;
    	}
    	
    	foreach ($data as $k=>$v){
    		$temp  = str_replace('{'.$k.'}', $v, $temp);
    	}
    	
    	return $actor.$temp;
    }	
}

?>