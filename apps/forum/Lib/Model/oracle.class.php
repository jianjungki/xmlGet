<?php

/**
 * 数据库操作类
 *
 */
final class oracle
{
 
 /**
  * 构造函数
  *
  */
 function __construct()
 {
  $oraDB="(DESCRIPTION=(ADDRESS_LIST=(ADDRESS=(PROTOCOL=TCP)(HOST=10.88.32.50)(PORT=1521)))(CONNECT_DATA=(SID=HW3MS4)))";
  $this->conn = oci_connect('HW3MS4_BBS', 'nonant', $oraDB,'UTF8');
  if($this->conn)
  {
  //echo "wwwww";
  }
  else
  {
   $this->conn = null;
   /* 显示错误信息，用于调试。正式上线时注释 */
   echo "conn error";
   $e = oci_error();
     echo $e['message'];
     /*  */
  }
 }
 
 /**
  * 析构函数
  *
  */
 function __destruct()
 {
  oci_close($this->conn);
  $this->conn = null;
 }
 
 /**
  * 查询函数，返回结果数据集
  *
  * @param string $sql
  * @return array
  */
 function Search($sql)
 {
  //echo $sql;
	  $sql = $this->ParseSQL($sql);
	  if(oci_execute($sql))
	  {
	   while($row = oci_fetch_array($sql,OCI_ASSOC ))
	   {
	    $result[] = $row;
	   }
	   oci_free_statement($sql);
	   if(isset($result))
	   {
	    return $result;
	   }
	   else
	   {
	    $result = array();
	    return $result;
	   }
	  }
	  else 
	  {
	   /* 显示错误信息，用于调试。正式上线时注释 */
		   $e = oci_error($sql);
		     echo $e['message'];
		     echo "<pre>";
		     echo htmlentities($e['sqltext']);
		     printf("\n%".($e['offset']+1)."s", "^");
		     echo "</pre>";
		     /* */
		     oci_free_statement($sql);
	  }
 }
 
 /**
  * 执行无返回值的SQL语句
  *
  * @param string $sql
  * @return bool
  */
 function ExecuteSQL($sql)
 {
  //echo $sql;
  $sql = $this->ParseSQL($sql);
  $result = false;
  
  if(oci_execute($sql))
  {
   $result = true;
  }
  else 
  {
   /* 显示错误信息，用于调试。正式上线时注释 */
   $e = oci_error($sql);
     echo $e['message'];
     echo "<pre>";
     echo htmlentities($e['sqltext']);
     printf("\n%".($e['offset']+1)."s", "^");
     echo "</pre>";
     /* */
  }
  oci_free_statement($sql);
  return $result;
 }
 
 /**
  * 执行存储过程，输入存储过程名和参数列表，成功返回TRUE。
  *
  * @param string $sp_name
  * @param array $paralist
  * @return bool
  */
 function ExecuteSP($sp_name, $paralist)
 {
  $para_len = count($paralist);
  $sql = "begin $sp_name(";
  for($i = 0;$i < $para_len - 1;$i++)
  {
   $sql .= "'$paralist[$i]',";
  }
  $sql .= "'".$paralist[$para_len-1]."');end;";
  $sql = $this->ParseSQL($sql);
  $result = false;
  if(oci_execute($sql))
  {
   $result = true;
  }
  else 
  {
   /* 显示错误信息，用于调试。正式上线时注释 */
   echo "exec error";
   $e = oci_error($sql);
     echo $e['message'];
     echo "<pre>";
     echo htmlentities($e['sqltext']);
     printf("\n%".($e['offset']+1)."s", "^");
     echo "</pre>";
     /* */
  }
  oci_free_statement($sql);
  return $result;
 }
 
 /**
  * 解析查询字符串
  *
  * @param string $sql
  * @return  resource
  */
 function ParseSQL($sql)
 {
  $p_sql = oci_parse($this->conn, $sql);
  /* 显示错误信息，用于调试。正式上线时注释 */
  if(!$p_sql)
  {
   echo "parse error";
   $e = oci_error($this->conn);
   echo $e['message'];
  }
  /* */
  return $p_sql;
 }
 
 /**
  * 执行数据库提交
  *
  * @return bool
  */
 function Commit()
 {
  return oci_commit($this->conn);
 }
 
 /**
  * 执行SQL语句但不提交
  *
  * @param resource  $sql
  * @return bool
  */
 function ExecuteNoCommit($sql)
 {
  return oci_execute($sql, OCI_DEFAULT);
 }
 
 
}

?>