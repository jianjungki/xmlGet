<?php

 class InterviewModel extends Model {
	  protected	$tableName	=	'weibo_character';
	  
	  
	  function getData($param) {
// 	  	D('Topic','weibo')->addTopic();
// 		$this->select();
	  	return $this->where($param)->order('topics_id')->limit(1)->select();
	  	 
	  }
	  
	
      }


?>