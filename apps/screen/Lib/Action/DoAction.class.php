<?php
/*********************************************************************************
 * DoAction.class.php
 * 操作大屏幕的动作
 * @uses
 * @encoding UTF-8
 * @version $id$
 * @copyright 2001-2013 sampeng
 * @author sampeng <penglingjun@zhishisoft.com>
 * @license PHP {@link www.zhishisoft.com}
 *********************************************************************************/
class DoAction extends Action
{
    public function apply(){
        $data['time_start'] = $this->_paramTimestamp($_POST['start_time']);
        $data['time_end']   = $this->_paramTimestamp($_POST['end_time'],true);
        $data['title']      = t($_POST['title']);
        $data['uid']        = $this->mid;
        $data['topic_id']      = D('Topic','weibo')->getTopicId(t($_POST['topic']));
        $data['keyword']    = t(implode(';',explode("<br />",nl2br($_POST['keyword']))));
        $data['cTime']      = time();
        $data['logo']       = intval($_POST['logo']);
        $data['explains']   = t($_POST['explains']);
        try{
            $result = D('Screen')->apply($data);
            if($result){
                $this->ajaxData['url'] = U('screen/index/detail',array('id'=>$result));
                $this->success("添加成功");
            }else{
                $this->error("添加失败");
            }
        }catch(ThinkException $e){
            $this->error($e->getMessage());
        }
    }

    public function deleteQueue()
    {
        $screen_id = intval($_POST['screen_id']);
        $weibo_id  = intval($_POST['weibo_id']);
        try{
            $res = D('ScreenWeibo')->deleteWeibo($screen_id,$weibo_id,$this->mid);
            $this->success("删除成功");
        }catch(ThinkException $e){
            $this->error($e->getMessage());
        }
    }

    public function deleteAllQueue()
    {
        $screen_id = intval($_POST['screen_id']);
        $weibo_id  = intval($_POST['weibo_id']);
        try{
            $res = D('ScreenWeibo')->deleteAllWeibo($screen_id,$this->mid);
            $this->success("删除成功");
        }catch(ThinkException $e){
            $this->error($e->getMessage());
        }
    }

    public function addWeiboToScreen(){
        $screen_id = intval($_POST['screen_id']);
        $weibo_id  = intval($_POST['weibo_id']);
        try{
            $res = D('ScreenWeibo')->addWeibo($screen_id,$weibo_id,$this->mid);
            $data = D('Weibo','weibo')->getOne($weibo_id);
            $this->ajaxData['a'] = getUserSpace($data['uid'], 'username', '_self', '{uname}',false);
            $this->ajaxData['uid'] = $data['uid'];
            $this->ajaxData['content']  = $data['content'];
            $this->ajaxData['time']     = friendlyDate($data['ctime']);
            if($res){
                $this->success("添加成功");
            }else{
                $this->error("该微博已添加,请问是否重新添加");
            }
        }catch(ThinkException $e){
            $this->error($e->getMessage());
        }
    }

    public function setWeiboIsShow(){
        $screen_id = intval($_POST['screen_id']);
        $weibo_id  = intval($_POST['weibo_id']);
        if($_SESSION['screen_'.$screen_id] == "start"){
            try{
                $res = D('ScreenWeibo')->addWeibo($screen_id,$weibo_id,$this->mid,ScreenWeiboModel::STATUS_SHOW);
                $data = D('Weibo','weibo')->getOne($weibo_id);
                $this->ajaxData['username'] = getUserSpace($data['uid'], 'username', '_self', '{uname}',false);
                $this->ajaxData['userface'] = getUserFace($data['uid']);
                $this->ajaxData['content']  = $data['content'];
                if($data['type'] ==1){
                    $temp = unserialize($data['type_data']);
                    $this->ajaxData['pic'] = sprintf('<img src="'.SITE_URL.'/data/uploads/'.$temp['thumburl'].'" class="right" width="120px" height="120px" style="border-radius:3px;border:#fff solid 1px"/>');
                }

                if($res){
                    $this->success("添加成功");
                }else{
                    $this->error("修改失败");
                }
            }catch(ThinkException $e){
                $this->error($e->getMessage());
            }
        }
        $this->error("暂停了");
    }

    public function start(){
       $_SESSION['screen_'.$_REQUEST['screen_id']] = "start";
    }

    public function stop(){
       unset($_SESSION['screen_'.$_REQUEST['screen_id']]);
    }

    private function _paramTimestamp($data,$end=false)
    {
        $data = explode("-", $data);
        if($end){
            return mktime(23,23,59,$data[1],$data[2],$data[0]);
        }else{
            return mktime(null,null,null,$data[1],$data[2],$data[0]);
        }
    }
}