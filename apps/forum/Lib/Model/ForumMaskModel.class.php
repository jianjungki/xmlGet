<?php
	class ForumMaskModel extends Model{
		//获取一个人的所有昵称
		protected  $tableName = 'forum_mask';
		public function getAllMast($uid,$detail=true){
			if($uid){
				$map['uid'] = intval($uid);
				$map['isdel'] = 0;
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
				$map['isdel'] =0;
				$list = $this->where($map)->find();
			}
			return $list;
		}
		//判断一个人的昵称数
		
		public function getMastCount($uid){
			if($uid){
				$map['uid'] = intval($uid);
				$map['isdel'] = 0;
				$count = $this->where($map)->count();
			}
			return count;
		}
		

	}
?>