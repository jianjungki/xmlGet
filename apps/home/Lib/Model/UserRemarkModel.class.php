<?php 
class UserRemarkModel extends Model{
    var $tableName = 'user_remark';
    /*
     * 增加备注信息
     * jianjungki
     * 2012.11.16 
     */
    function addRemark($uid,$toid,$remark){
        $data['user_id'] = $uid;
        $data['edit_id'] = $toid;
        if($info = $this->where($data)->find()){
            $info['remark_info'] = $remark;
            $info['remark_time'] = time();
            $res = $this->data($info)->save();
        }else{
            $data['remark_info'] = $remark;
            $data['remark_time'] = time();
            $res = $this->data($data)->add();
        }
        
        if(!$res){
            echo 0;
        }else{
            echo 1;
        }
    }
    /*
     * 获取备注信息
     * jianjungki
     * 2012.11.16
     */
    function findRemark($uid,$toid,$remark){
        $data['user_id'] = $uid;
        $data['edit_id'] = $toid;
        $info = $this->where($data)->find();
        
        return $info['remark_info'];
    }
}
?>