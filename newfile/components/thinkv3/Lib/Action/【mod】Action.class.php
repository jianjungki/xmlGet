<?php
    /**
     * GiftAction
     * 礼物控制层
     *
     * @uses 
     * @package 
     * @version 
     * @copyright 2009-2011 SamPeng 水上铁
     * @author SamPeng <sampeng87@gmail.com> 水上铁<wxm201411@163.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
class IndexAction extends Action{
	private $gift;             //礼品表模型
	private $gift_category;    //礼品类型表模型
	private $user_gift;        //用户送礼记录表模型
	protected $app_alias;
	
	/**
	 * 初始化函数
	 *
	 */	
	function _initialize(){
        //整个应用的赋值
        $this->gift = D('Gift');
		$this->gift_category = D('GiftCategory');
		$this->user_gift = D('UserGift');

		$this->user_gift->setGift($this->gift);
		$this->user_gift->setCategory($this->gift_category);		
		$this->gift_category->setGift($this->gift);

		global $ts;
		$this->app_alias = $ts['app']['app_alias'];

		// 获取礼品单位
		$config['creditName'] = '经验值';
		$this->assign('config', $config);
	}
	
}
