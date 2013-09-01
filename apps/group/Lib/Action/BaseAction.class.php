<?php
    /**
     * <b>注意:$this->未显示定义的变量名将会取值。对应变量名的model对象</b>
     * @uses Action
     * @package Action::group
     * @version $id$
     * @copyright 2009-2011 ThinkSNS
     * @author songhongguang
     */
    class BaseAction extends Action
    {
        protected $gid;
        protected $grouptype;
        /**
         * __initialize
         * 初始化
         * @access public
         * @return void
         */
        protected $isadmin;
        protected $ismember;
        protected $config;
		protected $projectConfig;
		protected $orgConfig;
        protected $groupinfo;
        protected $siteTitle;
        protected $is_invited;

        protected function _initialize()
        {
            $this->grouptype = 0;
        	$this->assign('all_my_app', D('Group')->getAllMyGroup($this->mid, 0));
			
			/*
			 * 缓存圈子、项目、组织ID
			 */
			 
			 if(!($cache = S('Cache_GroupType'))){
					$group = D('Group','group')->field('id')->where('grouptype=0')->select();
				 	$cache['group'] = getSubByKey($group,'id');
					$cache['group'] = implode(',', $cache['group']);
					$project = D('Group','group')->field('id')->where('grouptype=1')->select();
				 	$cache['project'] = getSubByKey($project,'id');
					$cache['project'] = implode(',', $cache['project']);
					$org = D('Group','group')->field('id')->where('grouptype=2')->select();
				 	$cache['org'] = getSubByKey($org,'id');
					$cache['org'] = implode(',', $cache['org']);
					S('Cache_GroupType',$cache,120);
				}
        	// 群组id
            if (isset($_GET['gid']) && intval($_GET['gid']) > 0) {
            	$this->gid = intval($_GET['gid']);
            } else if (isset($_POST['gid']) && intval($_POST['gid']) > 0) {
              	$this->gid = intval($_POST['gid']);
            } else {
              	$this->error('gid 错误');
            }

            $groupinfo = D('Group')->where('id='.$this->gid." AND is_del=0")->find();
            /* + ------ 后台全局限制 START */
            $groupinfo['openWeibo']      = model('Xdata')->get('group:weibo') ? $groupinfo['openWeibo'] : 0;
            $groupinfo['openBlog']       = model('Xdata')->get('group:discussion') ? $groupinfo['openBlog'] : 0;
            $groupinfo['openUploadFile'] = model('Xdata')->get('group:uploadFile') ? $groupinfo['openUploadFile'] : 0;
            /* + ------ 后台全局限制 END */
            
            $label = getLabel($this->gid);
			switch ($label) {
				case '项目':
					$act = 'projectIndex';
					break;
				
				default:
					$act = 'newIndex';
					break;
			}

            if (!$groupinfo) {
              $this->assign('jumpUrl', U('group/Index/' . $act));
            	$this->error('该' . $label . '不存在，或者被删除');
            } else if (0 == $groupinfo['status'] && !in_array(ACTION_NAME,array('delGroupDialog', 'delProjectDialog','delGroup'))) {
              $this->assign('jumpUrl', U('group/Index/' . $act));
		 		      $this->error("该" . $label . "正在审核中");
		 	}//}{$GLOBALS['ts']['app']['app_alias']}
            
          	// 判读当前用户的成员状态
          	$member_info = D('Member')->where("uid={$this->mid} AND gid={$this->gid}")->find();
          	if ($member_info) {
          		if ($member_info['level'] > 0) {
            		$this->ismember = 1;
         			$this->assign('ismember', $this->ismember);
         			if ($member_info['level'] == 1 || $member_info['level'] == 2) {
         				$this->isadmin = 1;
         				$this->assign('isadmin', $this->isadmin);
         			}
		            // 记录访问时间
		            D('Member')->where('gid=' . $this->gid." AND uid={$this->mid}")->setField('mtime',time());
          		}
          	}

            $groupinfo['cname0'] 	= D('Category')->getField('title', array('id'=>$groupinfo['cid0']));
            $groupinfo['cname1'] 	= D('Category')->getField('title', array('id'=>$groupinfo['cid1']));
            $groupinfo['type_name'] = $groupinfo['brower_level'] == -1?'公开':'私密';
            $groupinfo['tags']		= D('GroupTag')->getGroupTagList($this->gid);
            $groupinfo['openUploadFile'] = (model('Xdata')->get('group:uploadFile')) ? $groupinfo['openUploadFile'] : 0 ;
            $groupinfo['path'] = D('Category')->getPathWithCateId($groupinfo['cid1']);
            $groupinfo['path'] = implode(' - ', $groupinfo['path']);
            $this->groupinfo = $groupinfo;
			
            $this->assign('groupinfo',$groupinfo);
            $this->assign('gid',$this->gid);
			$this->setTitle($this->groupinfo['name']);

          	// 浏览权限
          	if (!$this->ismember) {
            	// 邀请加入
            	if (M('group_invite_verify')->where("gid={$this->gid} AND uid={$this->mid} AND is_used=0")->find()) {
            		$this->is_invited = 1;
	            	$this->assign('is_invited', $this->is_invited);
            	}
	            if ($groupinfo['brower_level'] == 1) {
            		if (MODULE_NAME != 'Group' || (ACTION_NAME != 'index' && ACTION_NAME != 'joinGroup')) {
            			if(ACTION_NAME!='loadmore'){
            				$this->redirect('group/Group/index', array('gid'=>$this->gid));
            			}else{
            				exit();
            			}
            		} else if('index' == ACTION_NAME) {
		            	$this->display();
		            	exit;
            		}
	            }
          	}

            // 右侧部分信息，根据模块需求调用
            if (!in_array(MODULE_NAME, array('Manage','Log'))) {
            	// 群内热议话题
            	$this->assign('hot_weibo_topic_list', D('WeiboTopic')->getHot($this->gid));
            	// 最新加入
            	$this->assign('new_member_list', D('Member')->getNewMemberList($this->gid));
            }
            
            $class = $this->mainNavClass();

            $this->assign($class);

            // 基本配置
            $this->assign('config',null);
            $this->config = model('Xdata')->lget('group');
        	$this->assign('groupConfig',$this->config);
			$this->projectConfig = model('Xdata')->lget('project');
        	$this->assign('projectConfig',$this->projectConfig);
			$this->orgConfig = model('Xdata')->lget('org');
        	$this->assign('orgConfig',$this->orgConfig);
        }

        protected function base()
        {
        	/*
			 * 缓存圈子、项目、组织ID
			 */
			 
			 if(!($cache = S('Cache_GroupType'))){
					$group = D('Group','group')->field('id')->where('grouptype=0')->select();
				 	$cache['group'] = getSubByKey($group,'id');
					$cache['group'] = implode(',', $cache['group']);
					$project = D('Group','group')->field('id')->where('grouptype=1')->select();
				 	$cache['project'] = getSubByKey($project,'id');
					$cache['project'] = implode(',', $cache['project']);
					$org = D('Group','group')->field('id')->where('grouptype=2')->select();
				 	$cache['org'] = getSubByKey($org,'id');
					$cache['org'] = implode(',', $cache['org']);
					S('Cache_GroupType',$cache,120);
				}	
			
        	$this->grouptype = 0;
            $class = $this->mainNavClass();

            $this->assign($class);
        	$this->assign('need_login',1);
			// 系统的配置文件
        	$this->assign('config',null);
            $this->config = model('Xdata')->lget('group');
        	$this->assign('groupConfig',$this->config);
			$this->projectConfig = model('Xdata')->lget('project');
        	$this->assign('projectConfig',$this->projectConfig);
			$this->orgConfig = model('Xdata')->lget('org');
        	$this->assign('orgConfig',$this->orgConfig);

      		$this->siteTitle = getSiteTitle();
			
      		$this->assign('all_my_app', D('Group')->getAllMyGroup($this->mid));
        }

        private function mainNavClass(){

            $data['index_class'] = MODULE_NAME == "Index" && ACTION_NAME == "newIndex"?"on":'off';
            $data['my_class']    = $this->gid || MODULE_NAME == "SomeOne" ?"on":"off";
            $data['message_class']  = MODULE_NAME == "Index" && in_array(ACTION_NAME, 
            	array("newly","index","message","projectMessage","orgMessage","post","projectPost","projectReplied","replied","comment","projectComment","orgComment","atme","projectAtme","orgAtme","bbsNotify","projectBbsNotify","orgBbsNotify"))?"on":"off";
			
			$data['pro_index_class'] = MODULE_NAME == "Index" && ACTION_NAME == "projectIndex"?"on":'off';
            return $data;
        }

        //执行单图上传操作
	   protected function _upload($path,$save_name,$is_replace,$is_thumb,$thumb_name,$thumb_max_width)
	   {
			if(!checkDir($path)){
				return '目录创建失败: '.$path;
			}

			import("ORG.Net.UploadFile");

	        $upload = new UploadFile();

	        //设置上传文件大小
	        $upload->maxSize	=	'2000000' ;

	        //设置上传文件类型
	        $upload->allowExts	=	explode(',',strtolower('jpg,gif,png,jpeg,bmp'));

			//存储规则
			$upload->saveRule	=	'uniqid';
			//设置上传路径
			$upload->savePath	=	$path;
	        //保存的名字
	        $upload->saveName   =   $save_name;
	        //是否缩略图
	        $upload->thumb          =   $is_thumb;
	        $upload->thumbMaxWidth  =   $thumb_max_width;
	        $upload->thumbFile      =   $thumb_name;

	        //存在是否覆盖
	        $upload->uploadReplace  =   $is_replace;
	        //执行上传操作
	        if(!$upload->upload()) {
	            //捕获上传异常
	            return $upload->getErrorMsg();
	        }else{
				//上传成功
				return $upload->getUploadFileInfo();
	    	}
	    }

	    protected function _getSearchKey($key_name = 'k',$prefix="group_search") {
	    	$key = '';
		    // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
			if ( isset($_REQUEST[$key_name]) && !empty($_REQUEST[$key_name]) ) {
				if($_GET[$key_name]){
					$key = html_entity_decode( urldecode($_GET[$key_name]) ,ENT_QUOTES);
				}elseif($_POST[$key_name]){
					$key = $_POST[$key_name];
				}
				// 关键字不能超过30个字符
				if ( mb_strlen($key, 'UTF8') > 30 )
					$key = mb_substr($key, 0, 30, 'UTF8');
				$_SESSION[$prefix.'_' . $key_name] = serialize( $key );
			}else if ( is_numeric($_GET[C('VAR_PAGE')]) ) {
				$key = unserialize( $_SESSION[$prefix.'_' . $key_name] );
			} else {
        unset($_SESSION[$prefix.'_' . $key_name]);
      }
        	$this->assign('search_key', h(t($key)));
			return trim($key);
	    }
 	}