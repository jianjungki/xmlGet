<?php
class UserPrivacyModel extends Model {
	protected	$tableName	=	'user_privacy';

	//获取用户设置
	function getUserSet($mid) 	{
		$userPrivacy = $this->where("uid=$mid")->field('`key`,`value`')->findAll();
        $temp = array(
                'weibo_comment' => 0, //(所有人，除黑名单)
                'message' => 0, //(所有人，除黑名单)
                'show_remark' => 3,//不显示备注
                'orginfo' => 0,//默认不显示
                'space' => 0,//(所有人，除黑名单)
                'person' => 3,//不显示备注
                'tele' => 0,//默认不显示
        );
		if($userPrivacy){
			foreach ($userPrivacy as $k=>$v){
				$r[$v['key']] = $v['value'];
			}
            $r = array_merge($temp,$r);
			return $r;
		}else{
			return $this->defaultSet();
		}
	}
    
    function getRemark($mid)   {
        $remarkinfo = $this->where("uid=$mid and `key`='show_remark'")->field('`value`')->find();
        if($remarkinfo){
            return $remarkinfo['value'];
        }else{
            return 0;
        }
        
    }
    //存储是否显示备注信息
    function saveRemark($message,$uid){
        $map['uid'] = $uid;
        $map['key'] = 'show_remark';
        $data['key']   = 'show_remark';
        $data['value'] = $message;
        $res = $this->where($map)->find();
        if($res){
            $this->where($map)->save($data);
        }
        if(!$res){
            $data['uid'] = $uid;
            $this->add($data);
        }
        return true;
    }
	//保存用户设置  我勒个去啊 谁写的
	function dosave($message,$uid){
		if(empty($uid)){
			return false;
		}
        
		$map['uid'] = $uid;
		$map['key'] = 'weibo_comment';
		$data['key']   = 'weibo_comment';
		$data['value'] = $message['weibo_comment'];
		$res = $this->where($map)->find();
		if($res){
			$this->where($map)->save($data);
		}
		if(!$res){
			$data['uid'] = $uid;
			$this->add($data);
		}
		unset($map);unset($data);
        
		$map['uid'] = $uid;
		$map['key'] = 'message';
		$data['key']   = 'message';
		$data['value'] = $message['message'];
		$res = $this->where($map)->find();
		if($res){
			$this->where($map)->save($data);
		}
		if(!$res){
			$data['uid'] = $uid;
			$this->add($data);
		}
		unset($map);unset($data);
		$map['uid'] = $uid;
		$map['key'] = 'space';
		$data['key']   = 'space';
		$data['value'] = $message['space'];
		$res = $this->where($map)->find();
		if($res){
			$this->where($map)->save($data);
		}
		if(!$res){
			$data['uid'] = $uid;
			$this->add($data);
		}
        unset($map);unset($data);
        
        $map['uid'] = $uid;
        $map['key'] = 'orginfo';
        $data['key']   = 'orginfo';
        if(isset($message['orginfo']))
            $data['value'] = $message['orginfo'];
        else 
            $data['value'] = 0;
        $res = $this->where($map)->find();
        if($res){
            $this->where($map)->save($data);
        }
        if(!$res){
            $data['uid'] = $uid;
            $this->add($data);
        }
        
        $map['uid'] = $uid;
        $map['key'] = 'tele';
        $data['key']   = 'tele';
        if(isset($message['tele']))
            $data['value'] = $message['tele'];
        else 
            $data['value'] = 0;
        $res = $this->where($map)->find();
        if($res){
            $this->where($map)->save($data);
        }
        if(!$res){
            $data['uid'] = $uid;
            $this->add($data);
        }
        
        $map['uid'] = $uid;
        $map['key'] = 'person';
        $data['key']   = 'person';
        if(isset($message['person']))
            $data['value'] = $message['person'];
        else 
            $data['value'] = 0;
        $res = $this->where($map)->find();
        if($res){
            $this->where($map)->save($data);
        }
        if(!$res){
            $data['uid'] = $uid;
            $this->add($data);
        }
        
		return true;

	}

	//默认配置
	private function defaultSet(){
		return array(
			'weibo_comment' => 0, //(所有人，除黑名单)
			'message' => 0,	//(所有人，除黑名单)
			'show_remark' => 3,//不显示备注
			'orginfo' => 0,//默认不显示
			'space' => 0,//(所有人，除黑名单)
			'person' => 3,//不显示备注
            'tele' => 0,//默认不显示
		);
	}

	function getPrivacy($mid,$uid){
		if($mid==$uid) {
			$data['weibo_comment'] = true;
			$data['message']       = true;
			$data['follow']        = true;
			return $data;
		}

		$isBackList  = isBlackList($uid, $mid);
		$followState = getFollowState($uid, $mid) != 'unfollow';
		$userset     = $this->getUserSet($uid);
		if ($isBackList) {
			$data['weibo_comment'] = false;
			$data['message']       = false;
			$data['follow']        = false;
			$data['blacklist']     = true;
		}else {
			$data['weibo_comment'] = ( $userset['weibo_comment'] )? $followState : true;
			$data['message']       = ( $userset['message'] )? $followState : true;
			$data['follow']        = true;
			$data['blacklist']     = false;
		}

		return $data;
	}

	//设置黑名单
	function setBlackList($mid,$type,$fid){
		if($type=='add'){
			$map['uid'] = $mid;
			$map['fid'] = $fid;
			if( M('user_blacklist')->where($map)->count()==0 ){
				$map['ctime'] = time();
				M('user_blacklist')->add($map);  //添加黑名单
				M('weibo_follow')->where("(uid=$mid AND fid=$fid) OR (uid=$fid AND fid=$mid)")->delete(); //自动解除关系
				return true;
			}else{
				return false;
			}
		}else{
			$map['uid'] = $mid;
			$map['fid'] = $fid;
			return M('user_blacklist')->where($map)->delete();
		}
	}

	//获取黑名单列表
	function getBlackList($mid){
		return M('user_blacklist')->where("uid=$mid")->findall();
	}

	//判断用户是否是黑名单关系
	function isInBlackList($uid,$mid){
		$uid = intval($uid);
		$mid = intval($mid);
		$result = M('user_blacklist')->where("uid=$mid AND fid=$uid")->find();
		return	$result;
	}
}
?>