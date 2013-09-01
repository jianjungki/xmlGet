<?php 
class SignModel extends Model {
	protected $tableName = "forum_sign";
	
	public function getSignOnlyInfo($id){
		$map['signid'] = array('in',$id);
		return $this->where($map)->findAll();
	}

	
	public function getAllSign($limit){
		$data = $this->findPage($limit);
		$temp = $data['data'];
		$attach = model('Xattach');
		foreach($temp as &$value){
			if (C ( 'MEMCACHED_ON' )) {
				$cache = service ( 'Cache' );
				$icon = $cache->getAttach($value['icon']);
				if(!$icon){
					$icon = $attach->where('id='.$value['icon'])->find();
					$cache->setAttach($value['icon'],$icon);
				}
			} else {
				$icon = $attach->where('id='.$value['icon'])->field("savepath,savename")->find();
				
			}
			if($icon){
				$value['icon'] = SITE_URL.'/data/uploads/'.$icon['savepath'].$icon['savename'];
			}
		}
		$data['data'] = $temp;
		return $data;
	}
	
	public function getSignInfoList($id,$needGroup = true){
		$map['sign.signid'] = array("in",$id);
		$map['post.status'] = 1;
		$data = $this->table("`ts_forum_sign` as sign left join `ts_forum_sign_post` as post
		on post.signId = sign.signid")->field("sign.signid,sign.name,sign.icon,sign.position,post.uid,post.cTime")->where($map)->order('sign.position ASC')->group('sign.signid')->findAll();
		$attach = model('Xattach');
		foreach($data as &$value){
			if (C ( 'MEMCACHED_ON' )) {
				$cache = service ( 'Cache' );
				$icon = $cache->getAttach($value['icon']);
				if(!$icon){
					$icon = $attach->where('id='.$value['icon'])->find();
					$cache->setAttach($value['icon'],$icon);
				}
			} else {
				$icon = $attach->where('id='.$value['icon'])->field("savepath,savename")->find();
				
			}
			if($icon){
				$value['icon'] = SITE_URL.'/data/uploads/'.$icon['savepath'].$icon['savename'];
			}
		}
		if($needGroup){
			return group($data,'position');
		}else{
			return $data;
		}
	}

}
