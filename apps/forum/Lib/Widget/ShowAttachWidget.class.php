<?php
/*
 * ShowAttachWidget
 * 显示附件
 * input array $data = array(
 *							'ids'		=>	array(1,2,3),
 *							'popedem'	=>	array(
 *									allow_read_attach	=>	1,	//是否可以阅读附件
 *									allow_read_image	=>	1,	//是否直接显示图片
 *									allow_check_attach	=>	1	//是否可以审核附件
								)
 *							);
 */
class ShowAttachWidget extends Widget{

	public function render($data){
		//获取附件
		$attaches =	X('Xattach')->getAttach($data['ids']);
		$var['data'] =	$attaches;
//		$temp = D('Space','home')->getFaceShow($_SESSION['mid']);
		if(!$temp) $temp = true;
		$var['needShowImage'] = $temp?true:false;
		
//		$data['popedom']['allow_read_attach']	=	1;	
//		$data['popedom']['allow_read_image']	=	1;
//		$data['popedom']['allow_check_attach']	=	1;
		
		$var['allow_read_attach']	 = intval($data['popedom']['allow_read_attach']);	
		$var['allow_read_image'] = intval($data['popedom']['allow_read_image']);
		$var['allow_check_attach'] =	intval($data['popedom']['allow_check_attach']);
		$var['pageAttach'] = $data['pageAttach'];
		//附件审核.判断当前用户是否有审核权限.
        $content = $this->renderFile(dirname(__FILE__)."/ShowAttach.html",$var);
        return $content;
    }
}
?>