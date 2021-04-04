<?php
/********************************************************************
 * Db Driver File, Autiomatic choice the connection mysql or mysqli *
 * Code-Base : Infinitewp Base Panel                                *
 * Auther : Senthil kumar V - Tech Lead, Revmakx Techonology Pvt    *
 * Copyright (c) 2012 Revmakx                                       *
 * www.revmakx.com                                                  *
 *                                                                  *
 *******************************************************************/

//Code from mysql.php - Start
class DBMysql{
	
	protected $DBLink;
	protected $DBHost;
	protected $DBUsername;
	protected $DBPassword;
	protected $DBName;
	protected $DBPort;
	
	function __construct($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort){
		$this->DBHost = $DBHost;
		$this->DBUsername = $DBUsername;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBPort = $DBPort;
	}
	
	function connect(){
		$this->DBLink = mysql_connect($this->DBHost.':'.$this->DBPort, $this->DBUsername, $this->DBPassword);
		if (!$this->DBLink) {
			return 'Mysql connect error: (' . mysql_error().') '.$this->error();
		}
		if (!mysql_select_db($this->DBName, $this->DBLink)){
			return 'Mysql connect error: (' . $this->errorNo().') '.$this->error();
		} else {
			return true;
		}
	}
	
	function query($SQL){
		
		$result = mysql_query($SQL, $this->DBLink);
		
		if(empty($result)){			
			$errno = $this->errorNo();
			if ($errno == 2013 || $errno == 2006){
				$this->connect();
				return mysql_query($SQL, $this->DBLink);
			}
		}
		
		return $result;
	}
	
	function insertID(){
		return mysql_insert_id($this->DBLink);
	}
	
	function affectedRows(){
		return mysql_affected_rows($this->DBLink);
	}	
	
	function realEscapeString($val){
		return mysql_real_escape_string($val, $this->DBLink);
	}
	
	function ping(){
		return mysql_ping($this->DBLink);	
	}
	
	function errorNo(){
		return mysql_errno($this->DBLink);
	}
	
	function error(){
		return mysql_error($this->DBLink);
	}
}

class DBMysqlResult{
	
	private $DBResult;
	
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return mysql_num_rows($this->DBResult);
	}
	function nextRow()
	{
		return mysql_fetch_assoc($this->DBResult);
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		return mysql_free_result($this->DBResult);
	}
}
//Code from mysql.php - End

//Code from mysqli.php - Start
class DBMysqli{
	
	protected $DBLink;
	protected $DBHost;
	protected $DBUsername;
	protected $DBPassword;
	protected $DBName;
	protected $DBPort;
	protected $DBSocket;
	
	
	function __construct($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort, $DBSocket){
		$this->DBHost = $DBHost;
		$this->DBUsername = $DBUsername;
		$this->DBPassword = $DBPassword;
		$this->DBName = $DBName;
		$this->DBPort = $DBPort;
		$this->DBSocket = $DBSocket;
	}
	
	function connect(){
		$this->DBLink = new mysqli($this->DBHost, $this->DBUsername, $this->DBPassword, $this->DBName, $this->DBPort, $this->DBSocket);
		if ($this->DBLink->connect_errno) {
			return 'Mysql connect error: (' . $this->DBLink->connect_errno.') '.$this->DBLink->connect_error;
		} else {
			return true;
		}
	}
	
	function query($SQL){
		if(strpos($this->DBLink->character_set_name(),"utf8") === false){
			$this->DBLink->set_charset("utf8");
		}
		$result = $this->DBLink->query($SQL);
		
		if(empty($result)){			
			$errno = $this->errorNo();
			if ($errno == 2013 || $errno == 2006){
				$this->connect();
				return $this->DBLink->query($SQL);
			}
		}
		
		return $result;
	}
	
	function insertID(){
		return $this->DBLink->insert_id;
	}
	
	function affectedRows(){
		return $this->DBLink->affected_rows;
	}	
	
	function realEscapeString($val){
		return $this->DBLink->real_escape_string($val);
	}
	
	function ping(){
		return $this->DBLink->ping();	
	}
	
	function errorNo(){
		return $this->DBLink->errno;
	}
	
	function error(){
		return $this->DBLink->error;
	}
}

class DBMysqliResult{
	
	private $DBResult;
	
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return $this->DBResult->num_rows;
	}
	function nextRow()
	{
		return $this->DBResult->fetch_assoc();
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		$this->DBResult->free();
	}
}
//Code from mysqli.php - End

//Code from db.php - Start
class DB{
	
	private static $queryString;
	private static $printQuery;
	private static $printAllQuery;
	private static $DBDriver;
	public static $DBResultClass;
	//private static $showError;
	//private static $showSQL;
	
	
	public static function connect($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort, $DBSocket = null){
		$driver = self::getDriver();
		if(in_array($driver, array('mysql', 'mysqli'))){
			$DBClass = 'DB'.ucfirst($driver);
			self::$DBResultClass = $DBClass.'Result';
			$host_data = self::parse_db_host( $DBHost );
			if ( $host_data ) {
				list( $DBHost, $DBPort, $DBSocket) = $host_data;
			}
			self::$DBDriver = new $DBClass($DBHost, $DBUsername, $DBPassword, $DBName, $DBPort, $DBSocket);
			$DBConnect = self::$DBDriver->connect();
			if($DBConnect !== true) {
				return $DBConnect;
			}
		} else {
			return "PHP has no mysql extension installed";
		}
		return true;
	}

	public static function getDriver() {
		if(class_exists('mysqli')){
				$driver = 'mysqli';
		}
		elseif(function_exists('mysql_connect')){
				$driver = 'mysql';
		}
		else{
				return false;
		}
		return $driver;
	}
	
	private static function get($params, $type){
		if(empty($params)) return false;
		
		$result = array();		
		$query = self::prepareQ('select', $params);
		
		$query_result = self::doQuery($query);	
		if(!$query_result) return $query_result;	
		$_result = new self::$DBResultClass($query_result);	
		
		
		if($_result){
			if($type == 'array'){
				while($row = $_result->nextRow()){
					if(!empty($params[3])){//array key hash
						$result[ $row[$params[3]] ] = $row;
					}
					else{
					$result[] = $row;
				}
			}
			}
			elseif($type == 'row'){
				$result = $_result->nextRow();
			}
			elseif($type == 'exists'){
				$result = $_result->rowExists();
			}
			elseif($type == 'field'){
				$row = $_result->nextRow();
				$result = ($row && is_array($row)) ? reset($row) : NULL;
			}
			elseif($type == 'fields'){
				while($row = $_result->nextRow()){
					if(!empty($params[3])){//array key hash
						$result[ $row[$params[3]] ] = reset($row);
					}
					else{
					$result[] = reset($row);
					}
				}
			}
			$_result->free();
		}
		return $result;
	}
	
	public static function getArray(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'array');
	}
	public static function getRow(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'row');
	}
	
	public static function getExists(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'exists');
	}
	
	public static function getField(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'field');
	}
	
	public static function getFields(){//table, select, conditions
		$args = func_get_args();
		return self::get($args, 'fields');
	}
	
	private static function prepareQ($type, $params){
		
		if(!empty($params) && count($params) == 1){
			return $params[0];
		}
		
		if($type == 'select'){
			if(empty($conditions)){ $conditions = 'true'; }
			return "SELECT ".$params[1]." FROM ".$params[0]." WHERE ".$params[2];
		}
		elseif($type == 'insert' || $type == 'replace'){
			if(is_array($params[1])) $params[1] = self::array2MysqlSet($params[1]);
			return ($type == 'insert' ? "INSERT" : "REPLACE")." INTO ".$params[0]." SET ".$params[1];
		}
		elseif($type == 'update'){
			if(is_array($params[1])) $params[1] = self::array2MysqlSet($params[1]);
			return "UPDATE ".$params[0]." SET ".$params[1]." WHERE ".$params[2];
		}
		elseif($type == 'delete'){
			return "DELETE FROM ".$params[0]." WHERE ".$params[1];
		}
	}
	
	public static function insert(){//table, setCommand
		$args=func_get_args();
		$query = self::prepareQ('insert', $args);
		return self::insertReplace($query);
	}
	
	
	public static function replace(){//table, setCommand
		$args=func_get_args();
		$query = self::prepareQ('replace', $args);
		return self::insertReplace($query);
	}
	
	private static function insertReplace($query){
		
		if(self::doQuery($query)){
			$lastInsertID = self::lastInsertID();
			if(!empty($lastInsertID)) return $lastInsertID;
			return true;
		}
		return false;
	}
	
	public static function update(){//table, setCommand, conditions
		$args=func_get_args();
		$query = self::prepareQ('update', $args);
		return self::doQuery($query);
	}
	
	public static function delete(){//table, conditions
		$args=func_get_args();
		$query = self::prepareQ('delete', $args);
		return self::doQuery($query);
	}

	public static function doQuery($queryString){	
	
		//$queryString = str_replace('?:', Reg::get('config.SQL_TABLE_NAME_PREFIX'), $queryString);
		
		self::$queryString = $queryString;
		
		if(self::$printAllQuery || self::$printQuery)
			echo '<br>'.self::$queryString.'<br>';

		$query = self::$DBDriver->query(self::$queryString);

		if($query)
			 return $query;
		else
		{
			self::printError(debug_backtrace());
			echo "\n".self::$queryString."\n<br>";
			return false;
		}
	}
	
	public static function getLastQuery(){//avoid using this function, it should be called as soon as query is executed
		return self::$queryString;		
	}
	
	private static function lastInsertID(){
		return self::$DBDriver->insertID();
	}
	
	public static function errorNo(){
		return self::$DBDriver->errorNo();
	}
	
	public static function error(){
		return self::$DBDriver->error();
	}
	
	public static function affectedRows(){
		return self::$DBDriver->affectedRows();
	}
	
	public static function realEscapeString($val){
		return self::$DBDriver->realEscapeString($val);
	}
	
	public static function escapse($val){ //same as public static function realEscapeString($val) 
		return self::$DBDriver->realEscapeString($val);
	}
	
	private static function printError($traceback_detail){
		echo "<b>Manual SQL Error</b>: [". self::$DBDriver->errorNo()."] " . self::$DBDriver->error() . "<br />\n
		 in file <b>" . $_SERVER['PHP_SELF'] ."</b> On line <b>" . $traceback_detail[count($traceback_detail) - 1]['line'] . "</b><br> ";
	}
	
	private static function array2MysqlSet($array){
		$mysqlSet='';
		$isPrev=false;
		foreach($array as $key => $value)
		{
			if($isPrev) $mysqlSet .= ', ';
			if(isset($value) && is_array($value))
				$mysqlSet .= $key." = ".self::realEscapeString($value[0]).""; //without quotes
			else
				$mysqlSet .= $key." = '".self::realEscapeString($value)."'";
			$isPrev = true;
		}
		return $mysqlSet;
	}
	
	private static function array2MysqlSelect($array){
		$mysqlSet='';
		$isPrev=false;
		foreach($array as $key => $value)
		{
			if($isPrev) $mysqlSet .= ', ';
			$mysqlSet .= $value;
			$isPrev = true;
		}
		return $mysqlSet;
	}
	
	public static function setPrintQuery($var){
		self::$printQuery = $var;
	}

	public static function parse_db_host( $host ) {
		$port    = null;
		$socket  = null;
		$is_ipv6 = false;

		// First peel off the socket parameter from the right, if it exists.
		$socket_pos = strpos( $host, ':/' );
		if ( $socket_pos !== false ) {
			$socket = substr( $host, $socket_pos + 1 );
			$host   = substr( $host, 0, $socket_pos );
		}

		// We need to check for an IPv6 address first.
		// An IPv6 address will always contain at least two colons.
		if ( substr_count( $host, ':' ) > 1 ) {
			$pattern = '#^(?:\[)?(?P<host>[0-9a-fA-F:]+)(?:\]:(?P<port>[\d]+))?#';
			$is_ipv6 = true;
		} else {
			// We seem to be dealing with an IPv4 address.
			$pattern = '#^(?P<host>[^:/]*)(?::(?P<port>[\d]+))?#';
		}

		$matches = array();
		$result  = preg_match( $pattern, $host, $matches );

		if ( 1 !== $result ) {
			// Couldn't parse the address, bail.
			return false;
		}

		$host = '';
		foreach ( array( 'host', 'port' ) as $component ) {
			if ( ! empty( $matches[ $component ] ) ) {
				$$component = $matches[ $component ];
			}
		}

		return array( $host, $port, $socket, $is_ipv6 );
	}
}

//-------------------------------------------------------------------------------------------------------------------->

# stores a mysql result
class DBResult{
	var $DBResult;
	function __construct($newResult)
	{
		$this->DBResult = $newResult;
	}
	function numRows()
	{
		return $this->DBResult->num_rows;
	}
	function nextRow()
	{
		return $this->DBResult->fetch_assoc();
	}
	function rowExists()
	{
		if (!$this->numRows())
			return false;
		return true;
	}
	function free(){
		$this->DBResult->free();
	}
	
}
//Code from db.php - End

class DBUpdateEngine extends DB
{
	
	public static function getTextColumns($table) 
	{
		$type_where  = "type NOT LIKE 'tinyint%' AND ";
		$type_where .= "type NOT LIKE 'smallint%' AND ";
		$type_where .= "type NOT LIKE 'mediumint%' AND ";
		$type_where .= "type NOT LIKE 'int%' AND ";
		$type_where .= "type NOT LIKE 'bigint%' AND ";
		$type_where .= "type NOT LIKE 'float%' AND ";
		$type_where .= "type NOT LIKE 'double%' AND ";
		$type_where .= "type NOT LIKE 'decimal%' AND ";
		$type_where .= "type NOT LIKE 'numeric%' AND ";
		$type_where .= "type NOT LIKE 'date%' AND ";
		$type_where .= "type NOT LIKE 'time%' AND ";
		$type_where .= "type NOT LIKE 'year%' ";

		$result = self::getArray("SHOW COLUMNS FROM `{$table}` WHERE {$type_where}");
		if (empty($result)) { 
			return null;
		} 
		$fields = array(); 
		if (count($result) > 0 ) { 
			foreach ($result as $key => $row) {
				$fields[] = $row['Field']; 
			} 
		} 

		$result = self::getArray("SHOW INDEX FROM `{$table}`");
		if (count($result) > 0) { 
			foreach ($result as $key => $row) {
				$fields[] = $row['Column_name']; 
			} 
		} 
	
		return (count($fields) > 0) ? $fields : null;
	}

	public static function load($list = array(), $tables = array(), $fullsearch = false) 
	{
		$report = array(
			'scan_tables' => 0, 
			'scan_rows' => 0, 
			'scan_cells' => 0,
			'updt_tables' => 0, 
			'updt_rows' => 0, 
			'updt_cells' => 0,
			'errsql' => array(), 
			'errser' => array(), 
			'errkey' => array(),
			'errsql_sum' => 0, 
			'errser_sum' => 0, 
			'errkey_sum' => 0,
			'time' => '', 
			'err_all' => 0
		);
		
		$walk_function = @function(&$str){
			$str = "`$str`";
		};

		
		if (is_array($tables) && !empty($tables)) {
			
			foreach ($tables as $table) 
			{
				$report['scan_tables']++;
				$columns = array();

				$fields = self::getArray('DESCRIBE ' . $table);
				foreach ($fields as $key => $column) {
					$columns[$column['Field']] = $column['Key'] == 'PRI' ? true : false;
				}
				
				$row_count = self::getField("SELECT COUNT(*) FROM `{$table}`");		
				if ($row_count == 0) {
					
					continue;
				}

				/*$page_size = 25000;
				$offset = ($page_size + 1);
				$pages = ceil($row_count / $page_size);*/

				$colList = '*';
				$colMsg  = '*';
				if (! $fullsearch) 
				{
					$colList = self::getTextColumns($table);
					if ($colList != null && is_array($colList)) {
						array_walk($colList, $walk_function);
						$colList = implode(',', $colList);
					} 
					$colMsg = (empty($colList)) ? '*' : '~';
				}
				
				if (empty($colList)) 
				{
					
					continue;
				} 
				if (!empty($GLOBALS['offset'])) {
					$row_offset = $GLOBALS['offset'];
				}else{
					$row_offset = 0;
				}
				$limit = 25000;

				//Paged Records
				$upd = false;
				// for ($page = 0; $page < $pages; $page++) 
				// {
				// 	$current_row = 0;
				// 	$start = $page * $page_size;
				// 	$end   = $start + $page_size;
				// 	$sql = sprintf("SELECT {$colList} FROM `%s` LIMIT %d, %d", $table, $start, $offset);
				// 	$data  = self::getArray($sql);

				// 	if (empty($data))
				// 		//$report['errsql'][] = mysqli_error($conn);
					
				// 	$scan_count = ($row_count < $end) ? $row_count : $end;
				while($row_count > $row_offset){

					$sql = sprintf("SELECT {$colList} FROM `%s` LIMIT %d OFFSET %d", $table, $limit, $row_offset);

					$select_args = array();
					$select_args['columns'] = $colList;//currently class wpmerge_select_by_memory_limit, only * and `table_name`.`column_name1` supported
					$select_args['table'] = $table;
					$select_args['limit'] = $limit;
					$select_args['offset'] = $row_offset;
					$select_args['total_rows'] = $row_count;
					$data  = self::getArray($sql);

					foreach ($data as $key => $row) {

						$report['scan_rows']++;
						$current_row = $row_offset + 1;
						$upd_col = array();
						$upd_sql = array();
						$where_sql = array();
						$upd = false;
						$serial_err = 0;

						
						foreach ($columns as $column => $primary_key) 
						{
							$report['scan_cells']++;
							$edited_data = $data_to_fix = $row[$column];
							$base64coverted = false;
							$txt_found = false;

							
							if (!empty($row[$column]) && !is_numeric($row[$column])) 
							{
								//Base 64 detection
								if (base64_decode($row[$column], true)) 
								{
									$decoded = base64_decode($row[$column], true);
									if (self::is_serialized($decoded)) 
									{
										$edited_data = $decoded;
										$base64coverted = true;
									}
								}
								
								//Skip table cell if match not found
								foreach ($list as $item) 
								{
									if(isset($item['method']) && $item['method'] === 'preg_replace'){
										//$__update_data_start_time = microtime(1);
										$___temp = preg_match('/'.$item['search'].'/U', $edited_data);
										//$GLOBALS['WPMERGE_preg_replace_TIME_TAKEN2'] += microtime(1) - $__update_data_start_time;
										if($___temp ){
											$txt_found = true;
											break;
										}
									}
									elseif (!empty($item['search']) && strpos($edited_data, $item['search']) !== false) {
										$txt_found = true;
										break;
									}
								}
								// if (! $txt_found) {
								// 	continue;
								// }

								//Replace logic - level 1: simple check on any string or serlized strings
								if ($txt_found) {
									$edited_data = self::recursive_unserialize_replace($list, $edited_data, false, (isset($item['method']) ? $item['method'] : false));
								}

								//Replace logic - level 2: repair serilized strings that have become broken
								$serial_check = self::fix_serial_string($edited_data);
								if ($serial_check['fixed']) 
								{
									$edited_data = $serial_check['data'];
								} 
								elseif ($serial_check['tried'] && !$serial_check['fixed']) 
								{
									$serial_err++;
								}
							}

							//Change was made
							if ($edited_data != $data_to_fix || $serial_err > 0) 
							{
								$report['updt_cells']++;
								//Base 64 encode
								if ($base64coverted) {
									$edited_data = base64_encode($edited_data);
								}
								$upd_col[] = $column;
								$upd_sql[] = $column . ' = "' . self::realEscapeString($edited_data) . '"';
								$upd = true;
							}

							if ($primary_key) {
								$where_sql[] = $column . ' = "' . self::realEscapeString($data_to_fix) . '"';
							}
						}

						if ($upd && !empty($where_sql)) 
						{
							
							$sql = "UPDATE `{$table}` SET " . implode(', ', $upd_sql) . ' WHERE ' . implode(' AND ', array_filter($where_sql));
							$result = self::doQuery($sql);

							if ($result) {
								if ($serial_err > 0) {
									$report['errser'][] = "SELECT " . implode(', ', $upd_col) . " FROM `{$table}`  WHERE " . implode(' AND ', array_filter($where_sql)) . ';';
								}
								$report['updt_rows']++;
							}
						} elseif ($upd) {
							$report['errkey'][] = sprintf("Row [%s] on Table [%s] requires a manual update.", $current_row, $table);
						}

						$row_offset++;
						if(check_for_clone_break()){
	                        return array('offset' => $row_offset);
						}
					}

					if( $row_count <= $row_offset ){
                        //table is completed, page for loop going to complete
						$GLOBALS['offset'] = 0;
                        return true;
                    }

					
				}

				if ($upd) {
					$report['updt_tables']++;
				}
			}
		}
		
		$report['errsql_sum'] = empty($report['errsql']) ? 0 : count($report['errsql']);
		$report['errser_sum'] = empty($report['errser']) ? 0 : count($report['errser']);
		$report['errkey_sum'] = empty($report['errkey']) ? 0 : count($report['errkey']);
		$report['err_all'] = $report['errsql_sum'] + $report['errser_sum'] + $report['errkey_sum'];
		return $report;
	}


	public static function recursive_unserialize_replace($from_to_list=array(), $data = '', $serialised = false, $method=false) {
		try {
			//$__update_data_start_time = microtime(1);
			//$unserialized = @unserialize($data);
			//$GLOBALS['WPMERGE_replace_unserialize_TIME_TAKEN'] += microtime(1) - $__update_data_start_time;

			if (is_string($data) && ($unserialized = @unserialize($data)) !== false) {
				$data = self::recursive_unserialize_replace($from_to_list, $unserialized, true, $method);
			} else if (is_array($data)) {
				$_tmp = array();
				foreach ($data as $key => $value) {
					$_tmp[$key] = self::recursive_unserialize_replace($from_to_list, $value, false, $method);
				}
				$data = $_tmp;
				unset($_tmp);
			} else if (is_object($data)) {

				$_tmp = $data;
				$props = get_object_vars( $data );
				foreach ($props as $key => $value) {
					//If some objects has \0 in the key it creates the fatal error so skip such contents
					if (strstr($key, "\0") !== false ) {
						continue;
					}
					$_tmp->$key = self::recursive_unserialize_replace( $from_to_list, $value, false, $method );
				}
				$data = $_tmp;
				unset($_tmp);
			} else {
				if (is_string($data)) {
					foreach ($from_to_list as $item) {
						//$item['search'], $item['replace']
						if(isset($item['method']) && $item['method'] === 'preg_replace'){
							//$__update_data_start_time = microtime(1);
							
							$data = preg_replace('/'.$item['search'].'/U', $item['replace'], $data);
							//$GLOBALS['WPMERGE_preg_replace_TIME_TAKEN'] += microtime(1) - $__update_data_start_time;
						}
						else{
							//$__update_data_start_time = microtime(1);
							$data = str_replace($item['search'], $item['replace'], $data);
							//$GLOBALS['WPMERGE_str_replace_TIME_TAKEN'] += microtime(1) - $__update_data_start_time;
						}
					}
				}
			}

			if ($serialised){
				//$__update_data_start_time = microtime(1);
				$___return_tets = serialize($data);
				//$GLOBALS['WPMERGE_replace_serialize_TIME_TAKEN'] += microtime(1) - $__update_data_start_time;
				return $___return_tets;
			}

		} catch (Exception $error){

		}
		return $data;
	}

	public static function is_serialized($data) 
	{
		$test = @unserialize(($data));
		return ($test !== false || $test === 'b:0;') ? true : false;
	}

	public static function fix_serial_string($data) 
	{
		$result = array('data' => $data, 'fixed' => false, 'tried' => false);
		if (preg_match("/s:[0-9]+:/", $data)) 
		{
			if (!self::is_serialized($data)) 
			{
				$regex = '!(?<=^|;)s:(\d+)(?=:"(.*?)";(?:}|a:|s:|b:|d:|i:|o:|N;))!s';
				$serial_string = preg_match('/^s:[0-9]+:"(.*$)/s', trim($data), $matches);
				//Nested serial string
				if ($serial_string) 
				{
					$inner = preg_replace_callback($regex, 'DBUpdateEngine::fix_string_callback', rtrim($matches[1], '";'));
					$serialized_fixed = 's:' . strlen($inner) . ':"' . $inner . '";';
				} 
				else 
				{
					$serialized_fixed = preg_replace_callback($regex, 'DBUpdateEngine::fix_string_callback', $data);
				}
				
				if (self::is_serialized($serialized_fixed)) 
				{
					$result['data'] = $serialized_fixed;
					$result['fixed'] = true;
				}
				$result['tried'] = true;
			}
		}
		return $result;
	}

	private static function fix_string_callback($matches) 
	{
		return 's:' . strlen(($matches[2]));
	}

}

function same_server_clone_db() {
	$limit = 300;

	$old_table_prefix = get_table_prefix(dirname(dirname(__FILE__)));

	$wp_tables = get_all_tables($old_table_prefix);

	foreach ($wp_tables as $table) {
		$unique_prefix = $GLOBALS['db_table_prefix'];
		$new_table = preg_replace('/'.$old_table_prefix.'/', $unique_prefix, $table, 1);
		// $new_table =  str_replace($old_table_prefix, $unique_prefix, $table); 

		$unique_prefix = (string) $unique_prefix;

		// $table_skip_status = $this->exclude_class_obj->is_excluded_table($table)
		if (preg_match('#^'.$old_table_prefix.'#', $table) != 1) {
			continue;
		}
		if (!is_complete($table)) {
			if (0) {
				update_iterator($table, -1); //Done
				continue;
			}

			$table_meta = get_table_data($table);
			extract($table_meta);
		} else {
			continue;
		}
		if ($is_new) {
			$result = clone_table_structure($table, $new_table);

			if ($result === false) {
				update_iterator($table, -1); //Done
				continue;
			}
		}
		clone_table_content($table, $new_table, $limit, $offset);
	}
}

function get_all_tables($old_table_prefix = false){
	$result = DB::getFields( 'SHOW TABLES LIKE "%'.$old_table_prefix.'%"');
	// $sql = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME NOT LIKE '%".$GLOBALS['db_table_prefix']."%' AND TABLE_SCHEMA = '".DB_NAME."'";
	
	// $result = DB::doQuery($sql);
	// file_put_contents(dirname(__FILE__)."/__debugger1.php", var_export($result,1)."\n<br><br>\n",FILE_APPEND );
	return $result;
}

function clone_table_structure($table, $new_table){
	DB::doQuery("DROP TABLE IF EXISTS `$new_table`");

	$sql = "CREATE TABLE `$new_table` LIKE `$table`";
	$is_cloned = DB::doQuery($sql);

	if ($is_cloned === false) {

		return false;
	}

	return true;
}

function clone_table_content($table, $new_table, $limit, $offset){
	while(true){
		$inserted_rows = 0;
		DB::doQuery("SET NAMES 'utf8'");
		$inserted_rows = DB::doQuery(
			"insert `$new_table` select * from `$table` limit $offset, $limit"
		);
		$inserted_rows = DB::affectedRows();
		if ($inserted_rows !== false) {
			if ($offset != 0) {

			}
			$offset = $offset + $inserted_rows;
			if ($inserted_rows < $limit) {
				update_iterator($table, -1); //Done
				break;
			}
			if(check_for_clone_break()){
				update_iterator($table, $offset);
				global $response_arr;

			    $response_arr = array();

			    initialize_response_array($response_arr);

			    $response_arr['db_clone'] = true;

			    $response_arr['status'] = 'partiallyCompleted';

			    $response_arr['break'] = true;

			    $response_arr['peak_mem_usage'] = (memory_get_peak_usage(true)/1024/1024);

			    status("Extract will continue next call", $success=true, $return=false);
			    die(status("multicall", $success=true, $return=false, $response_arr));
			}
		} else {
			update_iterator($table, -1); //Done
			break;
		}
	}
}
function same_server_add_completed_table(){
	$completed_tables = get_option('same_server_clone_db_completed_tables');
	if (empty($completed_tables)) {
		return set_option('same_server_clone_db_completed_tables', 1);
	}
	return set_option('same_server_clone_db_completed_tables', $completed_tables + 1);
}

function is_complete($name) {
	$table = get_table($name);

	if ($table) {
		return $table['offset'] == -1;
	}

	return false;
}

function get_table($name) {
	$single_table_result = DB::getArray("IWP_processed_iterator", '*', "name = '$name'");

	if (!empty($single_table_result)) {
		return $single_table_result[0];
	}
}

function get_table_data($table){
	$table_data = get_table($table);

	if ($table_data) {
		return array('offset' => $table_data['offset'], 'is_new' => false);
	}

	return array('offset' => 0, 'is_new' => true);
}
