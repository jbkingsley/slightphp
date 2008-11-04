<?php           
/**
 * ���ݿ���������࣬�̳�PDO��������������ó�GLOBAL����
 *
 */
class data{
	var $page=1;
	var $pageSize=0;
	var $limit=0;
	var $totalPage=0;
	var $totalSize=0;
	var $totalSecond=0;
	var $keyWords=array();
	var $items;//array(array(),array());

}
class SlightPHPDB{
	var $mysql;

	var $host;
	var $user;
	var $pass;
	var $database;
	var $orderby;
	var $groupby;
	var $sql;
	var $count=true;
	var $limit=0;
	var $page=1;
	var $countsql;
	var $error=array('code'=>0,'msg'=>"");

	/**
	 * ��ʼ������
	 * @param string uri  Ĭ��Ϊdefine�� pdo_uri
	 * @param string user Ĭ��Ϊdefine�� pdo_user
	 * @param string pass Ĭ��Ϊdefine�� pdo_pass
	 */
	function SlightPHPDB($ModuleName){//$host=null,$user=null,$pass=null,$database=null){
		global $APP_CONFIG;
		if(!empty($APP_CONFIG[$ModuleName]) and !empty($APP_CONFIG[$ModuleName]['db'])){
			//set_magic_quotes_runtime(0);
			$this->host= $APP_CONFIG[$ModuleName]['db']['host'];
			$this->user= $APP_CONFIG[$ModuleName]['db']['user'];
			$this->pass= $APP_CONFIG[$ModuleName]['db']['pass'];
			$this->database= $APP_CONFIG[$ModuleName]['db']['database'];
			$this->key = "mysql:".$this->host.":".$this->user.":".$this->pass;
			$GLOBALS[$this->key]="";
			$this->__connect();
		}

	}
	/**
	 * ����ҳ��
	 * @param int page ҳ�룬Ĭ��Ϊ1
	 */
	function setCount($count)
	{
		if($count==true){
			$this->count=true;
		}else{
			$this->count=false;
		}
	}
	/**
	 * ����ҳ��
	 * @param int page ҳ�룬Ĭ��Ϊ1
	 */
	function setPage($page)
	{
		if(!is_numeric($page) || $page<1){$page=1;}
		$this->page=$page;
	}
	/**
	 * ���ü�������
	 * @param int limit Ĭ��Ϊ0�����ǲ�����
	 */
	function setLimit($limit)
	{
		if(!is_numeric($limit) || $limit<0){$limit=0;}
		$this->limit=$limit;
	}
	/**
	 * ����Groupby
	 * @param string groupby Ĭ��Ϊ�գ�
	 * ����:setGroupby("groupby MusicID");
	 *      setGroupby("groupby MusicID,MusicName");
	 */
	function setGroupby($groupby)
	{
		$this->groupby=$groupby;
	}
	/**
	 * ����Orderby
	 * @param string orderby Ĭ��Ϊ�գ�
	 * ����:setOrderby("order by MusicID Desc");
	 */
	function setOrderby($orderby)
	{
		$this->orderby=$orderby;
	}

	function select($table,$condition="",$item="*",$groupby="",$orderby="",$leftjoin=""){
		if($item==""){$item="*";}
		if(is_array($table)){
			for($i=0;$i<count($table);$i++)
			{
				$tmp[]=trim($table[$i]);
			}
			$table=@implode(" , ",$tmp);
		}else{
			$table=trim($table);
		}

		if(is_array($item))
		$item =@implode(" , ",$item);

		$condiStr = $this->__quoteCondition($condition);
		if($condiStr!=""){
			$condiStr=" WHERE ".$condiStr;
		}
		$join="";
		if(is_array($leftjoin)){
			foreach ($leftjoin as $key=>$value){
				$join.=" LEFT JOIN $key ON $value ";
			}
		}

		$this->groupby  =$groupby!=""?$groupby:$this->groupby;
		$this->orderby  =$orderby!=""?$orderby:$this->orderby;
		$orderby_sql="";
		$orderby_sql_tmp = array();
		if(is_array($orderby)){
			foreach($orderby as $key=>$value){
				if(!is_numeric($key)){
					$orderby_sql_tmp[]=$key." ".$value;
				}
			}
		}else{
			$orderby_sql=$this->orderby;
		}
		if(count($orderby_sql_tmp)>0){
			$orderby_sql=" ORDER BY ".implode(",",$orderby_sql_tmp);
		}
		$limit="";
		if($this->limit!=0){
			$limit    =($this->page-1)*$this->limit;
			$limit ="LIMIT $limit,$this->limit";
		}
		$this->sql="SELECT $item FROM $table $join $condiStr $groupby $orderby_sql $limit";
		$this->countsql="SELECT count(1) totalSize FROM $table $condiStr $groupby";
		return $this->query($this->sql,$this->countsql);
	}
	function selectOne($table,$condition="",$item="*",$groupby="",$orderby="",$leftjoin="")
	{
		$this->setLimit(1);
		$this->setCount(false);
		$data=$this->select($table,$condition,$item,$groupby,$orderby,$leftjoin);
		if(isset($data->items[0]))
		return $data->items[0];
		else return;

	}

	/**
	 * ���º�����ֻ�ܶ�һ�������
	 * @param array table Ҫ���µı�
	 * @param array $item ���¶���
	 * @param string,array $condition ��������
	 * @param int $limit �������� 0��ʾû������
	 * @package int �����޸ĵ�����
	 * update("table",array('name'=>'myName','pass'=>'myPass'),array('id'=>1));
	 * update("table",array('name'=>'myName','pass'=>'myPass'),array("pass=$myPass"));
	 */
	function update($table,$condition="",$item=""){
		$value = $this->__quoteCondition($item,",");
		$condiStr = $this->__quoteCondition($condition);
		if($condiStr!=""){
			$condiStr=" WHERE ".$condiStr;
		}
		$this->sql="UPDATE $table SET $value $condiStr";
		$this->__execute($this->sql);
		return $this->rowCount();
	}
	/**
	 * ɾ��������ֻ�ܶ�һ����ɾ��
	 * @param array table Ҫɾ���ı�
	 * @param string,array $condition ��������
	 * @param int $limit �������� 0��ʾû������
	 * @return int ����ɾ������
	 * delete("table",array('name'=>'myName','pass'=>'myPass'),array('id'=>1));
	 * delete("table",array('name'=>'myName','pass'=>'myPass'),array("pass=$myPass"));
	 */
	function delete($table,$condition=""){
		$condiStr = $this->__quoteCondition($condition);
		if($condiStr!=""){
			$condiStr=" WHERE ".$condiStr;
		}
		$this->sql="DELETE FROM  $table $condiStr";
		$this->__execute($this->sql);
		return $this->rowCount();
	}
	/**
	 * ���뺯����ֻ�ܶ�һ�������
	 * @param $table ����
	 * @param array $item �����������array(0,4)����array("ID"=>3,"PlayID"=>4)
	 * @param array $update ,ֵ�� array("key"=>value,"key2"=>value2")��ʽ���� array("a=>b","c=>x")��������array("b","c") ִ��������SQL:
		 insert into zone_user_online values(2,'','','','',now(),now()) on duplicate key update onlineactivetime=CURRENT_TIMESTAMP;
	 * @return int InsertID ����InsertID
	 */
	function insert($table,$item="",$isreplace=false,$isdelayed=false,$update=array())
	{
		if($isreplace==true){
			$command="REPLACE";
		}else{
			$command="INSERT";
		}
		if($isdelayed==true){
			$command.=" DELAYED ";
		}

		$f = $this->__quoteItem($item);

		$this->sql="$command INTO $table ".$f['field']." VALUES(".$f['value'].")";
		$v = $this->__quoteCondition($update);
		if(!empty($v)){
			$this->sql.="ON DUPLICATE KEY UPDATE ".implode(",",$v);
		}
		$r=$this->__execute($this->sql);
		if($this->lastInsertId ()>0){
			return $this->lastInsertId ();
		}else{
			return $r;
		}
	}
	
	function query($sql,$countsql="")
	{
		$data = new data;
		$data->limit = $this->limit;
		$start = microtime(true);
		$result = $this->__execute($sql);
		$end = microtime(true);
		$data->totalSecond = $end-$start;
		if($result){
			while($row=mysql_fetch_array($result,MYSQL_ASSOC)){
				$tmp = array();
				foreach($row as $key=>$value){
					$tmp[$key]=stripslashes($value);
				}
				$data->items[]=$tmp;
				$data->pageSize++;
			}
		}
		if($this->limit !=0 and $this->count==true and $countsql!=""){
			$result = $this->__execute($countsql);
			if($result){
				$row = mysql_fetch_array($result,MYSQL_NUM );
				$data->totalSize = $row[0];
			}
			$data->totalPage = ceil($data->totalSize/$data->limit);
		}
		return $data;

	}
	function lastInsertId(){
		return mysql_insert_id($GLOBALS[$this->key]);
	}
	function rowCount(){
		return mysql_affected_rows($GLOBALS[$this->key]);
	}
	/**
	 * ��������
	 */

	function __connect($forceReconnect=false){
		if(empty($GLOBALS[$this->key]) || $forceReconnect){
			if(!empty($GLOBALS[$this->key])){
				mysql_close($GLOBALS[$this->key]);
				unset($GLOBALS[$this->key]);
			}
			$GLOBALS[$this->key] = mysql_connect($this->host,$this->user,$this->pass,false,MYSQL_CLIENT_COMPRESS);
		}
		if(!$GLOBALS[$this->key]){
			die("���緱æ�����Ժ�����");
		}
		if($this->database!=""){
			mysql_select_db($this->database,$GLOBALS[$this->key]);
			if(defined("mysql_charset")){
				$charset = "SET NAMES '".mysql_charset."'";
				mysql_query($charset);
			}
		}
	}
	function __execute($sql){
		if(empty($GLOBALS[$this->key]) || !mysql_ping($GLOBALS[$this->key])){
			$this->__connect($forceReconnect=true);
		}
		if(defined("DEBUG")){
			echo "SQL:$sql\n";
		}
		$result = mysql_query($sql,$GLOBALS[$this->key]);
		if(!$result){
			$this->error['code']=mysql_errno();
			$this->error['msg']=mysql_error();
			
			return false;
		}else{
			return $result;
		}
	}
	function __quoteItem($item){
		$result = array("field"=>"","value"=>"");
		if(is_array($item)){
			$it=array();
			$value=array();
			foreach($item as $k=>$v){
				if(!is_numeric($k))
				{
					$it[]="`".$k."`";
					$value[]="'".mysql_real_escape_string($v,$GLOBALS[$this->key])."'";
				}else{
					$tmp=explode("=",$v);
					if(isset($tmp[0]) and isset($tmp[1])){
						$it[]="`".$tmp[0]."`";
						$value[]=$tmp[1];
					}
					unset($tmp);
				}
				
			}
			if(count($value)>0){
				$result['value'] = implode(",",$value);
			}
			if(count($it)){
				$result['field'] = "(".implode(",",$it).")";
			}
			return $result;
		}
	}
	function __quoteCondition($condition,$split="AND"){
		$condiStr = "";
		if(is_array($condition)){
			$v1=array();
			foreach($condition as $k=>$v)
			{
				if(!is_numeric($k))
				{
					$v1[]="`".$k."`"." = '".mysql_real_escape_string($v,$GLOBALS[$this->key])."'";
				}else{
					$v1[]=($v);
				}
			}
			if(count($v1)>0)
			{
				$condiStr=implode(" ".$split." ",$v1);

			}
		}else{
			$condiStr=$condition;
		}
		return $condiStr;
	}
}
?>
