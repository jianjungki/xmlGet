<?php
    /**
     * 【Name】
     * 【chineseName】
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2012 toone
     * @author 
     * @license PHP Version 5.2 {@link www.toone.cn}
     */
class 【Name】 extends Model{
		var $tableName = '【tables.name】';	
		/**
		 * __insertData
		 * 把数据插入数据库
		 * @param $toUser 发送对象ID $map 数据组
		 * @return $add 插入结果集;
		 */		
		private function __insertData($toUser,$map){
			foreach ($toUser as $_touid){
				//组成数据集
				$map['toUserId']     = intval($_touid);
				//将信息入库
				$res = $this->add($map);
			}
			return $res;
		}

}