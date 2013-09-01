<?php
class ScreenModel extends Model
{
	const TYPE_ALL        = 0;  //全部案例
	const TYPE_RUNING     = 1;  //正在进行
	const TYPE_WILL_START = 2;  //即将开始
	const TYPE_OLD        = 3;  //历史回顾
	const STATUS_PASS     = '1'; //通过审核的微博大屏幕
	const STATUS_NO_PASS  = '0'; //未通过审核的微博大屏幕


	const FILE_CACHE_PREFIX = "screen_";
	const DEFAULT_LIST_COUNT = 20;
	const DEFAULT_RIGHT_COUNT = 6;

	/**
	 * 申请微博大屏幕
	 * @param unknown_type $data
	 * @throws ThinkException
	 */
	public function apply($data)
	{
		if($data['time_start'] >= $data['time_end']) throw new ThinkException("开始时间必须小于结束时间");
		if(empty($data['time_start'])) throw new ThinkException("必须有开始时间");
		if(empty($data['time_end'])) throw new ThinkException("必须有结束时间");
		if(empty($data['title']) ) throw new ThinkException("请输入标题");
		if(empty($data['uid']) || empty($data['cTime'])) throw new ThinkException("这是一个异常操作");
		if(empty($data['topic_id'])) throw new ThinkException("必须绑定一个话题");
		if(empty($data['keyword'])) throw new ThinkException("必须绑定关键词");

		$result = $this->add($data);
		if($result)
		    F(self::FILE_CACHE_PREFIX."count",null);
		return $result;
	}

	public function del($id){
	    if(!empty($id)){
	        //先删除微博，再删大屏幕
	        $map['screen_id'] = array('in',$id);
	        $resWeibo = D('ScreenWeibo')->where($map)->delete();
	        $resScreen = $this->where($map)->delete();
	        return $resScreen;
	    }
	    return false;
	}


	/**
	 * 获得所有已经通过审核的微博大屏幕数
	 */
	public function getScreenCount()
	{
		$data = F(self::FILE_CACHE_PREFIX."count");

		if(!$data ){
			$sql = "select count(1) as count from ".C('DB_PREFIX')."screen where status='".self::STATUS_PASS."'";
			$data = $this->query($sql);
			F(self::FILE_CACHE_PREFIX."count",$data);
		}
		return $data['0']['count'];
	}


    public function getScreen($screen_id)
    {
        $this->_checkScreenExists($screen_id);
        $sql = 'select screen.screen_id,screen.uid,screen.title,screen.time_start,screen.time_end,screen.explains,screen.logo,screen.keyword,topic.name as topic
                from '.C('DB_PREFIX').'screen as screen
                left join '.C('DB_PREFIX').'weibo_topic as topic
                on topic.topic_id = screen.topic_id
                where screen_id='.$screen_id." and screen.status='".self::STATUS_PASS."'";

        $result = $this->query($sql);

        $result =  $this->_paramDataForOther($result);
        return $result[0];

    }
	/**
	 * 获取首页的数据
	 * @param int $type self::TYPE_ALL | self::TYPE_RUNING | self::TYPE_WILL_START | self::TYPE_OLD
	 * @param string $order
	 */
	public function getScreenList($type = self::TYPE_ALL,$order = 'cTime DESC',$count = self::DEFAULT_LIST_COUNT)
	{
		$data = $this->where($this->_getTypeTimestemp($type))->order($order)->findPage($count);
		if($data['count']>0){
		    $data['data'] = $this->_paramDataForOther($data['data']);
		}
		return $data;
	}

	/**
	 * 搜索得到列表数据
	 * @param int $type self::TYPE_ALL | self::TYPE_RUNING | self::TYPE_WILL_START | self::TYPE_OLD
	 * @param string $order
	 */
	public function searchScreenList($title ,$order = 'cTime DESC',$count = self::DEFAULT_LIST_COUNT)
	{
	    $where = $this->_getTypeTimestemp(self::TYPE_ALL);
        $where .= ' AND title like "%'.$title.'%"';
	    $data = $this->where($where)->order($order)->findPage($count);
	    if($data['count']>0){
	        $data['data'] = $this->_paramDataForOther($data['data']);
	    }
	    return $data;
	}



	/**
	 * 获取推荐的大屏幕
	 * @param int $count
	 */
	public function getRcommend($count = self::DEFAULT_RIGHT_COUNT)
	{
	    $rand = array();
	    $randTemp = array();
	  //  $sql = 'SELECT screen_id FROM '.C('DB_PREFIX').'screen ORDER BY RAND() limit 5';
	    
	    $sql = 'SELECT a.screen_id  from '.C('DB_PREFIX').'screen a 
	    		WHERE a.screen_id >  ((SELECT MAX(screen_id) FROM '.C('DB_PREFIX').'screen)-(SELECT MIN(screen_id) FROM '.C('DB_PREFIX').'screen)) * RAND() + (SELECT MIN(screen_id) FROM '.C('DB_PREFIX').'screen) 			
			LIMIT 5';
	  
	    $temp_rand = $this->query($sql);
	    $rand = getSubByKey($temp_rand,'screen_id');
        $map['screen_id'] = array('in',$rand);
        $db_data = $this->where($map)->order('NULL')->field('title,uid,logo,screen_id')->findAll();

        //重新排序成随机的
        $result = array();
        foreach($db_data as $key=>$value){
           $result[array_search($value['screen_id'],$rand)] = $value;
        }
        sort($result);
        return $this->_paramDataForOther($result);
	}


	private function _paramDataForOther($data)
	{
        foreach($data as $key=>$value){
            $data[$key]['type'] = $this->_paramDeadlineForType($value['time_start'], $value['time_end']);
        }
        return $data;
	}


	private function _paramDeadlineForType($start,$end)
	{
	    $time = time();
	    if($start <= $time && $end > $time ){
	        return self::TYPE_RUNING;
	    }else if($end < $time){
	        return self::TYPE_OLD;
	    }else if($start > $time){
	        return self::TYPE_WILL_START;
	    }else{
	        return self::TYPE_ALL;
	    }
	}
	private function _getTypeTimestemp($type,$table=null)
	{
		$time = time();
		if(isset($table) && is_string($table) && !empty($table)){
		    $table = $table.".";
		}else{
		    $table = null;
		}
		switch ($type){
			case self::TYPE_ALL:
				return $table."status='".self::STATUS_PASS."'";
			case self::TYPE_RUNING:
				return sprintf("{$table}status = '%s' and {$table}time_start <= %s and {$table}time_end >=%s",self::STATUS_PASS,$time,$time);
			case self::TYPE_WILL_START:
				return sprintf("{$table}status = '%s' and {$table}time_start > %s",self::STATUS_PASS,$time);
			case self::TYPE_OLD;
				return sprintf("{$table}status = '%s' and {$table}time_end <%s",self::STATUS_PASS,$time);
			default:
				return "status='".self::STATUS_PASS."'";
		}
	}

	private function _checkScreenExists($screen_id)
	{
	    $screen_id = intval($screen_id);
	    if(empty($screen_id)) throw new ThinkException("该微博大屏幕并不存在");
	}
}