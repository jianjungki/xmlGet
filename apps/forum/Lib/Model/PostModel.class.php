<?php
class PostModel extends Model {
	var $tableName = 'forum_post';
	
	function getPostInfo($pid,$byTid = false) {
		
		if(!$byTid){
			$where = "a.pid=$pid";
			$info = $this->query ( "SELECT a.pid,a.isdel,a.maskId,b.tclass,a.fid,b.hide,b.lock,a.uid,a.tid,a.istopic,a.cTime,a.title as posttitle,a.content,b.tags as ttags,b.title,b.tclass,b.uid as tuid,a.attach FROM " . C ( 'DB_PREFIX' ) . "forum_post a LEFT JOIN " . C ( 'DB_PREFIX' ) . "forum_topic b on a.tid=b.tid WHERE  $where" );
		}else{
			$where = "a.tid=$pid";
			$info = $this->query ( "SELECT a.pid,b.maskId,b.isdel,a.fid,a.uid,b.lock,b.hide,a.tid,a.istopic,a.cTime,a.title as posttitle,a.content,b.tags as ttags,b.title,b.tclass,b.uid as tuid,a.attach FROM " . C ( 'DB_PREFIX' ) . "forum_post a LEFT JOIN " . C ( 'DB_PREFIX' ) . "forum_topic b on a.tid=b.tid WHERE $where" );
		}
		return $info [0];
	}
	function getPostInfos($pid,$byTid = false) {
		if(is_array($pid)){
			$where = " a.pid in (".implode(',',$pid).")";
		}else{
			$where = " a.pid={$pid}";
		}
		
		if(!$byTid){
			$info = $this->query ( "SELECT a.pid,a.maskId,a.fid,b.hide,b.lock,a.uid,a.tid,a.istopic,a.cTime,a.title as posttitle,a.content,b.tags as ttags,b.title,b.tclass,b.uid as tuid,a.attach FROM " . C ( 'DB_PREFIX' ) . "forum_post a LEFT JOIN " . C ( 'DB_PREFIX' ) . "forum_topic b on a.tid=b.tid WHERE  $where" );
		}else{
			$info = $this->query ( "SELECT a.pid,b.maskId,a.fid,a.uid,b.lock,b.hide,a.tid,a.istopic,a.cTime,a.title as posttitle,a.content,b.tags as ttags,b.title,b.tclass,b.uid as tuid,a.attach FROM " . C ( 'DB_PREFIX' ) . "forum_post a LEFT JOIN " . C ( 'DB_PREFIX' ) . "forum_topic b on a.tid=b.tid WHERE $where" );
		}
		return $info;
	}
}
?>