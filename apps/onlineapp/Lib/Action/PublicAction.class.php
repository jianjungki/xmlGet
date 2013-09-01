<?php
class PublicAction extends Action{
    
    public function comment() {
            $this->assign("appid",$_GET['appid']);
            //dump($_REQUEST);
            $this->display();
    }
    
    public function StarIt(){
        $appinfo = M('App')->where("app_id = ".$_REQUEST['appid'])->find();
        $star = floatval($_POST['rate']);
        if($star>4){
            $appinfo['fivestar']++;
        }else if($star>3){
            $appinfo['fourstar']++;
        }else if($star>2){
            $appinfo['threestar']++;
        }else if($star>1){
            $appinfo['twostar']++;
        }else if($star>0){
            $appinfo['onestar']++;
        }
        $all = $appinfo['fivestar']+$appinfo['fourstar']+$appinfo['threestar']+$appinfo['twostar']+$appinfo['onestar'];
        $appinfo['avgstar'] = ($appinfo['avgstar']*($all-1)+$star)/$all;
        if($appinfo['avgstar']<=1){
           $appinfo['avgstar'] = ($appinfo['fivestar']*5+$appinfo['fourstar']*4+$appinfo['threestar']*3+$appinfo['twostar']*2+$appinfo['onestar']*1)/$all;
        }
        $appinfo['avgstar'] = number_format($appinfo['avgstar'],2);
        $res = M('App')->where("app_id = ".$_REQUEST['appid'])->save($appinfo);
        echo json_encode($appinfo['avgstar']);
    }
    
    /*
     * 获取描述
     */
    public function getDesc(){
        $appid = $_POST['appid'];
        $desc = M('App')->where("app_id = ".$appid)->getField("description");
        echo $desc;
    }
    
    public function doAjaxcomment(){
        $data['commentinfo'] = $_POST['commentinfo'];
        $data['appid'] = $_POST['appid'];
        $data['uid'] = $this->mid;
        
        //$apptem = M('App_comment')->where("uid = {$this->mid} and appid = ".$_POST['appid'])->find();
        
        /*if($apptem){
            $res = D('AppComment')->where("uid = {$this->mid} and appid = ".$_POST['appid'])->data($data)->add();
            if($res){
                $return['message']  =   L('Successfully_Saved');
                $return['status']   =   1;
            }else{
                $return['message']  =   L('Failed_To_Save');
                $return['status']   =   0;
            }*/
        //}else{
            $res = D('AppComment')->data($data)->add();
            if($res){
                $return['message']  =   L('Successfully_Added');
                $return['status']   =   1;
            }else{
                $return['message']  =   L('Failed_To_Add');
                $return['status']   =   0;
            }
        //}
        exit(json_encode($return));
    }
}
