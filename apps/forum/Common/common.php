<?php
function getWidget($id, $admin, $data, $service) {
	foreach ( $data as $value ) {
		if ($value ['id'] == $id) {
			$signId = 0;
			if (! empty ( $value ['signId'] )) {
				$sign = "[widget:" . $value ['signId'] . "]";
				$signId = $value ['signId'];
				$widget = $service->parseId ( $sign, false );
			} else {
				$widget = $service->parse ( $value ['widget'] );
				$sign = $service->getSign ( $value ['widgetType'] );
				$map ['id'] = $value ['id'];
				$save ['signId'] = $sign;
				$signId = $sign;
				M ( 'ForumWidget' )->where ( $map )->save ( $save );
			}
			$preg = "/([\n\r\t\s]*)<DIV class=[\"']?diy_admin_popup(.*)[\"']?(.*)>(.*)<\/DIV>([\n\r\t\s]*)/siU";
			if ($admin) {
				$adminDiv = sprintf ( '<div class="diy_admin_popup R" onclick="diyPopup(%s,\'%s\',\'%s\')">%s</div>', $id, $signId, $value ['widgetType'], "管理" );
				$widget = preg_replace ( $preg, $adminDiv, $widget );
			} else {
				$widget = preg_replace ( $preg, '', $widget );
			
			}
			return $widget;
		}
	}
}
function getWidgetType() {
	return array ("number" => "数字", "text" => "字符串", "radio" => "单选", "checkbox" => "多选", "textarea" => "文本", "select" => "选择", "email" => "电子邮件", "url" => "超级链接" );
}

function getCreditType() {
	return array ('addTopic' => '发主题(+)', 'reply' => '回复(+)', 'elite' => '精华(+)', 'top' => '置顶(+)', 'commend' => '推荐(+)', 'topX' => 'TOP5(+)', 'close' => '锁帖(-)', 'delete' => '删除帖子(-)', 'edit' => '编辑帖子(-)', 'upload' => '上传附件(+)', 'download' => '下载附件(-)', 'remove_elite' => '撤销精华(-)', 'remove_topX' => '撤销TOP5(-)' );
}

function filterWord($word) {
	if (C ( 'MEMCACHED_ON' )) {
		$cache = service ( 'Cache' );
		$filterWord = $cache->getFilterWord ();
		if (! $filterWord) {
			$filterWord = D ( 'FilterWord' )->order ( 'ordernum DESC' )->findAll ();
			$filterWord = getSubByKey ( $filterWord, 'name' );
			$cache->setFilterWord ( $filterWord );
		}
		$filter = $filterWord;
	} else {
		$filter = D ( 'FilterWord' )->order ( 'ordernum DESC' )->findAll ();
		$filter = getSubByKey ( $filter, 'name' );
	}
	$filterTemp = array ("script", "iframe" );
	$filterOther = array ();
	foreach ( $filterTemp as $value ) {
		$filterOther [0] [] = $value;
		$filterOther [1] [] = chunk_split ( $value, 2, "-" );
	}
	$word = str_ireplace ( $filterOther [0], $filterOther [1], $word );
	return str_ireplace ( $filter, "*", $word );

}

function parseTopic($data, $group = true, $forums = false) {
	$signModel = D ( 'Sign', "forum" );
	$cateModel = D ( 'Board', "forum" );
	$sign = array ();
	$cate = array ();
	$forum = array ();
	
	foreach ( $data as &$value ) {
		//标识处理
		if ($value ['sign'] != 0) {
			$sign [] = $value ['sign'];
		}
		
		if ($value ['tclass'] != 0) {
			$cate [] = $value ['tclass'];
		}
		if ($forums) {
			$forum [] = $value ['fid'];
		}
		$value ['title'] = filterWord ( $value ['title'] );
	}
	$sign = array_unique ( array_filter ( $sign ) );
	$sign = array_unique ( array_filter ( explode ( ",", implode ( ",", $sign ) ) ) );
	$cate = array_unique ( array_filter ( $cate ) );
	$forum = array_unique ( array_filter ( $forum ) );
	if (! empty ( $sign )) {
		$sign = $signModel->getSignInfoList ( $sign, $group );
		if ($group) {
			foreach ( $data as &$value ) {
				if (! empty ( $value ['sign'] )) {
					$temp = explode ( ',', $value ['sign'] );
					foreach ( $sign [0] as $v ) {
						if (in_array ( $v ['signid'], $temp )) {
							if (! is_array ( $value ['sign'] ))
								$value ['sign'] = array ();
							$value ['sign'] [0] [] = $v;
						}
					}
					
					foreach ( $sign [1] as $v ) {
						if (in_array ( $v ['signid'], $temp )) {
							if (! is_array ( $value ['sign'] ))
								$value ['sign'] = array ();
							$value ['sign'] [1] [] = $v;
						}
					}
				}
			}
		} else {
			foreach ( $data as &$value ) {
				if (! empty ( $value ['sign'] )) {
					$temp = explode ( ',', $value ['sign'] );
					foreach ( $sign as $v ) {
						if (in_array ( $v ['signid'], $temp )) {
							if (! is_array ( $value ['sign'] ))
								$value ['sign'] = array ();
							$value ['sign'] [] = $v;
						}
					}
				}
			}
		}
	
	}
	
	if (! empty ( $cate )) {
		$cate = $cateModel->gettClassById ( $cate );
		foreach ( $data as &$value ) {
			foreach ( $cate as $v ) {
				if ($v ['id'] == $value ['tclass']) {
					$value ['cate'] = $v;
				}
			}
		}
	}
	
	if ($forums && ! empty ( $forum )) {
		$map ['fid'] = array ('in', $forum );
		$forum = D ( 'Forum' )->where ( $map )->findAll ();
		foreach ( $data as &$value ) {
			foreach ( $forum as $v ) {
				if ($v ['fid'] == $value ['fid']) {
					$value ['forum'] = $v ['name'];
				}
			}
		}
	}
	
	//	$value['sign'] = $signModel->getSignInfoList($value['sign']);
	

	return $data;
}

//获取版块全部统计信息
function getTotalCount($tree, $totalCount, $class, $type) {
	$child = array ();
	getChildTagId ( array ($tree ), $child );
	
	foreach ( $child as $value ) {
		$theme [] = $totalCount [$value] ['theme'];
		$reply [] = $totalCount [$value] ['reply'];
	}
	$data ['theme'] = array_sum ( $theme );
	$data ['reply'] = array_sum ( $reply );
	return $data [$type];
}

//获取版块今天统计信息
function getTodayCount($tree, $totalCount, $class, $type) {
	$child = array ();
	getChildTagId ( array ($tree ), $child );
	
	foreach ( $child as $value ) {
		$theme [] = $totalCount [$value] ['theme'];
		$reply [] = $totalCount [$value] ['reply'];
	}
	$data ['theme'] = array_sum ( $theme );
	$data ['reply'] = array_sum ( $reply );
	return $data [$type];
}

function getChildTagId($tree, &$child = array()) {
	foreach ( $tree as $v ) {
		$child [] = $v [a];
		if (is_array ( $v [d] )) {
			getChildTagId ( $v [d], $child );
		}
	}
}

function getBBSContent($content) {
	return str_replace ( array ('[quote]', '[/quote]' ), array ('<div class="rela_fie"><fieldset><legend>引用</legend><p>', '</p></fieldset></div>' ), $content );
}

function getQuoteContent($content) {
	$content = preg_replace ( "/\[quote\](.+?)\[\/quote\]/is", "", $content );
	$content = strip_tags ( $content );
	//return msubstr ( $content, 0, 100 );
	return $content;
}

//取得Tag版块下的版主
function getBanzu($tagid) {
	$info = D ( 'Board' )->where ( 'tagid=' . $tagid )->field ( 'banzhu' )->find ();
	$info = explode ( ',', $info ['banzhu'] );
	$info = array_map ( 'getUserName', $info );
	return implode ( ',', $info );
}

function getTclassName($classid) {
	$info = M ( 'bbs_tclass' )->where ( 'id=' . $classid )->field ( 'name' )->find ();
	if ($info) {
		return "[ " . $info ['name'] . " ]";
	}
}

//将二维数组格式化为一维数组
function formatArray($array, $field = 'id') {
	$res = array ();
	foreach ( $array as $v ) {
		$res [] = $v [$field];
	}
	return $res;
}

function changeTime($time) {
	
	if ($time == 86400) {
		echo '一天';
	} elseif ($time == 259200) {
		echo '三天';
	} elseif ($time == 604800) {
		echo '一周';
	} elseif ($time == 2592000) {
		echo '一个月';
	} elseif ($time == 31536000) {
		echo '一年';
	} elseif ($time == 0) {
		echo '永久';
	}
}

function getUid($name) {
	$map ['fullname'] = array ('like', "%$name%" );
	$list = M ( 'User' )->where ( $map )->findAll ();
	$uids = getSubByKey ( $list, 'uid' );
	return $uids;
}

function getPost($uid) {
	$map ['uid'] = $uid;
	$count = M ( 'forum_post' )->where ( $map )->count ();
	return $count;
}

function getGroupIcon($url) {
	$file = SITE_URL . '/data/uploads/' . $url;
	if (! empty ( $url ) && file_exists ( UPLOAD_PATH . '/'  . $url )) {
		return $file;
	} else {
		return __PUBLIC__ . '/images/user_pic.gif';
	}
}

function getMaskFaces($id) {
	$faceid = intval ( $id );
	$face = M ( 'x_attach' )->where ( 'id=' . $faceid )->find ();
	$path = $face ['savepath'] . $face ['savename'];
	$url = SITE_URL . '/data/uploads/' . $path;
	if (! empty ( $id ) && file_exists ( UPLOAD_PATH . '/'  . $path )) {
		return $url;
	} else {
		return __PUBLIC__ . '/images/user_pic.gif';
	}
}

function getBlockName($id) {
	$fid = intval ( $id );
	$block = M ( 'forum' )->where ( 'fid=' . $fid )->find ();
	return $block ['name'];
}
function addLog($uid, $data, $temp, $type = 'admin') {
	return D ( 'AdminLog', 'forum' )->addLog ( $uid, $data, $temp, $type );
}

function rmdirr($dirname) {

	if (!file_exists($dirname)) {
		return false;
	}

	if (is_file($dirname) || is_link($dirname)) {
		return unlink($dirname);
	}

	$dir = dir($dirname);
	while (false !== $entry = $dir->read()) {

		if ($entry == '.' || $entry == '..') {
			continue;
		}
		rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
	}
	$dir->close();

	return rmdir($dirname);
}

function getSignIcon($signid){
	$res = M('forum_sign')->where("signid=$signid")->find();
	$map['id'] = $res['icon'];
	$face = M ( 'x_attach' )->where ( $map )->find ();
	$path = $face ['savepath'] . $face ['savename'];
	$url = SITE_URL . '/data/uploads/' . $path;
	if (! empty ( $signid ) && file_exists ( UPLOAD_PATH . '/'  . $path )) {
		return $url;
	} else {
		return __PUBLIC__ . '/images/user_pic.gif';
	}
}

/* 新添加 */
function getHeaderLinkList(){
	$headerLinkList	=	F('forum_header_link_list');
	if(!$headerLinkList){
		$forumHeader = model('Xdata')->get("forum_dict:link_forum_header_list");
		$pid	=	$forumHeader['value'];
		$headerLinkList	=	service('Category')->getSubCategoryList($pid);
		F('forum_header_link_list',$headerLinkList);
	}
	return $headerLinkList;
}

/**
 * 以数组中的一个字段的值为唯一索引返回一个三维数组
 * @param $pArray 一个二维数组
 * @param $pFieldBy 作为索引的字段的KEY值
 * @param $pIncludeFileld 可以定义返回的数组的包含的原数组的字段
 * @return 返回新的三维数组
 */
function group($pArray, $pFieldBy, $pIncludeFileld = "") {
	if ($pIncludeFileld != "")
		$fields = explode ( ",", $pIncludeFileld );
	$result_array = array ();
	
	for($i = 0; $i < count ( $pArray ); $i ++) {
		$group_key = $pArray [$i] [$pFieldBy];
		if (! isset ( $result_array [$group_key] )) {
			$result_array [$group_key] = array ();
		}
		
		if ($pIncludeFileld != "") {
			$temp = array ();
			foreach ( $fields as $field ) {
				$temp [$field] = $pArray [$i] [$field];
			}
			$result_array [$group_key] [] = $temp;
		} else {
			$result_array [$group_key] [] = $pArray [$i];
		}
	}
	return $result_array;
}

function getForumUserFace($uid, $setting, $siteSetting, $maskId = -1) {
	return getUserFace($uid,'b');
}

/**
 * 自动判定，获取用户名
 * @param unknown_type $uid	用户id
 * @param unknown_type $setting	版块设置
 * @param unknown_type $siteSetting 全站设置
 */
function getForumUserName($uid, $setting, $siteSetting, $maskId = -1) {
	return getUserName($uid);
}

function getUserWork($uid) {
	if($uid == -1) return "guest";
	$GLOBALS ['_MapName'];
	if (isset ( $GLOBALS ['_MapName'] [$uid] )) {
		return $GLOBALS ['_MapName'] [$uid];
	}
	//开启了Memcached的处理过程
	if (C ( 'MEMCACHED_ON' )) {
		$cache = service ( "Cache" );
		$username = $cache->getUserName ( $uid, "work" );
		//缓存未命中，去数据库取
		if (! $username) {
			$map ['uid'] = $uid;
			$userinfo = M ( 'User' )->where ( $map )->field ( 'uname,fullname,name' )->find ();
			$GLOBALS ['_MapName'] [$uid] = $userinfo ['uname'];
			$cache->setUserName ( $uid, $userinfo ['uname'], "work" );
			return t ( $userinfo ['uname'] );
		} else {
			return t ( $username );
		}
	} else {
		//缓存未命中，去数据库取
		if (! isset ( $GLOBALS ['_MapName'] [$uid] )) {
			$map ['uid'] = $uid;
			$userinfo = M ( 'User' )->where ( $map )->field ( 'uname,fullname,name' )->find ();
			$GLOBALS ['_MapName'] [$uid] = $userinfo ['uname'];
			return t ( $userinfo ['uname'] );
		} else {
			return t ( $GLOBALS ['_MapName']  [$uid] );
		}
	}
}

function getWidgetData($data) {
	$res = explode ( '<br />', nl2br ( $data ) );
	$result = array ();
	foreach ( $res as &$value ) {
		$temp = str_replace ( "\r\n", "", $value );
		$temp = array_map ( "trim", explode ( "=", $temp ) );
		if (count ( $temp ) > 1) {
			$result [$temp [0]] = $temp [1];
		} else {
			$result [] = $temp [0];
		}
	}
	return $result;
}

?>