<?php
/**
 * db samples code
 **/
class index_db{
	private $_dbConfig;
	private $_zone;
	function __construct($zone="index"){
		$this->_zone = $zone;
		$this->_dbConfig = SDb::getConfig($this->_zone);
		$this->_db = SDb::getDbEngine("pdo_mysql");
		$this->_db->init($this->_dbConfig);
	}
	
	/**
	 * ���ӻ��ּ�¼
	 */
	function addScore($username,$score){
		return $this->_db->insert("p_score",array("username"=>$username,"score"=>$score));
	}
}
?>