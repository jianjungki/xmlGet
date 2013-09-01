<?php
include('oracle.class.php');
class ExportModel extends Model{
	
	var $oracle;
	
	function __construct(){
		$this->oracle = new oracle();
	}
	
	//论坛下分类列表
	function getCategory( $root ) { 
		 $info = $this->oracle->Search('select CATEGORYID,NAME,LFT,RGT from jivecategory');
		 return $info;
	} 
	
	//论坛列表
	function getforumlist(){
		 $info = $this->oracle->Search('select FORUMID,NAME from jiveforum');
		 return $info;
	}
	
	function oraclequery($sql){
		return $this->oracle->ExecuteSQL($sql);
	}
	
	//主题列表
	function getThemeList( $board , $type ){
		if($type!='all'){
			$map = " AND TCLASS=".intval($type);
		}
		$info = $this->oracle->Search("select MESSAGEID,THREADID,USERID,tclass,SUBJECT,BODY,CREATIONDATE,IP FROM jivemessage WHERE topic=1 AND FORUMID=$board $map");
		//$info = $this->oracle->Search("select MESSAGEID,THREADID,USERID,tclass,SUBJECT,CREATIONDATE,IP FROM jivemessage WHERE topic=1 AND FORUMID=$board $map");
		
		foreach($info as $key=>$value){
			$info[$key]['post']            =  $this->getPostList( $value['MESSAGEID'] );
			$info[$key]['replycount']      =  count( $info[$key]['post'] );
			$info[$key]['CREATIONDATE']    =  $this->transferTime( $value['CREATIONDATE'] );
			$info[$key]['UID']             =  $this->initBBSUser( $value['USERID'] );
			$info[$key]['ATTACH']          =  $this->getAttachList( $info[$key]['UID'] ,$value['MESSAGEID'] );
		}
		return $info;
	}
	
	//回复列表
	function getPostList( $tid ){
		$info = $this->oracle->Search("select MESSAGEID,SUBJECT,BODY,USERID,CREATIONDATE,IP from jivemessage WHERE PARENTMESSAGEID=".$tid." ORDER by MESSAGEID ASC");
		//$info = $this->oracle->Search("select MESSAGEID,SUBJECT,USERID,CREATIONDATE,IP from jivemessage WHERE PARENTMESSAGEID=".$tid." ORDER by MESSAGEID ASC");
		foreach($info as $key=>$value){
			$info[$key]['CREATIONDATE']    =  $this->transferTime( $value['CREATIONDATE'] );
			$info[$key]['UID']             =  $this->initBBSUser( $value['USERID'] );
			$info[$key]['ATTACH']          =  $this->getAttachList( $info[$key]['UID'] , $value['MESSAGEID'] );
		}		
		return $info;
	}
	
	
	//获取分类
	function getcategorylist($forumid){
		$info = $this->oracle->Search("select type_id,type_name from jiveforumthreadtype where object_id=$forumid");
		return $info;
	}
	
	//激活用户
	private function initBBSUser( $userID ){
		$userinfo = M('')->query("select * FROM ts_3ms_acl_user where USER_ID=$userID");
		$userinfo = $userinfo[0];
		$webuser =  D('User')->where("uname='{$userinfo['USER_PORTAL_ID']}'")->find();
		if($webuser['uid']){
			return $webuser['uid'];
		}else{
			$hrbiInfo = D('BaseInfo','home')->get_Hrbi_Info_Data($userinfo['USER_PORTAL_ID']);			
			$regdata['uname']     = $userinfo['USER_PORTAL_ID'];
			$regdata['email']     = $userinfo['USER_PORTAL_ID'].'@huawei.com';
			$regdata['passwd']    = md5('huawei');
			$regdata['name']      = $hrbiInfo['englishname']['value'];
			$regdata['fullname']  = $hrbiInfo['username']['value'];
			$regdata['sex']       =  $hrbiInfo['usersex']['value'];
			$regdata['active']    =  1;
			$regdata['timezone']  =  'beijing';
			$regdata['regtime']   =  time();
			$regdata['lang']      =  'zh';
			$regdata['identity']  =  1;
			$id =  model('User')->add($regdata);
			return $id;
		}
	}
	
	//获取附件列表
	function getAttachList( $uid , $messageid ){
		$list = $this->oracle->Search("select * from jiveattachment where objectID=".$messageid);
		if( $list ){
			foreach($list as $key=>$value){
				$data[$key]['uid']             = $uid;
				$data[$key]['attach_type']     = 'bbs';
				$data[$key]['name']            = $value['FILENAME'];
				$data[$key]['savename']        = $value['ATTACHMENTID'].'.bin';
				$data[$key]['savepath']        = 'hw3ms_bbs/';
				$data[$key]['type']            = $value['CONTENTTYPE'];
				$data[$key]['size']            = $value['FILESIZE'];
				$data[$key]['extension']       = substr(strrchr($value['FILENAME'],"."),1);
				$data[$key]['mtime']           = $this->transferTime( $value['CREATIONDATE'] );
			}
			
			return $data;
		}
	}
	
	//转化时间
	function transferTime( $time ){
		return intval( substr( $time , 0 , 10 ) ) ;
	}	
	
}
?>