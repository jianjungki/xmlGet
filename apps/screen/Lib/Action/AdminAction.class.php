<?php
/**
 * AdminAction
 * 心情管理
 * @uses Action
 * @package Admin
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
import('admin.Action.AdministratorAction');

class AdminAction extends AdministratorAction
{
	private static $buffer = array(ScreenModel::TYPE_OLD=>'<em class="ico_dpm_end">已结束</em>',
		               ScreenModel::TYPE_RUNING=>'<em class="ico_dpm_ing">正在进行</em>',
		               ScreenModel::TYPE_WILL_START=>'<em class="ico_dpm_begin">即将开始</em>');
	private static $color = array(ScreenModel::TYPE_OLD=>'',
		               ScreenModel::TYPE_RUNING=>'time_ing',
		               ScreenModel::TYPE_WILL_START=>'time_begin');
	private static $tabArray = array(ScreenModel::TYPE_ALL=>'全部案例',
	                   ScreenModel::TYPE_RUNING=>'正在进行',
	                   ScreenModel::TYPE_WILL_START=>'尚未开始',
	                   ScreenModel::TYPE_OLD=>'历史回顾',
        	           );

    /**
     * basic
     * 基础设置管理
     * @access public
     * @return void
     */
    public function index ()
    {
        $model = D('Screen');
        $list  =  $model->getScreenList(intval($_GET['type']));
        if ( !empty($_POST) ) {
            $_SESSION['screen_admin_search'] = serialize($_POST);
        }else if ( isset($_GET[C('VAR_PAGE')]) ) {
            $_POST = unserialize($_SESSION['screen_admin_search']);
        }else {
            unset($_SESSION['screen_admin_search']);
        }

        if(!empty($_POST)){
            $list = D('Screen')->searchScreenList(text($_POST['title']));
        }else{
            $list = D('Screen')->getScreenList();

        }
        $list['data'] = $this->_getDetailOtherDataForMore($list['data'],$_GET['type']);

        $this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');
        $this->assign($list);
        $this->assign('title',text($_POST['title']));
        $this->display();
    }

    public function doDeleteScreen(){
            $result       = D('Screen')->del(safe($_REQUEST['id']));
            if( false !== $result){
                if ( !strpos($_REQUEST['id'],",") ){
                    echo 2;            //说明只是删除一个
                }else{
                    echo 1;            //删除多个
                }
            }else{
                echo -1;               //删除失败
            }
    }
    private function _getDetailOtherDataForMore($data,$type=ScreenModel::TYPE_ALL){
        $logo = array();
        $temp = array();
        foreach($data as $key=>$v){
            if($type == ScreenModel::TYPE_ALL){
                $data[$key]['class']['buffer'] = self::$buffer[$v['type']];
                $data[$key]['class']['color']  = self::$color[$v['type']];
            }
            $data[$key]['time_start']      = friendlyDate($v['time_start'],'ymd');
            $data[$key]['time_end']        = friendlyDate($v['time_end'],'ymd');
            if(!empty($v['logo'])){
                $logo[$v['screen_id']] = $v['logo'];
                $temp[$v['logo']] = $key;
            }else{
                $data[$key]['logo'] = __THEME__.'/images/ts.png';
            }
        }
        $logo = model('Attach')->getAllAttachById($logo);
        foreach($temp as $key=>$value){
            $data[$value]['logo'] = $logo[$key];
        }
        return $data;
    }

}
