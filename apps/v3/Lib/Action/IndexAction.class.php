<?php
    /**
     * Action
     * 【Name】控制层
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2012 tonone 同望
     * @author tonone   同望
     * @license PHP Version 5.2 {@link www.toone.cn}
     */
class IndexAction extends Action{

   【tables】start
	   private $【tables.name】;        //【tables.chineseName】模型
	【tables】end  
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){	
        //整个应用的赋值
		global $ts;

	  【tables】start
	  	$this->user_gift = D('【tables.Name】')
	【tables】end  


	}
}
