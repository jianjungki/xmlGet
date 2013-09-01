<?php
/*
 * 认证信息记录表
 * @author:jianjungki
 * @time:2012/11/17
 */
class VerifiedLogModel extends Model{
    var $tableName = "orglog_verified";
    /*
     * 存储组织用户认证信息，用于后面的审核
     * @author:jianjungki
     * @time:2012.11.17
     */
    public function SaveLog($data){
        return $this->data($data)->add();
    }
    
    /*
     * 获取用户认证信息列表，用于审核
     * @author:jianjungki
     * @time:2012.11.17
     */
     public function AdminListLog(){
         $data = $this->findPageBySql("SELECT * FROM (SELECT * FROM `ts_orglog_verified` WHERE verified = '0' ORDER BY `veritime` DESC) a  GROUP BY `uid`",10);
         return $data;
     }
     
     /*
      * 获取用户最新通过审核的资料
      * @author:jianjungki
      * @time:2012.11.17
      */
     public function GetLastLog($uid){
         $data = $this->where("uid = ".$uid)->order("veritime")->findPage(10);
         return $data;
     }
     
     /*
      * 认证成功并通知用户
      * @author:jianjungki
      * @time:2012.11.17
      */
      public function VerifyPassed($str){
          $res = $this->where("logid in (".$str.")")->setField(array('verified','passedtime'),array("1",time()));
          $uids =  is_array($str) ? $str:explode(',', $str);
          foreach ($uids as $value) {
             $this->SetUserInfo($value);
          }
          return $res;
      }
      
      
      /*
      * 删除认证
      * @author:jianjungki
      * @time:2012.11.17
      */
      public function DelLog($str){
          if($this->where("logid in (".$str.")")->delete()){
          	  M('user_verified')->where("uid = ".$data['uid'])->setField("verified","1");
              return 1;
          }else{
              return 0;
          }
          
      }
      
      /*
      * 审核通过并提交修改内容
      * @author:jianjungki
      * @time:2012.11.17
      */
     public function SetUserInfo($logid){
         $userinfo = $this->where("logid = ".$logid)->find();
         $userset = D('User')->where("uid = ".$userinfo['uid'])->find();
         $orgid = $userset['deputyoriid'];
         if($userset){
             $userset['uname'] = $userinfo['unitShortName'];
             $userset['province'] = $userinfo['province'];
             $userset['city'] = $userinfo['city'];
             $userset['location'] = getLocation($userinfo['province'],$userinfo['city']);
             $res1 = D('User')->data($userset)->save();
         }
        
         if($orginfo = D('OrgInfo')->where("orgid = ".$orgid)->find()){
             $orginfo['unitFullName'] = $userinfo['unitFullName'];
             $orginfo['unitShortName'] = $userinfo['unitShortName'];
             $orginfo['location'] = getLocation($userinfo['province'],$userinfo['city']);
             $orginfo['website'] = $userinfo['website'];
             $orginfo['category'] = $userinfo['category'];
             $orginfo['content'] = $userinfo['info'];
             $orginfo['username'] = $userinfo['realname'];
             $orginfo['telephone'] = $userinfo['phone'];
             if(!empty($userinfo['attachment'])){
                  $orginfo['fileId'] = $userinfo['attachment'];
             }
            
             $res2 = D('OrgInfo')->data($orginfo)->save();
         }
        
         //认证信息合并内容
            if(!empty($userinfo['attachment'])){
                $data['attachment']=$userinfo['attachment'];
            }
            $data['realname'] = preg_match('/^[\x{2e80}-\x{9fff}]+$|^[a-zA-Z\.·]+$/u', $userinfo['realname']) ? $userinfo['realname'] : '';
            $data['phone']    = $userinfo['phone'];
            $data['reason']   = $userinfo['reason'];
            $data['info']   = $userinfo['info'];
            $data['verify_char']   = "orgver_veritype";
            $data['veritype']   = "组织认证";
            $data['verified']   = "1";
            $data['uid']   = $userinfo['uid'];
            if(M('user_verified')->where("uid = ".$data['uid'])->find()){
                $res1 = M('user_verified')->where("uid = ".$data['uid'])->data($data)->save();
            }else{
                $res1 = M('user_verified')->data($data)->add();
            }
			
			//更新组织信息到(圈子中的)组织
			$res3 = D('Group','group')->where("deputyorgid = " . $orgid . " AND uid =" . $data['uid'])->setField("name",$userinfo['unitFullName']);
        
         if($res1&&$res2&&$res3){
             return 1;
         }else{
             return 0;
         }
         
     }
}