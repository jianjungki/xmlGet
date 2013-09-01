<?php
class AdminRuleModel extends Model {
	protected $tableName = "forum_admin_rule";
	
	public function getPopedomOneGroup($fid,$gid){
		if (C ( 'MEMCACHED_ON' )) {
			$cache = service ( 'Cache' );
			$popedom = $cache->getForumRule($gid,$fid,"admin");
			if( $popedom === false || ($popedom != array() && empty($popedom)) ){
				$popedom = $this->_getPopedomOneGroup($fid,$gid);
				if($popedom){
					$cache->setForumRule($gid,$fid,"admin",$popedom);
				}else{
					$cache->setForumRule($gid,$fid,"admin",array());
				}
			}
		} else {
			$popedom = $this->_getPopedomOneGroup($fid,$gid);
		}
		return $popedom;
	}
	
	public function _getPopedomOneGroup($fid, $gid) {
		if (empty ( $gid )) {
			return array ();
		}
		//查找fid的根基
		$category = service ( 'Category' );
		$category = $category->getCategoryPath ( $fid, false );
		$fid = getSubByKey ( $category, 'fid' );
		$model = D ( 'Template', 'forum' );
		$moedl2 = D ( 'Sign', 'forum' );
		
		while ( ! empty ( $fid ) ) {
			$map = array ();
			$map ['fid'] = array_pop ( $fid );
			$map ['admingid'] = $gid;
			$data = $this->where ( $map )->find ();
			
			//模板
			if (! empty ( $data ['allow_edit_special_thread'] )) {
				$data ['allow_edit_special_thread'] = $model->getTemplateOnlyInfo ( $data ['allow_edit_special_thread'] );
			}
			//特殊标识
			if (! empty ( $data ['allow_edit_special_sign'] )) {
				$data ['allow_edit_special_sign'] = $moedl2->getSignOnlyInfo ( $data ['allow_edit_special_sign'] );
			}
			
			if ($data){
				return $data;
			}
		}
		$map ['fid'] = 0;
		$map ['admingid'] = $gid;
		$data = $this->where ( $map )->find ();
		//模板
		if (! empty ( $data ['allow_edit_special_thread'] )) {
			$data ['allow_edit_special_thread'] = $model->getTemplateOnlyInfo ( $data ['allow_edit_special_thread'] );
		}
		//特殊标识
		if (! empty ( $data ['allow_edit_special_sign'] )) {
			$data ['allow_edit_special_sign'] = $moedl2->getSignOnlyInfo ( $data ['allow_edit_special_sign'] );
		}
		
		return $data;
	}
	public function cleanCache($fid,$gid,$cache) {
		$category = service ( 'Category' );
		$inputFid = $fid = $fid == 0?1:$fid;
		$cate = $category->getSubCategoryID ( $fid );
		foreach ( $cate as $value ) {
			$cache->cleanForumRule ( $gid,$value,"admin" );
		}
	}
	public function addAdminPope($fid) {
		$fid = intval ( $fid );
		$gid = $_POST ['gids'];
		$field = array_unique ( $_POST ['field'] );
		unset ( $_POST ['adminGid'] );
		unset ( $_POST ['field'] );
		$search = array ();
		foreach ( $_POST as $k => $value ) {
			foreach ( $value as $k2 => $v ) {
				$search [$k2] [$k] = $v;
			}
		}
		$m ['fid'] = $fid;
		$map ['fid'] = $fid;
		$cache = service('Cache');
		foreach ( $gid as $value ) {
			$m ['admingid'] = $value;
			$map ['admingid'] = $value;
			foreach ( $field as $v ) {
				$map [$v] = intval ( isset ( $search [$value] [$v] ) );
			}
			$this->cleanCache ( $fid,$value, $cache );
			$res = $this->where ( $m )->find ();
			if ($res) {
				$r = $this->where ( $m )->save ( $map );
				if ($r) {
					$result = $r;
				}
			} else {
				$r = $this->add ( $map );
				if ($r) {
					$result = $r;
				}
			}
		}
		return $result;
	}
	
	public function addThreadPope($fid) {
		$fid = intval ( $fid );
		$gid = $_POST ['gids'];
		$field = array_unique ( $_POST ['field'] );
		
		unset ( $_POST ['adminGid1'] );
		unset ( $_POST ['field'] );
		$search = array ();
		foreach ( $_POST as $k => $value ) {
			foreach ( $value as $k2 => $v ) {
				$search [$k2] [$k] = $v;
			}
		}
		$m ['fid'] = $fid;
		$map ['fid'] = $fid;
		$cache = service('Cache');
		foreach ( $gid as $value ) {
			$m ['admingid'] = $value;
			$m ['admingid'] = $value;
			foreach ( $field as $v ) {
				$map [$v] = intval ( isset ( $search [$value] [$v] ) );
			}
			$this->cleanCache ( $fid, $value,$cache );
			$res = $this->where ( $m )->find ();
			if ($res) {
				$r = $this->where ( $m )->save ( $map );
				if ($r) {
					$result = $r;
				}
			} else {
				$r = $this->add ( $map );
				if ($r) {
					$result = $r;
				}
			}
		}
		return $result;
	}
	
	public function addBlockPope($fid) {
		$fid = intval ( $fid );
		$gid = $_POST ['gids'];
		$field = array_unique ( $_POST ['field'] );
		unset ( $_POST ['adminGid2'] );
		unset ( $_POST ['field'] );
		$search = array ();
		foreach ( $_POST as $k => $value ) {
			foreach ( $value as $k2 => $v ) {
				$search [$k2] [$k] = $v;
			}
		}
		$m ['fid'] = $fid;
		$map ['fid'] = $fid;
		$cache = service('Cache');
		foreach ( $gid as $value ) {
			$m ['admingid'] = $value;
			$map ['admingid'] = $value;
			foreach ( $field as $v ) {
				$map [$v] = intval ( isset ( $search [$value] [$v] ) );
			}
			$this->cleanCache ( $fid, $value ,$cache);
			$res = $this->where ( $m )->find ();
			if ($res) {
				$r = $this->where ( $m )->save ( $map );
				if ($r) {
					$result = $r;
				}
			} else {
				$r = $this->add ( $map );
				if ($r) {
					$result = $r;
				}
			}
		}
		return $result;
	}
	
	public function setDefaultAdminPope($fid) {
		$list = $this->where ( "fid=0" )->findAll ();
		foreach ( $list as $k => $v ) {
			$v ['fid'] = $fid;
			$this->add ( $v );
		}
	}
}
?>