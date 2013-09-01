<?php
	class ForumMastModel extends Model{
		//获取一个人的所有昵称
		public function getAllMast($uid,$detail=true){
			if($uid){
				$map['uid'] = intval($uid);
				$list = $this->where($map)->findAll();
				if($detail==true){
					return $list;
				}elseif($detail==false){
					$data = getSubByKey($list,'id');
				return $data;
				}
			}
		}
		
		//获取一个人的启用昵称
		public function getUserMast($uid){
			if($uid){
				$map['uid'] = intval($uid);
				$map['status'] = 1;
				$list = $this->where($map)->find();
			}
			return $list;
		}
		//判断一个人的昵称数
		
		public function getMastCount($uid){
			if($uid){
				$map['uid'] = intval($uid);
				$count = $this->where($map)->count();
			}
		}
	}
?>