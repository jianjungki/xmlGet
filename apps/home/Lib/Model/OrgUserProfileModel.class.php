<?php

class OrgUserProfileModel extends OrgUserModel {
    public function getUserInfo($space = false){
        $userInfoList                      = $this->where('uid='.$this->uid)->field('id,uid,module,data,type')->findall();
        $userInfo                          = $this->dataProcess( $userInfoList ,$space);
        $userInfo['detail']                = $this->table(C('DB_PREFIX').'user')->where("uid={$this->uid}")->find();
        //查找企业相关资料
        $temp                              = $userInfo['detail']['deputyoriid'];
        $userInfo['Orgdetail']             = $this->table(C('DB_PREFIX').'org_info')->where("orgid='{$temp}'")->find();
        //dump($userInfo['Orgdetail']);
        $userInfo['base']['completeness']  = 100;
        return $userInfo;
    }
    
    //数据处理
    private function dataProcess( $userInfoList,$space ){
        $fieldList = $this->data_field(false,$space);
        foreach ($userInfoList as $value){
            if( $value['type'] == 'info' ){
                $database[ $value['module'] ] = unserialize( $value['data'] );
            }else{
                $data[ 'profile' ]['list'][] = array_merge( array('module'=>$value['module'],'id'=>$value['id']) , unserialize($value['data']) );
            }
        }
        $data['profile']['completeness'] = round( count( array_unique( getSubByKey( $data[ 'profile' ]['list'] ,'module') ) ) / 2 , 2) *100;
        foreach ($fieldList as $key=>$value){
            foreach ( $value as $k=>$v){
                $t = $database[$key][$k];
                if( $t ) $complete++;
                $data[$key]['list'][]  = array('field' => $k,'name'  => $v,'value' => $t );

            }
            $data[$key]['completeness'] = round( $complete/count($value) , 2 ) * 100 ;
            unset($complete);
        }
    }
    
    //更新个人情况
    function upintro(){
        $fieldList = $this->data_field( 'intro' );
        foreach ($fieldList as $key=>$value){
            $data[$key] = t( msubstr( $_POST['intro'][$key],0,70,'utf-8',false ) );
        }
        $this->dosave('intro',$data);
        $data['message'] = '更新完成';
        $data['boolen']  = 1;
        return $data;
    }
    
}