<?php
class IndexAction extends AdministratorAction {
    //后台框架页
    public function index() {
    	$this->assign('channel', $this->_getChannel());
    	$this->assign('menu',    $this->_getMenu());
        $this->display();
    }

    //后台首页
    public function main() {
    	echo '<h2>这里是后台首页</h2>';
    	$this->display();
    }

    protected function _getChannel() {
			//add By lenghaoran 2011-01-16 判断用户是否为管理员
		$isSystemAdmin = service('SystemPopedom')->hasPopedom($this->mid, 'admin/*/*', false);
       if ($isSystemAdmin)
		{
			return array(
				'index'			=>	'首页',
				'global'		=>	'全局',
				'content'		=>	'内容',
				'user'			=>	'用户',
				'apps'			=>	'应用',
				'extension'		=>	'插件',
			);
			
		}
		else 
		{
				return array(
				'index'			=>	'首页',
				'global'		=>	'全局',
				'content'		=>	'内容',
				'user'			=>	'用户',
				'apps'			=>	'应用',
				'extension'		=>	'插件',
			);
		}
		

    }

    protected function _getMenu() {
    	$menu = array();
    	//注意顺序！！
      	$isSystemAdmin = service('SystemPopedom')->hasPopedom($this->mid, 'admin/*/*', false);
        if ($isSystemAdmin)
		{
			// 后台管理首页
			$menu['index'] 		=   array(
				'首页'			=>	array(
					'首页'		=>	U('admin/Home/statistics'),
					'缓存更新'    =>  SITE_URL . '/cleancache.php?all',
					'更新模板(V3配置)'   =>  SITE_URL . '/xmlcon.php',
					'系统升级'	 =>	U('admin/Home/update'),
					'数据备份'    =>  U('admin/Tool/backup'),
					'CNZZ统计'   =>  U('admin/Tool/cnzz'),
				),
			);
		} else{
			// 后台管理首页
			$menu['index'] 		=   array(
				'首页'			=>	array(
					'首页'		=>	U('admin/Home/statistics'),
					'缓存更新'    =>  SITE_URL . '/cleancache.php?all',		
				'CNZZ统计'   =>  U('admin/Tool/cnzz'),
				
				),
			);
		}


		

    	//全局
    	$menu['global'] 	=   array(
    		'全局配置'		=>  array(
    			'站点配置'	=>	U('admin/Global/siteopt'),
    			'注册配置'	=>	U('admin/Global/register'),
    			'邀请配置'	=>	U('admin/Global/invite'),
    			'积分配置'	=>	U('admin/Global/credit'),
    			'公告配置'	=>	U('admin/Global/announcement'),
    			'邮件配置'	=>	U('admin/Global/email'),
    			'附件配置'	=>	U('admin/Global/attachConfig'),
				'文章配置'	=>	U('admin/Global/document'),
    			'审核配置'	=>	U('admin/Global/audit'),
                '地区配置'    =>  U('admin/Tool/area'),
                '分类配置' =>  U('admin/Global/configdict'),
                '粉丝榜配置' =>  U('admin/User/follower'),
    		),
    	);

       if ($isSystemAdmin)	{
			//内容
			$menu['content'] 	=   array(
				'内容管理'		=>  array(
					'广告管理'	=>	U('admin/Content/ad'),
					'模板管理'	=>	U('admin/Content/template'),
					'附件管理'	=>	U('admin/Content/attach'),
					'评论管理'	=>	U('admin/Content/comment'),
					'私信管理'	=>	U('admin/Content/message'),
					'通知管理'	=>	U('admin/Content/notify'),
					'动态管理'	=>	U('admin/Content/feed'),
					'举报管理'	=>	U('admin/Content/denounce'),
					'管理日志'   =>	U('admin/Content/adminLog'),
				),
    	);
	   }else {
		    //内容
			$menu['content'] 	=   array(
    		'内容管理'		=>  array(
				'广告管理'	=>	U('admin/Content/ad'),    			
    			'附件管理'	=>	U('admin/Content/attach'),
    			'评论管理'	=>	U('admin/Content/comment'),
    			'私信管理'	=>	U('admin/Content/message'),
    			'通知管理'	=>	U('admin/Content/notify'),
    			'动态管理'	=>	U('admin/Content/feed'),
    			'举报管理'	=>	U('admin/Content/denounce'),    			
    		),
       	  );
		}

    
		  if ($isSystemAdmin)	{
			  	//用户
			$menu['user']		=	array(
				'用户'			=>	array(
					'组织管理'	=>	U('admin/User/orguser'),
					'用户管理'	=>	U('admin/User/user'),
					'组织认证管理'  =>  U('admin/User/GetVerified'),
					'用户组管理'	=>	U('admin/User/userGroup'),
					'资料配置'	=>	U('admin/User/setField'),
					'消息群发'	=>	U('admin/User/message'),
					'邀请统计'    =>  U('admin/Tool/inviteRecord'),
					'登陆日志'	=>	U('admin/Login/index'),
				),
				'权限'			=>	array(
					'节点管理'	=>	U('admin/User/node'),
					'权限管理'	=>	U('admin/User/popedom'),
				),
			);
		   }else {
			$menu['user']		=	array(
				'用户'			=>	array(
					'组织管理'	=>	U('admin/User/orguser'),
					'用户管理'	=>	U('admin/User/user'),
					'组织认证管理'  =>  U('admin/User/GetVerified'),
					'用户组管理'	=>	U('admin/User/userGroup'),
					'资料配置'	=>	U('admin/User/setField'),
					'消息群发'	=>	U('admin/User/message'),
					'邀请统计'    =>  U('admin/Tool/inviteRecord'),
					
				)
			);
           }
      if ($isSystemAdmin)	{
			//应用
			$menu['apps'] 		=	array(
				'应用'		=>	array(
					'应用列表'	=>	U('admin/Apps/applist'),
					'应用安装'	=>	U('admin/Apps/install'),
				),
			);

				$menu['apps']['应用']['漫游平台'] = SITE_URL . '/apps/myop/myop.php?my_suffix=' . urlencode('/appadmin/list');
				$menu['apps']['应用']['微博'] = U('weibo/Admin/index');

				$apps = model('App')->getAdminApp('app_name,app_alias,admin_entry,host_type');
				foreach ($apps as $v) {
					if($v['host_type']==0){
						if ($v['app_name']=='group') {
	                        $menu['apps']['应用'][$v['app_alias']] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=group';
							$menu['apps']['应用']['项目'] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=project';
							$menu['apps']['应用']['组织'] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=org';
	                    }else{
	                    	$menu['apps']['应用'][$v['app_alias']] = U($v['app_name'].'/'.$v['admin_entry']);
	                    }
					}
					
				}
          }else {
           			//应用
			$menu['apps'] 		=	array(
				'应用'		=>	array(
					'应用列表'	=>	U('admin/Apps/applist'),
				),
			);
		
				$menu['apps']['应用']['微博'] = U('weibo/Admin/index');

				$apps = model('App')->getAdminApp('app_name,app_alias,admin_entry,host_type');
				foreach ($apps as $v) {
					if($v['host_type']==0){
						if ($v['app_name']=='group') {
	                        $menu['apps']['应用'][$v['app_alias']] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=group';
							$menu['apps']['应用']['项目'] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=project';
							$menu['apps']['应用']['组织'] = U($v['app_name'].'/'.$v['admin_entry']) . '&gtype=org';
	                    }else{
	                    	$menu['apps']['应用'][$v['app_alias']] = U($v['app_name'].'/'.$v['admin_entry']);
	                    }
					}
					
				}
		  }

    	$menu['extension']	=	array(
    		'插件'			=>	array(
    			'插件列表'   =>  U('admin/Addons/index'),
    		),
    	);

    		$addons = model('Addons')->getAddonsAdmin();
        	foreach($addons as $value){
    			$menu['extension']['插件'][$value[0]] = U('admin/Addons/admin',array('pluginid' => $value[1]));
    		}


    	return $menu;
    }
}
