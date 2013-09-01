<?php
 /*
 @param xml文件地址
 @param 生成的文件的存放地址
 @return 返回结果:1为成功，0为出现异常中断
 */
 
 
include_once('tbszip.php'); // load the TbsZip library
 $package = "newfile";
 $package_file =  $package."/package.zip";
 $execute_file =  $package."/package.xml";
if(file_exists($execute_file)){
    $content = file_get_contents($execute_file);
}else{
	$zip = new clsTbsZip(); // create a new instance of the TbsZip class
    $zip->Open($package_file); // open an existing archive for reading and/or modifying
	$content = $zip->FileRead("package.xml");
}

$get = new XMLCrash($content);
//创建目录函数
function createFolder($path){
	if (!file_exists($path)){
		createFolder(dirname($path));    //return to lastpath
		mkdir($path, 0777); //if windows, seems set 0777 is useless
	}
}

function xCopy($source,$destination,$child){
		if(!is_dir($source)){
			echo ("错误: $source 的路径出错\n");
			return 0;
		}
		if(!is_dir($destination)){
			mkdir($destination,0777);
		}
		$handle = dir($source);
		while($entry=$handle->read()){
			if(($entry!=".")&&($entry!="..")){
				if(is_dir($source."/".$entry)){
					if($child)
						xCopy($source.'/'.$entry,$destination.'/'.$entry,$child);
				}else{
					copy($source.'/'.$entry,$destination.'/'.$entry);
				}
			}
		}
		return 1;
}

function copyAction($componentId){
	$configfile = array(
			"v3",
			"tenc",
			"components/".$componentId
		);
	$destin = 'gift';
	foreach($configfile as $value){
		if (!xCopy($value, $destin,true)) {
			echo "复制 $value 失败...\n";
		}
	}
}

function sqlCon(){
	
}


class XMLCrash{
	private $source_file;
	private $arg = array();//单属性解析（标签）
	private $set = array();//单属性解析（值）
	private $forit = array();//复合标签解析（标签）
	private $index = array();//复合标签解析（值）
	private $tmpfile;//临时变量
	private $fileset;//解析文件入口目录
	private $fileinfo;//缓存变量
	private $Tplconfig;//模板文件目录设置
	private $componentid;//组件id
	//构造解析信息
	function __construct($content){
		
		$xml = simplexml_load_string($content,'SimpleXMLElement',LIBXML_NOCDATA);
		
		if($xml){
			

			foreach ($xml->components->component as $key => $value) {
				
				$ruleInstances = $value->ruleInstances;//组件属性
				//$filenow = "V3/Tpl/default/";
				$this->fileset = "newfile";
				$filenow = $this->fileset;
				$fileout = "set";//输出文件的目录
				$this->Tplconfig = "Tpl/default/";//模板文件所在目录
				$property = $value->propertys;//组件属性
				$control = $value->controls;//组件，需要遍历
				$this->componentid = $value->attributes()->id;
				
				$act = $value->componentVariants->xPath("componentVariant[@name='act']");//动作
				$app = $value->componentVariants->xPath("componentVariant[@name='app']");//应用
				$mod = $value->componentVariants->xPath("componentVariant[@name='mod']");//模型
				
				$fileout = $fileout."/".$app[0]->attributes()->initValue."/Tpl/default/".$mod[0]->attributes()->initValue."/".$act[0]->attributes()->initValue;
				
				createFolder(dirname($fileout));
				
				if(file_exists($this->fileset."/components/".$value->attributes()->id."/".$this->Tplconfig."mod/act.html")){
					$filenow = $this->fileset.$value->attributes()->id."/";
				}else if(file_exists($this->fileset."/components/thinkv3/".$this->Tplconfig."mod/act.html")){
					$filenow = $this->fileset."/components/thinkv3/";
				}else if(file_exists($this->fileset."/components/tnec/".$this->Tplconfig."mod/act.html")){
					$filenow = $this->fileset."/components/tnec/";
				}
				
				$this->filename = $filenow.$this->Tplconfig."mod/act.html";
				if(!file_exists($this->filename)){
					echo $this->filename."不存在";
					return 0;
				}
				$this->tmpfile =  file_get_contents($this->filename);
				$this->fileinfo = $this->parse($this->arg,$this->set,$this->tmpfile);//获取初始文件数据
				/*************解析开始***************************/
				$this->xml2control($control,"");
				/*************解析结束**************************/
				$fp=fopen($fileout.".html","a+");//fopen(),读操作
				if(!(preg_match("|.?haveedit.*|",fgets($fp,20)))){
					fclose($fp);
					unlink($fileout.".html");
					$fp=fopen($fileout.".html","w");//fopen(),写操作
					fputs($fp,$this->fileinfo);
				}

				fclose($fp);
				echo $filenow."生成成功<br>";
				
			}
			return 1;
		}
	}

	//json解析用，暂时不考虑
	function arr_foreach($name="",$arr){//json解析函数
		if(!is_array ($arr)){//递归出口
			return false;
		}
		foreach ($arr as $key => $val ) { //递归解析数组

			if (is_array ($val)&&!is_numeric($key)) {
				if(empty($name)){
					$name = $key; 
				}else{
					$name = $name . "." .$key;
				}
				$this->arr_foreach ($name,$val); 
			}else if(!is_array ($val)){//解析不是数组的内容，元素
				$this->forit[] = "propertys.".$name.".".$key;
				$this->index[] = $val;
			}else if(is_array ($val)&&is_numeric($key)){//解析既是数组又是数字的内容
				$this->forit[] = "propertys.".$name;
				$this->index[] = $arr;
				break;//如果是数字下标，则直接飘过，只需要上一个名字
			}
			
			//echo $val."<br>"; 
		}

		if(!empty($this->index)){ //索引序列
			foreach ($this->index as $key => $value) {
				$this->set[] = $value;	
			}
			foreach ($this->forit as $key => $value) {
				$this->arg[] = $value;
			}

		}
		unset($this->forit);
		unset($this->index);

		return;
		
	}


	function xml2control($xml,$filenow){//递归解析xml数据
		
		if($xml->attributes()){//判断解析目录，并且把标签属性加入待解析内容
			//查找包含的控件
			$mainpath = $this->fileset."/controls/".$filenow.$xml->attributes()->type."/".$xml->attributes()->id."/".$this->Tplconfig."mod/act.html";//自定义控件内容
			$componentpath = $this->fileset."/controls/".$filenow.$xml->attributes()->type."/component".$this->componentid."/".$this->Tplconfig."mod/act.html";//自定义组件控件内容
			$tencpath = $this->fileset."/controls/".$filenow.$xml->attributes()->type."/tenc/"."/".$this->Tplconfig."mod/act.html";//项目控件模板内容
			$thinkv3path = $this->fileset."/controls/".$filenow.$xml->attributes()->type."/thinkv3/"."/".$this->Tplconfig."mod/act.html";//v3控件模板内容
			$flag = 0;
			if(!file_exists($mainpath)&&!file_exists($componentpath)&&!file_exists($tencpath)&&!file_exists($thinkv3path)){//都不存在的时候，表明该标签不能解析
				$flag = 1;
			}else if(file_exists($mainpath)){
				$this->filename = $mainpath;
			}else if(file_exists($componentpath)){
				$this->filename = $componentpath;
			}else if(file_exists($tencpath)){
				$this->filename = $tencpath;
			}else if(file_exists($thinkv3path)){
				$this->filename = $thinkv3path;
			}//优先级分别判断
			
			//查找原来控件
			$mainpath = $this->fileset."/controls/".$xml->attributes()->type."/".$xml->attributes()->id."/".$this->Tplconfig."mod/act.html";//自定义控件内容
			$componentpath = $this->fileset."/controls/".$xml->attributes()->type."/component".$this->componentid."/".$this->Tplconfig."mod/act.html";//自定义组件控件内容
			$tencpath = $this->fileset."/controls/".$xml->attributes()->type."/tenc/"."/".$this->Tplconfig."mod/act.html";//项目控件模板内容
			$thinkv3path = $this->fileset."/controls/".$xml->attributes()->type."/thinkv3/"."/".$this->Tplconfig."mod/act.html";//v3控件模板内容
			if(!file_exists($mainpath)&&!file_exists($componentpath)&&!file_exists($tencpath)&&!file_exists($thinkv3path)&&$flag){//都不存在的时候，表明该标签不能解析
				return;
			}else if(file_exists($mainpath)){
				$this->filename = $mainpath;
			}else if(file_exists($componentpath)){
				$this->filename = $componentpath;
			}else if(file_exists($tencpath)){
				$this->filename = $tencpath;
			}else if(file_exists($thinkv3path)){
				$this->filename = $thinkv3path;
			}//优先级分别判断
			
			echo $this->filename."<br><br>";
			$filenow = $xml->attributes()->type."/";
			foreach ($xml->attributes() as $key => $value) {
				$this->arg[] =  $key;
				$this->set[] =  $value;
			}
		}
		
		if($xml->propertys){//配置内容添加入解析队列
			foreach ($xml->propertys->property as $key=>$value) {
				//非json转化的普通数据
					if($value->attributes()->name=="Visible"&&$value=="False"){
						 $this->fileinfo = $this->file_combine($xml->attributes(),"",$this->fileinfo);
						 unset($this->arg);unset($this->set);return;
					}
					$this->arg[] =  "propertys.".$value->attributes()->name;
					$this->set[] =  $value;
			}
			
			$this->tmpfile = file_get_contents($this->filename);
			$content = $this->parse($this->arg,$this->set,$this->tmpfile);
			echo $xml->attributes()->id."<br><br>";
			$this->fileinfo = $this->file_combine($xml->attributes(),$content,$this->fileinfo);
		}
		
		if(count($xml->controls)||count($xml->control)){
			if(count($xml->controls)){
				$this->xml2control($xml->controls,$filenow);
			}else{
				for ($i=count($xml->control)-1; $i >= 0 ; $i--) {
					$this->xml2control($xml->control[$i],$filenow);
				}
			}
		}
		
	}
	//合并文件，按照解析规则
	function file_combine($xmlib,$vals,$content){
		if(empty($content)){
			return $vals;
		}else{
			$info = preg_replace("|【.+V3UNDER.+".$xmlib->type.".+".$xmlib->id."】.?start[\s\S][^\【]*【.+V3UNDER.+".$xmlib->type.".+".$xmlib->id."】.?end|",$vals,$content);
			return $info;
		}
	}

	function parse($tags,$vals,$file_content){ //标签内容映射
			  if(!is_array($tags)){ 
				 $value = preg_replace("|【".$tags."】|",$vals,$file_content);  
			  }else{ 
				 $an = count($tags); 
				 for($i=0;$i<$an;$i++){ 
					$tags[$i] = "|【".$tags[$i]."】|"; 
				 } 
				$value = preg_replace($tags,$vals,$file_content);  

			 }
			  unset($this->arg);unset($this->set); 
			  return $value;
	 }
}


?>