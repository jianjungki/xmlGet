<?php
class MaskModel extends Model {
	protected $tableName = "forum_mask";
	
	public function getUserMask($uid) {
		if (C ( 'MEMCACHED_ON' )) {
			$cache = service ( 'Cache' );
			$data = unserialize($cache->getUserName($uid,"maskName"));
			if(!$data){
				$map ['uid'] = $uid;
				$map ['status'] = 1;
				$data = $this->where ( $map )->field('name,maskId')->find ();
				$cache->setUserName($uid,serialize($data),"maskName");
			}
		} else {
			$map ['uid'] = $uid;
			$map ['status'] = 1;
			$data = $this->where ( $map )->field('name,maskId')->find ();
		}
		return $data;
	}
}