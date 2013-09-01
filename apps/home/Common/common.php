<?php

//是否已设置头像
function isSetAvatar($uid){
    return is_file( SITE_PATH.'/data/uploads/avatar/'.$uid.'/small.jpg');
}

//获取微博条数
function getMiniNum($uid){
	return M('weibo')->where('uid=' . $uid . ' AND isdel=0')->count();
}

//获取关注数
function getUserFollow($uid){
	$count['following'] = M('weibo_follow')->where("uid=$uid AND type=0")->count();
	$count['follower']  = M('weibo_follow')->where("fid=$uid AND type=0")->count();
	return $count;
}

// 短地址
function getContentUrl($url) {
	return getShortUrl( $url[1] ).' ';
}

// 登录页微博表情解析
function login_emot_format($content)
{
    return preg_replace_callback('/(?:#[^#]*[^#^\s][^#]*#|(\[.+?\]))/is', 'replaceEmot', $content);
}
/*******************2012-09-14加*****************************/
//判读是不是创建者
function iscreater($uid,$orgid) {
	return D('User')->where("uid=$uid AND deputyoriid=$orgid")->count();
}


//判读是不是管理员
function isadmin($uid,$orgid) {
	$ret = D('OrgUserlink')->where("uid=$uid AND orgid=$orgid AND (level=1 OR level=2)")->count();

	return $ret;
}

//判读是不是成员
function ismember($uid,$orgid) {
	return D('OrgUserlink')->where("uid=$uid AND orgid=$orgid AND level=3")->count();
}



/**
 * 根据群组Logo的保存路径获取Logo的URL
 *
 * @param string $save_path 群组Logo的保存路径
 * @return string 群组Logo的URL. 给定路径不存在时, 返回默认的群组Logo的URL地址.
 */
function logo_path_to_url($save_path)
{
	$path = SITE_PATH . '/data/uploads/' . $save_path;
	if (file_exists($path))
		return SITE_URL . '/data/uploads/' . $save_path;
	else
		return SITE_URL . '/apps/group/Tpl/default/Public/images/group_pic.gif';
}

/**
 * 根据群组ID获取群组详情页的URL
 *
 * @param int $group_id 群组ID
 * @return string 群组详情页的URL
 */
function group_id_to_url($group_id)
{
	return U('group/Group/index', array('gid'=>$group_id));
}

//判断是不是我的粉丝
/**
 * 
 * @param unknown_type $uid 关注
 * @param unknown_type $fid 被关注
 * @return boolean 
 * 2012-11-2 
 */
function isFans($uid,$fid){
	
	if(D('Follow','weibo')->where('fid='.$fid.' and uid='.$uid.' and type=0 ')->select())
		return true;
	else 
		return false;
	
}
//判断是否项目
	function isProject($gid) {
		$flag  = 0;
		$pro = D('Group', 'group') -> field('id') -> where('deputyorgid is not NULL') -> select();
		$pro = getSubByKey($pro, 'id');
		if (in_array($gid, $pro)) {
			$flag  = 1;
		}
		return $flag;
	}
	//获取活动封面存储地址
	function getCover($coverId,$width=80,$height=80){
		$cover = D('Attach')->field('savepath,savename')->find($coverId);
		if($cover){
			$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=".get_photo_url($cover['savepath'].$cover['savename']);
		}else{
			$cover	=	SITE_URL."/thumb.php?w=$width&h=$height&url=./apps/event/Tpl/default/Public/images/hdpic1.gif";
		}
		return $cover;
	}
	
	//根据存储路径，获取图片真实URL
	function get_photo_url($savepath) {
		return './data/uploads/'.$savepath;
	}
	
	//获取项目或圈子的标题
	function getLabel($gid) {
		$gt = D('Group', 'group') -> field('grouptype') -> where('id = '.$gid) -> select();
		switch ($gt[0]['grouptype']) {
			case 1:
				$label = '项目';
				break;
			
			case 2:
				$label = '组织';
				break;
			
			default:
				$label = '圈子';
				break;
		}
		return $label;
	}
