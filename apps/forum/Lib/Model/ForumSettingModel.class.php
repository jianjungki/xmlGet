<?php
class ForumSettingModel extends Model {
	protected $tableName = "forum_setting";
	protected static $setting = array ();
	
	//初始化版块设置
	public function initBasicSet($fid) {
		if(empty($fid)) {
			return false;
		}
		$isExist = $this->where('fid='.$fid)->find();
		if(empty($isExist)) {
			$add['fid'] = $fid;
			$attachInfo = model('Xdata')->get('forum:forum_setting');
			$add['attach_type'] = $attachInfo['attach_type'];
			$this->add($add);
		}
	}
	
	//2010-10-22
	public function addBasicSet() {
		$map ['fid'] = intval ( $_GET ['fid'] );
		//$map['score'] = implode(',',$_POST['score']);
		$map ['image_state'] = intval ( $_POST ['image'] );
		//	$map['truename_state'] = intval($_POST['truename']);
//		$map ['header_state'] = intval ( $_POST ['headpic'] );
//		if (intval ( $_POST ['dummy'] ) == 1) {
//			$map ['dummy_state'] = 1;
//			$map ['truename_state'] = 0;
//		} elseif (intval ( $_POST ['dummy'] ) == 0) {
//			$map ['dummy_state'] = 0;
//			$map ['truename_state'] = 1;
//		}
		$map ['dummy_state'] = intval ( $_POST ['dummy'] );
		$map ['position'] = intval ( $_POST ['position'] );
		//$map['affiche']	 = t($_POST['affiche']);
		//$map['rule']	 = t($_POST['rule']);
		$map ['attach_type'] = t ( $_POST ['attach_type'] );
		$map ['attach_size'] = abs(intval ( $_POST ['attach_size'] ));
		if (intval ( $_POST ['delthread'] ) == 0) {
			$map ['del_by_time'] = 0;
		} else {
			$map ['del_by_time'] = abs ( intval ( $_POST ['time'] ) );
		}
		//$map['special_thread_type'] = implode(',',$_POST['special_thread_type']);
		$r = $this->where ( 'fid=' . $map ['fid'] )->find ();
		if ($r) {
			$res = $this->where ( 'fid=' . $map ['fid'] )->save ( $map );
		} else {
			$res = D ( 'ForumSetting' )->add ( $map );
		}
		
		$uploadData = service ( 'Xattach' )->upload ( "system" );
		if ($uploadData ['status']) {
			foreach ( $uploadData ['info'] as $value ) {
				if ($value ['key'] == 'icon') {
					$data ['forum_icon'] = $value ['id'];
				}
				if ($value ['key'] == 'logo') {
					$data ['forum_logo'] = $value ['id'];
				}
			}
		}
		$data ['name'] = t ( $_POST ['name'] );
		$data ['forum_intro'] = t ( $_POST ['forumintro'] );
		if (D ( 'Forum' )->where ( 'fid=' . $map ['fid'] )->find ()) {
			$result = D ( 'Forum' )->where ( 'fid=' . $map ['fid'] )->save ( $data );
		} else {
			$result = D ( 'Forum' )->add ( $data );
		}
		if ($res || $result) {
			$cache = service ( "Cache" );
			$this->cleanCache($map['fid'],$cache);
			return ture;
		}
	}
	
	public function getBaseicSet($fid,$cache=true,$needNowBase=false){
		if(C ( 'MEMCACHED_ON' ) && $needNowBase == false){
			$cache = service ( "Cache" );
			$baseSet = $cache->getForumSetting($fid);
			if(!$baseSet){
				$baseSet = $this->_getBaseicSet($fid,$cache,$needNowBase);
				$cache->setForumSetting ( $fid, $baseSet );
			}
			return $baseSet;
		}else{
			return $this->_getBaseicSet($fid,$cache,$needNowBase);
		}
	}
	private function cleanCache($fid,$cache){
		$category = service ( 'Category' );
		$cate = $category->getSubCategoryID($fid);
		foreach($cate as $value){
			$cache->setForumSetting ( $value, $this->_getBaseicSet ( $value, false ) );
		}
	}
	
	//获取论坛的基本设置信息
	private function _getBaseicSet($fid, $cache = true, $needNowBase = false) {
		if(isset(self::$setting[$fid])) {
			return self::$setting[$fid];
		}
		$category = service ( 'Category' );
		$inputFid = $fid;
		$category = $category->getCategoryPath ( $fid, false );
		
		$timeSetting = $manageFid = $fid = getSubByKey ( $category, 'fid' );
		if ($needNowBase) {
			$baseSql = "SELECT forum.* 
					FROM ts_forum_setting as forum";
		} else {
			$baseSql = "SELECT basic.*,forum.name as name,forum.forum_logo as logo,forum.forum_icon as icon,forum.forum_intro as intro,forum.forum_manager as manager 
					FROM ts_forum as forum join ts_forum_setting as basic on basic.fid = forum.fid";
		}
		
		$forumModel = D ( 'Forum' );
		while ( ! empty ( $fid ) ) {
			$nowfid = array_pop ( $fid );
			$sql = $baseSql . " WHERE forum.fid = {$nowfid}";
			$data = M ()->query ( $sql );
			$data = $data [0];
			
			if (! empty ( $data )) {
				//版主继承上一级
				$data ['manager'] = array();
				while ( ! empty ( $manageFid ) ) {
					$tempFid = array_pop ( $manageFid );
					$map ['fid'] = $tempFid;
					$manager = $forumModel->where ( $map )->field ( 'forum_manager' )->find ();
					if (! empty ( $manager )) {
						$tempData = explode ( ',', $manager ['forum_manager'] );
						$data ['manager'] = array_merge ( $data ['manager'], $tempData );
					}
				}
				unset($map);
				$basic = D ( 'Forum' )->where ( 'fid=' . $inputFid )->field('fid,name,forum_logo,timeSetting,forum_icon,forum_intro,view_count,topic_count,post_count,most_online_user,lastpost_uid,lastpost_time,lastpost_post_tid,lastpost_post_pid,today_thread_count,today_post_count,today_view_count')->find ();
				$data = array_merge ( $data, $basic );
				//时间控制继承上一级
				if(empty($basic['timeSetting'])){
					while ( ! empty ( $timeSetting ) ) {
						$tempFid = array_pop ( $timeSetting );
						$map ['fid'] = $tempFid;
						$timeSettingData = $forumModel->where ( $map )->field ( 'timeSetting' )->find ();
						if (! empty ( $timeSettingData['timeSetting'] )) {
							$tempData = $timeSettingData ['timeSetting'];
							$data ['timeSetting'] =  $tempData;
							$timeSetting = array();
						}
					}
				}
				
				self::$setting [$inputFid] = $data;
				return $data;
			}
		}
		
		$sql = $baseSql . " WHERE forum.fid = 0";
		$data = M ()->query ( $sql );
		
		$data = $data [0];
		if (! empty ( $data ['manager'] )) {
			$data ['manager'] = explode ( ',', $data ['manager'] );
		}
		
		if ($needNowBase) {
			$basic = D ( 'Forum' )->where ( 'fid=' . $inputFid )->find ();
			$data = array_merge ( $data, $basic );
		}
		self::$setting [$inputFid] = $data;
		return $data;
	}
}
?>