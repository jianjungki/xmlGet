<?php
class UserRuleModel extends Model {
	protected $tableName = "forum_user_rule";
	
	public function getPopedomOneGroup($fid, $gid) {
		if (C ( 'MEMCACHED_ON' )) {
			$cache = service ( 'Cache' );
			$popedom = $cache->getForumRule ( $gid, $fid, "user" );
			
			if ($popedom === false || ($popedom != array() && empty($popedom))) {
				$popedom = $this->_getPopedomOneGroup ( $fid, $gid );
				if($popedom){
					$cache->setForumRule ( $gid, $fid, "user", $popedom );
				}else{
					$cache->setForumRule ( $gid, $fid, "user", array() );
				}
			}
			
		} else {
			$popedom = $this->_getPopedomOneGroup ( $fid, $gid );
		}
		return $popedom;
	}
	
	public function _getPopedomOneGroup($fid, $gid) {
		if (empty ( $gid )) {
			$gid = 7;
		}
		//查找fid的根基
		$category = service ( 'Category' );
		$category = $category->getCategoryPath ( $fid, false );
		$fid = getSubByKey ( $category, 'fid' );
		$model = D ( 'Template', 'forum' );
		while ( ! empty ( $fid ) ) {
			$map ['fid'] = array_pop ( $fid );
			$map ['usergid'] = array ('in', $gid );
			$data = $this->where ( $map )->find ();
			//模板
			if (! empty ( $data ['allow_post_special_thread'] )) {
				$tempPost = explode ( ',', $data ['allow_post_special_thread'] );
				$str [] = $data ['allow_post_special_thread'];
			}
			//特殊标识
			if (! empty ( $data ['allow_edit_special_thread'] )) {
				$tempEdit = explode ( ',', $data ['allow_edit_special_thread'] );
				$str [] = $data ['allow_edit_special_thread'];
			}
			if (! empty ( $str )) {
				$str = implode ( ',', $str );
				$temp = $model->getTemplateOnlyInfo ( $str );
				$tempData = array ();
				$tempData2 = array ();
				foreach ( $temp as $value ) {
					if (in_array ( $value ['id'], $tempPost )) {
						$tempData [] = $value;
					}
					if (in_array ( $value ['id'], $tempEdit )) {
						$tempData2 [] = $value;
					}
				}
				
				if (! empty ( $data ['allow_post_special_thread'] )) {
					$data ['allow_post_special_thread'] = $tempData;
				}
				
				if (! empty ( $data ['allow_edit_special_thread'] )) {
					$data ['allow_edit_special_thread'] = $tempData2;
				}
			}
			if ($data)
				return $data;
		}
		
		$map ['fid'] = 0;
		$map ['usergid'] = array ('in', $gid );
		$data = $this->where ( $map )->find ();
		
		//模板
		if (! empty ( $data ['allow_post_special_thread'] )) {
			$tempPost = explode ( ',', $data ['allow_post_special_thread'] );
			$str [] = $data ['allow_post_special_thread'];
		}
		//特殊标识
		if (! empty ( $data ['allow_edit_special_thread'] )) {
			$tempEdit = explode ( ',', $data ['allow_edit_special_thread'] );
			$str [] = $data ['allow_edit_special_thread'];
		}
		if (! empty ( $str )) {
			$str = implode ( ',', $str );
			$temp = $model->getTemplateOnlyInfo ( $str );
			$tempData = array ();
			$tempData2 = array ();
			foreach ( $temp as $value ) {
				if (in_array ( $value ['id'], $tempPost )) {
					$tempData [] = $value;
				}
				if (in_array ( $value ['id'], $tempEdit )) {
					$tempData2 [] = $value;
				}
			}
			
			if (! empty ( $data ['allow_post_special_thread'] )) {
				$data ['allow_post_special_thread'] = $tempData;
			}
			
			if (! empty ( $data ['allow_edit_special_thread'] )) {
				$data ['allow_edit_special_thread'] = $tempData2;
			}
		}
		return $data;
	}
	
	public function cleanCache($fid,$gid,$cache) {
		$category = service ( 'Category' );
		$inputFid = $fid = $fid == 0?1:$fid;
		$cate = $category->getSubCategoryID ( $fid );
		foreach ( $cate as $value ) {
			$cache->cleanForumRule ( $gid,$value,"user" );
		}
	}
	
	//2010-10-22
	public function addUserPopedom($fid) {
		$fid = intval ( $fid );
		$gid = $_POST ['gids'];
		$field = array_unique ( $_POST ['field'] );
		unset ( $_POST ['gid'] );
		unset ( $_POST ['field'] );
		$search = array ();
		
		foreach ( $_POST as $k => $value ) {
			foreach ( $value as $k2 => $v ) {
				$search [$k2] [$k] = $v;
			}
		}
		$m ['fid'] = $fid;
		$map ['fid'] = $fid;
		$cache = service ( 'Cache' );
		foreach ( $gid as $value ) {
			$map = array ();
			$m ['usergid'] = $value;
			$map ['usergid'] = $value;
			foreach ( $field as $v ) {
				$map [$v] = intval ( isset ( $search [$value] [$v] ) );
			}
			$this->cleanCache ( $fid, $value,$cache );
			$res = $this->where ( $m )->find ();
			if ($res) {
				$R = $this->where ( $m )->save ( $map );
				if ($R) {
					$result = $R;
				}
			} else {
				$map ['fid'] = $fid;
				$map ['usergid'] = $value;
				$R = $this->add ( $map );
				if ($R) {
					$result = $R;
				}
			}
		}
		return $result;
	}
	
	public function setDefaultUserPope($fid) {
		$list = $this->where ( "fid=0" )->findAll ();
		foreach ( $list as $k => $v ) {
			
			$v ['fid'] = $fid;
			$this->add ( $v );
		}
	}

}
?>