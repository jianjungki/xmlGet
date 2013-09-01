<?php
/*
 * 问题模型，用于获取列表信息
 * @author:jianjungki
 * @time:2012/11/06
 */
class ProblemModel extends Model{
    var $tableName = "weibo_problem";
    /*
     * 用法：获取全部问题
     * @param $topic_id 话题id
     * @param $num 分页数
     * @param $order　排序方法
     * 返回问答微博列表
     */
    function GetAllList($topic_id,$order){
        $map['topics_id'] = $topic_id;
        $map['is_display'] = 1;//可以显示
        $map['content_type'] = 0;
        $problem  = $this->where($map)->order($order)->findPage();
        //dump($this);
        $map['content_type'] = 1;
        $map['guest_time'] = array("exp","is not NULL");
        $map['content'] = array("exp","is not NULL");
        foreach ($problem['data'] as $key => $value) {
            $map['weibo_id'] = $value['weibo_id'];
            $problem['data'][$key]['answer']  = $this->where($map)->findAll();
        }
        return $problem;
    }
    /*
     * 用法：传递参数获取主持人列表
     * @param $topic_id 话题id
     * @param $order 排序方式
     * @param $num 返回条数
     */
    function GetHostList($topic_id,$status,$order,$num){
        $map['topics_id'] = $topic_id;
        $map['content_type'] = 0;
        if($status=="answered")
            $map['reply_time'] = array("neq",0);
        else if($status=="notanswer")
            $map['reply_time'] = 0;
        if($order=="up")
            $orderit = "problem_id";
        else if($order=="down")
            $orderit = "problem_id desc";
        $infolist = $this->where($map)->order($orderit)->findPage();
        $map['content_type'] = 1;
        $map['guest_time'] = array("exp","is not NULL");
        $map['content'] = array("exp","is not NULL");
        foreach ($infolist['data'] as $key => $value) {
            $infolist['data'][$key]['answers'] = $this->where("weibo_id = ".$value['weibo_id']." AND content_type = 1 AND content is not NULL")->count();
            $map['weibo_id'] = $value['weibo_id'];
            $infolist['data'][$key]['answer'] = $this->where($map)->findAll();
        }
        //dump($this);
        return $infolist;
        
    }
    /*
     * 用法：传递参数获取嘉宾列表
     * @param $topic_id 话题id
     * @param $order 排序方式
     * @param $num 返回条数
     * 返回主持人微博列表
     */
    function GetGuestList($topic_id,$guest_id,$status,$order,$num){
        $map['topics_id'] = $topic_id;
        $map['content_type'] = 1;
        $map['is_display'] = 1;//可以显示
        $map['guest_id'] = $guest_id;
        if($status=="answered")
            $map['reply_time'] = array("neq",0);
        else if($status=="notanswer")
            $map['reply_time'] = 0;
        //回复与未回复筛选
        if($order=="up")
            $orderit = "problem_id";
        else if($order=="down")
            $orderit = "problem_id desc";
        $infolist = $this->where($map)->order($orderit)->findPage();
        foreach ($infolist['data'] as $key => $value) {
            $infolist['data'][$key]['answers'] = $this->where("weibo_id = ".$value['weibo_id']." AND content_type = 1 AND content is not NULL")->count();
        }
        return $infolist;
    }
    
    /*
     * 用法：屏蔽微访谈问题
     * @param $topic_id 话题id
     * @param $weibo_id 微博的id
     * 微博被屏蔽
     */
    function NotDisplay($weibo_id){
        $map['weibo_id'] = $weibo_id;
        $is_display = $this->where($map)->find();
        if($is_display['is_display']==1){
            $info = $this->where($map)->setField("is_display",0);
            if(false != $info){
                return 1;
            }else{
                return 2;
            }
        }else{
            $info = $this->where($map)->setField("is_display",1);
            if(false != $info){
                return 3;
            }else{
                return 4;
            }
        }
            
        
        
    }
    
    /*
     * 用法：更换回答问题的嘉宾
     * @param $weibo_id 微博的id
     * 微博被屏蔽
     */
    function ExchangePro($weibo_id){
        
        $info = $this->where()->order()->findPage($num);
        
    }
}
