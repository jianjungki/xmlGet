<?php
class MessageAction extends Action
{
	/* 私信 */

	function _initialize() {
        $data ['followTopic'] = D ( 'Follow', 'weibo' )->getTopicList ( $this->mid );
        global $ts;

        //SamPeng 2011.12.15重构整个方法
        $this->assign('install_app',$ts['install_apps']);
        $this->assign ( $data );
    }

	// 私信列表
	public function index($type = 'inbox') {
		$dao = model('Message');

		$list = $dao->getMessageListByUid($this->mid, array(1, 2));

		$map['list_id'] = array('IN', getSubByKey($list['data'], 'list_id'));
		$countlist=M('message_content')->where($map)->field("count(list_id) AS count,list_id")->group('list_id')->findall();
		foreach($countlist as $k=>$v){
			$newcount[$v['list_id']]=$v['count'];
		}
		foreach($list['data'] as &$value){
			$value['message_num']=$newcount[$value['list_id']];
			$value['last_message']['content'] = htmlspecialchars_decode($value['last_message']['content']);
		}
		$this->assign($list);

		// 设置私信为已读
		model('Message')->setMessageIsRead(null, $this->mid);

		$this->setTitle($type = L('my_private_message'));
		$this->display('list');
	}

	// 私信详情
	public function detail()
	{
		$message = model('Message')->isMember($_GET['id'], $this->mid, true);
		if (empty($message))
			$this->error(L('message_notexist'));

		$message['member'] = model('Message')->getMessageMembers($_GET['id'], 'member_uid');

		$message['to']     = array();

		foreach($message['member'] as $v){
			$this->mid != $v['member_uid'] && $message['to'][] = $v;
		}

		$map['list_id']=$_GET['id'];
		$message['message_num']=M('message_content')->where($map)->count();
		$this->assign('message', $message);

		$this->assign('type', intval($_GET['type']));
		$this->setTitle(L('message'));
		$this->display();
	}

	public function loadMessage()
	{
		$message = model('Message')->getMessageByListId($_POST['list_id'], $this->mid, $_POST['since_id'], $_POST['max_id']);
		foreach($message['data'] as &$value) {
			$value['content'] = str_replace('&amp;', '&', $value['content']);
			$value['content'] = htmlspecialchars_decode($value['content']);
			$value['content'] = safe($value['content'],'link');
		}
		//是否已经归类
		$work_list = M("work_task")->where('app_type = 2 AND uid='.$this->mid)->select();
		$work_wid = getSubByKey($work_list, 'app_id');
		foreach( $message['data'] as $key=>&$value){
			// if (in_array($value['message_id'], $work_wid)) {
				$value['is_work'] = in_array($value['message_id'], $work_wid);
				// $message['data'][$key] = $value;
			// }
            
        }
		
		$this->assign('message', $message);
		$this->assign('type', intval($_POST['type']));
		$message['data'] = $message['data'] ? $this->fetch() : null;
		echo json_encode($message);
	}

	public function post() {
		$data['touid'] = intval($_GET['touid']);
		$data['gid'] = $_GET['gid'] ? intval($_GET['gid']) : '';
		$data['gtype'] = $_GET['gtype'];
		$this->assign('data',$data);
		$this->display();
	}

	public function doPost() {
		if (!lockSubmit()) {
			echo -1;
			exit;
		}
		if (empty($_POST['to']) || empty($_POST['content'])) {
			echo 0;
			exit;
		}
		$to_num = explode(',', $_POST['to']);
		if( sizeof($to_num)>10 ){
			echo '-2';
			exit;
		}
		$map['uid'] = $_POST['to'];
		$map['key'] = 'message'; 
		$res = M('user_privacy')->where($map)->find();
		unset($map);
		$map['uid'] = $_POST['to'];
		$map['fid'] = $this->mid;
		$result = M('weibo_follow')->where($map)->find();
		$black  = M('user_blacklist')->where($map)->find();
		if($res['value'] == 1 && !$result && !$balck){
			echo '-3';exit;
		}
		if($res['value'] == 0 && $black){
			echo '-3';exit;
		}
		//只保留A标签
		$_POST['content'] = safe($_POST['content'],'link');
		
		$res = model('Message')->postMessage($_POST, $this->mid);
		if ($res) {
			echo 1;
		}else {
			echo 0;
		}

		// NO unlockSubmit(); !!!
	}

	public function doReply() {
		if ( !$_POST['id'] || empty($_POST['reply_content']) ) {
			echo 0;
			exit;
		}

		$res = model('Message')->replyMessage( intval($_POST['id']), t($_POST['reply_content']), $this->mid );
		if ($res) {
			echo 1;
		}else {
			echo 0;
		}
	}

	public function doSetIsRead() {
		$res = model('Message')->setMessageIsRead(t($_POST['ids']), $this->mid);
		if ($res) echo 1;
		else	  echo 0;
	}

	public function doSetIsUnread() {
		/*$res = model('Message')->setMessageIsUnread(t($_POST['ids']));
		if ($res) echo 1;
		else	  echo 0;*/
	}

	public function doDelete() {
		$res = model('Message')->deleteMessageByListId($this->mid, t($_POST['ids']));
		if ($res) echo 1;
		else	  echo 0;
	}

	public function doDeleteSession()
	{
		$res = model('Message')->deleteSessionById($this->mid, t($_POST['ids']));
		if ($res) echo 1;
		else	  echo 0;
	}

	/* 通知 */
	public function notify()
	{
		$list = X('Notify')->get('receive=' . $this->mid, 10);
		// 解析表情
		foreach($list['data'] as $k => $v) {
			$list['data'][$k]['title'] = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['title']);
			$list['data'][$k]['body']  = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['body']);
			$list['data'][$k]['other'] = preg_replace_callback("/\[(.+?)\]/is",replaceEmot,$v['other']);
		}
		$this->assign($list);
		$this->setTitle(L('notifications'));
		$this->display();
	}

	//应用消息（漫游的邀请）
	public function appmessage() {
		$db_prefix	= C('DB_PREFIX');
		$sql		= "SELECT COUNT(appid) AS count,`appid`,`typename` FROM {$db_prefix}myop_myinvite WHERE `touid`={$this->mid} GROUP BY `appid`";
		$res		= M('')->query($sql);
		$my_count	= array();
		foreach ($res as $v) {
			$my_count[$v['appid']]	= $v;
		}

		$map['touid']	= $this->mid;
		$res	= M('myop_myinvite')->where($map)->order('appid DESC')->findPage('10');
		unset($map);
		// 将应用消息置为已读
		$appids = getSubByKey($res['data'], 'id');
		$map['touid'] = $this->mid;
		M('myop_myinvite')->where($map)->setField('is_read', '1');

		//修正邀请链接错误问题

		!defined(MYOP_URL) && define(MYOP_URL,$this->getmyopurl());

		foreach($res['data'] as $k=>$v){
			$myml = '';
			$myml = $v['myml'];
			$myml = str_ireplace(MYOP_URL, '', $myml);
			$myml = str_ireplace('userapp.php', MYOP_URL.'/userapp.php', $myml);
			$myml = preg_replace('/(invite[^\"]*)/', '#', $myml);
			$res['data'][$k]['myml']	=	$myml;
		}
		
		$user = M('login')->where("uid='". $this->mid."'")->find();
		
		if(!empty($user["type_uid"])){
		$account = $user["type_uid"];
		$ip = '010001030165';
		$mip = MD5($ip);
		$mcheckData = MD5($mip.$account);
		$this->assign('mcheckData',$mcheckData);
		
		}
		$this->assign('account',$account);
    	$this->assign($res);
    	$this->setTitle(L('app_message'));
		$this->display('appmessage');
	}

	private function getmyopurl(){
		$uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : ( $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'] );
		return $this->shtmlspecialchars('http://'.$_SERVER['HTTP_HOST'].substr($uri, 0, strrpos($uri, '/')));
	}

	private function shtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = shtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
			str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}
	public function deleteMyInvite() {
		$_POST['hash']	= t($_POST['hash']);
		$map['touid']	= $this->mid;
		$map['hash']	= $_POST['hash'];
		if ( M('myop_myinvite')->where($map)->find() && M('myop_myinvite')->where($map)->delete() ) {
			echo 1;
		}else {
			echo 0;
		}
	}

	//删除通知
	function delnotify(){
		$intNotifyId = intval( $_POST['notify_id'] );
		if(M('notify')->where('notify_id='.$intNotifyId.' AND receive='.$this->mid)->delete()){
			echo 1;
		}
	}
}