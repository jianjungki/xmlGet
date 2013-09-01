<?php
class DatadictModel extends Model {
    public function getAllData($data_id = -1){
        $data_id != -1 && $map['pid'] = $data_id;
        $list = $this->where($map)->order('`id` ASC')->findAll();
        return $list;
    }
    
    public function getCommonData($type){
           $pid =  $this->getDataSelect($type);
           $info = $this->getAllData($pid);
           return $info;
    }
    
    public function getOneData($dataid){
       return $this->where("id = ".$dataid)->find();
    }
    
    public function getDataSelect($str){
        $data_id = $this->where("index_char = '".$str."' && can_del = 1")->getField("id");
        return $data_id;
    }
    
    public function getDataAttach($str){
        //if(is_numeric($str)){
         //   $urlid = $this->where("index_id = ".$str)->getField("icon_url");
        //}else{
             $urlid = $this->where("index_char = '".$str."'")->getField("icon_url");
       // }
        return $urlid;
    }
    
    public function getDataName($str){
        if(is_numeric($str)){
             $name = $this->where("index_id = ".$str)->getField("name");
        }else{
             $name = $this->where("index_char = '".$str."'")->getField("name");
        }
        return $urlid;
    }
}