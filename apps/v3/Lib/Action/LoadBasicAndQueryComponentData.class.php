	/**
	 * 加载组件数据显示
	 *
	 */
	function LoadBasicAndQueryComponentData() {
		//获取当前用户的ID和姓名
				$fromUid = $this->mid;
				//获取要发送的好友ID，如有不明可参考'好友选择widget'的说明
				$toUserId = $_POST['fri_ids'];
				if(empty($toUserId)){
					$this->error('你还没有选择好友');
					exit;
				}
				//获取附加信息
				$sendInfo['sendInfo'] = t($_POST['sendInfo']);
				//获取发送方式
				$sendInfo['sendWay']  = t($_POST['sendWay']);
				
				//获取礼品ID并用t函数过滤
				$giftId =  t($_POST['giftId']);
				//查询数据库获取礼品的全部信息
				$giftInfo = $this->gift->where('id='.$giftId)->find();
				if($giftInfo['status']!=1){
					$this->error('该礼物已被禁用');
				}
				//发送礼品
				$result = $this->user_gift->sendGift($toUserId,$fromUid,$sendInfo,$giftInfo);     

				if($result===true){
					//如果发送成功就跳到‘送出的礼品’页面
					$this->assign('jumpUrl',U('/Index/sendbox'));
					global $ts;
					$this->success($ts['app']['app_alias'].'赠送成功！');
				}else{
					//如果发送失败就跳转到错误提示页并显示失败原因
					$this->error($result);
				}
	}