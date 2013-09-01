<?php
class UserGroupModel extends Model {
	protected	$tableName	=	'forum_user_group';

	//添加用户组
	public function addUserGroup($title) {
		$data['name']		= $title;
		return $this->add($data);
	}
	
	//删除用户组
	public function deleteUserGroup($gids) {
		//防误操作
		if (empty($gids)) return false;
		
		$map['user_group_id']	= array('in', $gids);
		M('user_group')		->where($map)->delete();
	//	M('user_group_link')->where($map)->delete();
		return true;
	}
	
	public function getUserGroupByMap($map = '', $field = '*', $order = 'gid ASC') {
		return $this->field($field)->where($map)->order($order)->findAll();
	}
	
	//根据IDs获取用户组
	public function getUserGroupById($gids, $field = '*', $order = '') {
		$map['gid']	= array('in', $gids);
		return $this->getUserGroupByMap($map, $field, $order);
	}
	
	//根据用户ID获取用户组
	public function getUserGroupByUid($uids) {
		$map['uid']	= array('in', $uids);
		return M('user_group')->where($map)->order('gid ASC')->findAll();
	}
	
	public function getUidByUserGroup($gids) {
		$map['gid']	= array('in', $gids);
		return M('user_group')->where($map)->findAll();
	}
	
	//将用户添加至用户组
    public function addUserToUserGroup($uids, $gids) {
    	$gids = is_array($gids) ? $gids : explode(',', $gids);
    	$uids = is_array($uids) ? $uids : explode(',', $uids);
    	
    	//用户组信息
    	$groups = $this->getUserGroupById($gids);
    	if (!$groups) 
    		return false;
    	
    	//用户信息
    	$map['uid']	= array('in', $uids);
    	$users = model('User')->getUserList($map, false, false, 'uid,uname', '', count($uids));
    	unset($map);
    	if (!$users)
    		return false;
    	$users = $users['data'];
    	
    	//删除旧数据
    	$map['uid'] = array('in', $uids);
    	M('user_group_link')->where($map)->delete();
    	unset($map);
    	
    	//组装SQL，插入新数据
    	$sql = "INSERT INTO `" . C('DB_PREFIX') . "user_group_link` (`user_group_id`,`user_group_title`,`uid`,`uname`) VALUES ";
    	foreach($groups as $group) {
    		foreach($users as $user) {
    			$sql .= "('{$group['user_group_id']}', '{$group['title']}', '{$user['uid']}', '{$user['uname']}'),";
    		}
    	}
    	$sql = rtrim($sql, ',');
    	return $this->execute($sql);
    }
	
	public function isUserGroupExist($title, $gid = 0) {
		$map['gid']	= array('neq', $gid);
		$map['name'] = $title;
    	return $this->where($map)->find();
    }
    
    public function isUserGroupEmpty($gids) {
    	$map['gid']	= array('in', $gids);
    	return ! M('user_group')->where($map)->find();
    }
    
    public function isUserInUserGroup($uid, $gids) {
    	$map['uid']			  	= $uid;
    	$map['user_group_id']	= array('in', $gids);
    	return M('user_group_link')->where($map)->find();
    }
}