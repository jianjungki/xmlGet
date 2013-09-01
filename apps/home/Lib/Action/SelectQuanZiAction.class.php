<?php
class SelectQuanZiAction extends Action {
        //------------------------------------------------以下是选择好友组件相关-----------------------------------
        public function getOne() {

				$name = t(urldecode($_GET['name']));
				$Model      = M('');
                $db_prefix  = C('DB_PREFIX');
				if($name){
					//从我的圈子			
					$mygroup = $Model->field('quanzi.id AS fuid,quanzi.name AS funame,quanzi.logo AS fpic')
                                        ->table("{$db_prefix}group AS quanzi LEFT JOIN {$db_prefix}group_member AS member ON quanzi.id=member.gid")
                                        ->where("member.uid={$this->mid} AND member.status = 1 AND quanzi.name LIKE '%{$name}%' AND quanzi.deputyorgid is NULL")
										->order('quanzi.id DESC')
										->limit(10)
										->findAll();
                    
				}
                //合并，并过滤重复
                is_array($mygroup) || $mygroup=array();//查询返回空时，不为数组，则需转为空数组
                foreach($mygroup as $k=>$v) {
                        $out[$k]['fUid'] = $v['0'];
                        $out[$k]['friendUserName'] = $v['1'];
                        $out[$k]['friendHeadPic'] = $this->get_photo_url($v['2']);
                }
				
                echo json_encode($mygroup);
        }

        public function getAll() {
                $db_prefix  = C('DB_PREFIX');
                $typeId = intval($_GET['typeId']);
				$group = M('')->field('quanzi.id AS fuid,quanzi.name AS funame,quanzi.logo AS fpic')
                                  ->table("{$db_prefix}group AS quanzi LEFT JOIN {$db_prefix}group_member AS member ON quanzi.id=member.gid")
                                   ->where("member.uid = {$this->mid} AND member.status = 1 AND quanzi.deputyorgid is NULL")
								   ->order('quanzi.id DESC')
								   ->findPage(12);				
                foreach($group['data'] as $k=>$v) {
                        $out[$k]['fUid'] = $v['fuid'];
                        $out[$k]['friendUserName'] = getShort($v['funame'],'16','…');
                        $out[$k]['friendFullName'] = $v['funame'];
                        $out[$k]['friendHeadPic'] = $this->get_photo_url($v['fpic']);
                }
                echo json_encode($out);

        }

        public function getType() {
				$typeId = array(
							    array('id'=>1,'name'=>L('attend_group')),
				);
                echo json_encode( $typeId );
        }

        public function getCount() {
                $db_prefix  = C('DB_PREFIX');
                $typeId = intval($_GET['typeId']);
				$groupNum = M('')->field('quanzi.id AS fuid,quanzi.name AS funame,quanzi.logo AS fpic')
				                 ->table("{$db_prefix}group AS quanzi LEFT JOIN {$db_prefix}group_member AS member ON quanzi.id=member.gid")
                                   ->where("member.uid = {$this->mid} AND member.status = 1 AND quanzi.deputyorgid is NULL")
				                 ->order('quanzi.id DESC')->count();				
				
				echo $groupNum;
        }

        //----------------------------------------------组件 end----------------------------------------------
        //根据存储路径，获取照片真实URL
        function get_photo_url($savepath) {
            $path   =  SITE_URL.'/data/uploads/' . $savepath;
            if(!file_exists($path))
               $path = SITE_URL.'/apps/group/Tpl/default/Public/images/group_pic.gif';
            return $path;
        }
}
?>
