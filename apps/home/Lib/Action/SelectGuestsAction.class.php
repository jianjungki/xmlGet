<?php
class SelectGuestsAction extends Action {
	
        //------------------------------------------------以下是选择好友组件相关-----------------------------------
        public function getOne() {

				$name = t(urldecode($_GET['name']));
				
				$db_prefix  = C('DB_PREFIX');
				$Model      = M('');
				if($name){
					//从嘉宾中找				
					$guests = $Model->field('character.topics_id AS topicsid,character.uname AS funame')
										->table("{$db_prefix}weibo_character AS character ")
										->where("character.user_id={$this->mid} AND user.uname LIKE '%{$name}%' ")
										->order('follow.follow_id DESC')
										->limit(15)
										->findAll();
				}
                foreach($guests as $k=>$v) {
                        $out[$k]['fUid'] = $v['0'];
                        $out[$k]['friendUserName'] = $v['1'];
                        $out[$k]['friendHeadPic'] = getUserFace($v['2']);
                }

                echo json_encode($out);
        }

        public function getAll() {

                $typeId = intval($_GET['typeId']);
                $Model      = M('');
                $db_prefix  =  C('DB_PREFIX');
                $All = $Model->field('user.uid AS fuid,user.uname AS funame')
                ->table("{$db_prefix}user AS user ")
                ->order('user.uid DESC')
                ->findPage(12);	
				
               foreach($All['data'] as $k=>$v) {
                        $out[$k]['fUid'] = $v['fuid'];
                        $out[$k]['friendUserName'] = getShort($v['funame'],'16','…');
                        $out[$k]['friendFullName'] = $v['funame'];
                        $out[$k]['friendHeadPic'] = $this->get_photo_url($v['fuid']);
               }
                
               echo json_encode($out);

        }
        
        //----------------------------------------------组件 end----------------------------------------------
        //根据存储路径，获取照片真实URL
        function get_photo_url($savepath) {
        	$path   =  SITE_URL.'/data/uploads/' . $savepath;
        	if(!file_exists($path))
        		$path = SITE_URL.'/apps/group/Tpl/default/Public/images/group_pic.gif';
        	return $path;
        }


        public function getCount() {
                $db_prefix  =  C('DB_PREFIX');
                $Model      = M('');
				
				$followNum = $Model->field('user.uid AS fuid,user.uname AS funame')
				->table("{$db_prefix}user AS user ")
				->order('user.uid DESC')
				->count();
				
				echo $followNum;
        }
}
?>
