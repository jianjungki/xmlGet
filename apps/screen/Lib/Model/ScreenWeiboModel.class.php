<?php
/*********************************************************************************
 * ScreenWeiboModel.class.php
 * 微博大屏幕的微博相关操作
 * 当前数据库使用普通数据做存储，如果客户条件允许，可以将数据表改成内存表做存储
 * @uses
 * @encoding UTF-8
 * @version $id$
 * @copyright 2001-2013 sampeng
 * @author sampeng <penglingjun@zhishisoft.com>
 * @license PHP {@link www.zhishisoft.com}
 *********************************************************************************/
class ScreenWeiboModel extends Model
{
    const STATUS_SHOW = '1';
    const STATUS_HIDDEN = '0';
    private static $_all_field = 'weibo_id,screen_id,cTime,uid,status';


    /**
     * 添加微博到微博大屏幕中
     * @param int $screen_id
     * @param int $weibo
     */
    public function addWeibo($screen_id,$weibo_id,$uid,$status=self::STATUS_HIDDEN)
    {
        $this->_checkWeiboAndScreen($screen_id,$uid, $weibo_id);
        $cTime = time();
        $sql = "replace into ".C('DB_PREFIX')."screen_weibo set weibo_id={$weibo_id},screen_id={$screen_id},status = '".$status."',uid={$uid},cTime={$cTime}";
        return $this->execute($sql);
    }
    /**
     * 从屏幕墙上删除某条微博
     * @param unknown_type $screen_id
     * @param unknown_type $weibo
     */
    public function deleteWeibo($screen_id,$weibo_id,$uid)
    {
        $this->_checkWeiboAndScreen($screen_id, $uid,$weibo_id);
        $map['screen_id'] = $screen_id;
        $map['weibo_id']  = $weibo_id;
        $map['status']    = self::STATUS_HIDDEN;
        return $this->where($map)->delete();
    }

    /**
     * 从屏幕墙上删除所有微博
     * @param int $screen_id
     * @param int $uid
     */
    public function deleteAllWeibo($screen_id,$uid)
    {
        $this->_checkWeiboAndScreen($screen_id, $uid);
        $map['screen_id'] = $screen_id;
        $map['status']    = self::STATUS_HIDDEN;
        return $this->where($map)->delete();
    }

    /**
     * 获取用于显示的微博队列
     * @param int $screen_id
     * @param int $count
     */
    public function getShowWeiboQueue($screen_id,$uid,$timestemp = 0,$count = 0)
    {
        $this->_checkWeiboAndScreen($screen_id,$uid);
        $timestemp = $timestemp?"AND screen.cTime > {$timestemp}":"";
        $limit     = $count?"limit 0,{$count}":"";
        $sql = "SELECT screen.weibo_id,screen.cTime as screenTime,weibo.uid,weibo.content,weibo.ctime,weibo.type,weibo.type_data FROM `".C('DB_PREFIX')."screen_weibo` as screen
                left join ".C('DB_PREFIX')."weibo as weibo
                on weibo.weibo_id = screen.weibo_id
                WHERE ( screen.`screen_id` = {$screen_id} ) AND ( screen.`status` = '".self::STATUS_HIDDEN."' ) {$timestemp} AND (weibo.isdel = 0)
                ORDER BY screen.cTime ASC
                {$limit}";
        $data = $this->query($sql);
        return $data;
    }
    /**
     * 获取已经显示过的微博id
     * @param $screen_id
     * @param $weibo_id
     * @param $uid
     */
    public function getShowedWeiboId($screen_id,$weibo_id,$uid)
    {
        $this->_checkWeiboAndScreen($screen_id,$uid);
        $map['screen_id'] = $screen_id;
        $map['status']    = self::STATUS_SHOW;
        $map['weibo_id']  = array('in',$weibo_id);
        $res = $this->where($map)->field('weibo_id')->findAll();
        if(!$res) return false;
        $weibo_id_temp = getSubByKey($res,'weibo_id');
        return $weibo_id_temp;
    }



    private function _checkWeiboAndScreen($screen_id,$uid=null,$weibo_id = null)
    {
        $screen_id = intval($screen_id);
        if(empty($screen_id)) throw new ThinkException('操作必须和某个大屏幕相关');
        if(isset($weibo_id) && empty($weibo_id)) throw new ThinkException('操作必须是操作某个有效微博');
        if(isset($uid)){
            $map['screen_id'] = $screen_id;
            $res = D('Screen')->where($map)->field('uid')->find();
            $mid = $res['uid'];
            if($mid != $uid) throw new ThinkException("没有操作权限");
        }
    }
}