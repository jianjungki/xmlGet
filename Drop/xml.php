<?php
/*
function caculate($first,$opera,$next){
	if($opera == '+'){
		return $first+$next;
	}else if($opera == '-'){
		return $first-$next;
	}else if($opera == '*'){
		return $first*$next;
	}else if($opera == '/'){
		return $first/$next;
	}
}
$val = "【propertys.Height/100】";
$val = preg_replace("/(【)(([a-zA-Z.]*)(\/)([0-9]*))(】)/ei","caculate(20,'\\4','\\5')",$val);
*/
$get = new XMLCrash("package.xml");


class XMLCrash{
private $source_file;
private $arg = array();//单属性解析
private $set = array();
private $forit = array();//复合标签解析
private $index = array();
private $fileinfo;
private $tmpfile;

function __construct($filename){
	if(file_exists($filename)){
		$xml = simplexml_load_file($filename,'SimpleXMLElement',LIBXML_NOCDATA);
	}
	if($xml){
	    $tables = $xml->components->component->componentTables;
	    $ruleInstances = $xml->components->component->ruleInstances;//组件属性
	    $property = $xml->components->component->propertys;//组件属性
	    $control = $xml->components->component->controls;//礼物组件，需要遍历
	    $options = $xml->systemOptions;
	    $field = $xml->tables->table->fields;
	    $getid = $xml->components->component->attributes()->id;
		if(file_exists("V3/Tpl/default/".$getid."/Index/index.html")){//确定解析目录
				$this->fileinfo = file_get_contents("V3/Tpl/default/".$getid."/Index/index.html");
		}else if(file_exists("V3/Tpl/default/tenc/Index/index.html")){
				$this->fileinfo = file_get_contents("V3/Tpl/default/tenc/Index/index.html");
		}else{
			    $this->fileinfo = file_get_contents("V3/Tpl/default/v3/Index/index.html");
		}
		$this->xml2control($control);
	    //$name = parse("Name","56",$source_file);
		//echo  $name;
	}
}


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


function xml2control($xml){//递归解析xml数据
	
	if($xml->attributes()){//判断解析目录，并且把标签属性加入待解析内容
		$mainpath = "V3/Tpl/default/controls/".$xml->attributes()->type."/".$xml->attributes()->id."/index.html";
		$nextpath = "V3/Tpl/default/controls/".$xml->attributes()->type."/index.html";
		if(!file_exists($mainpath)&&!file_exists($nextpath)){
			$filename = $nextpath;
		}else if(!file_exists($mainpath)&&file_exists($nextpath)){
			$this->filename = $nextpath;
		}else if(file_exists($mainpath)){
			$this->filename = $mainpath;
		}
		foreach ($xml->attributes() as $key => $value) {
			$this->arg[] =  ucfirst($key);
			$this->set[] =  $value;
			$this->arg[] =  $key;
			$this->set[] =  $value;
		}
	}
	if($xml->propertys){//配置内容添加入解析队列
		foreach ($xml->propertys->property as $key=>$value) {
			//echo $value->attributes()->name."<br>";
			//$info = str_replace("'","\"",$value);//替换单引号，因为json不允许单引号存在

			/*if($json = @json_decode($info,true)){//判断是否json
				$get = $json;
				if(is_array($get)){
					//var_dump($get);
					$this->arr_foreach($value->attributes()->name,$get);//把json数据解析
				}else{//普通数据直接获取
					$this->arg[] =  "propertys.".$value->attributes()->name;
					$this->set[] =  $info;
				}
				
			}else{
			*/
			/********************保留内容重要**********************/
			//非json转化的普通数据
				$get = $info;
				$this->arg[] =  "propertys.".$value->attributes()->name;
				$this->set[] =  $get;
			//}


		}
		if(is_array($this->arg)){
			foreach ($this->arg as $key => $value) {
				print_r($value.":".$this->set[$key]."<br>");
			}
		}
		$this->tmpfile = file_get_contents($this->filename);
		echo $this->filename."<br>";
		echo $xml->attributes()->id."<br>";
		$content = $this->parse($this->arg,$this->set,$this->tmpfile);
		echo $content."<br>";
		print("<br>*********************************************************************************************<br>");
	}
	
	//echo $xml->attributes()->id."<br>";
	if(count($xml->controls)||count($xml->control)){
		if(count($xml->controls)){
			$this->xml2control($xml->controls);
		}else{
			for ($i=0; $i < count($xml->control); $i++) {
				$this->xml2control($xml->control[$i]);
			}
			
		}

		/*if(is_array($this->arg)){
			foreach ($this->arg as $key => $value) {
				print_r($value."<br>");
			}
		}*/

	}



}


function parseto($name){
	$getinfo = (array)$name;
	$params = array_change_key_case($getinfo['@attributes']);//解析属性
	$property = $name->propertys;
	//遍历设置属性
	for ($i = 0;$i < count($property->property);$i++) {
		$args[] = $property->property[$i]->attributes()->name;//解析属性标签
		$set[] = $property->property[$i];

    }
    //遍历所有普通属性
	foreach ($params as $key => $value) {
		$args[] = $key;
		$set[] = $value;
	}
	$source_file = parse($args,$set,$source_file);
}


function file_combine($xmlinfo){
	
}

/*
function newtpl($url){ 
	global $TPL_content; 
	$TPL_content = file_get_contents($url); 
} 
function mkcircle($name, $sum, $i_top = null){ 
	global $TPL_content; 
	//注意，最多两层循环！ 
	if(strstr($TPL_content, "")) 
	{ 
		$ex = explode("", $TPL_content); 
		$ex_ex = explode("", $ex[0]); 
		for($i = 0;$i < $sum;$i ++){ 
			$to_r = preg_replace("/{".$name."(.+?)}/is", "{".$name."1_".$i."m}", $ex_ex[1]); 
			$to_r = preg_replace("/--{(.+?)}--/is", "--{1_".$i."t}--", $to_r); 
			$to_circle .= $to_r; 
		} 
		$TPL_content = $ex_ex[0].$to_circle.$ex[1]; 
	} 
} 
function addvar($toreplace, $replaced, $i = null){ 
	global $TPL_content; 
	$TPL_content = str_replace("{".$toreplace.(isset($i)?\\\'_\\\'.$i.\\\'m\\\':\\\'\\\')."}", $replaced, $TPL_content);
} 
function write(){ 
	global $TPL_content; 
	print($TPL_content); 
} */

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