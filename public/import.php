<?php

$GLOBALS['IWP_MMB_PROFILING']['ACTION_START'] = microtime(1);
global $extract_start_time;
$extract_start_time = $GLOBALS['IWP_MMB_PROFILING']['ACTION_START'];
@ini_set('memory_limit', "-1"); // For big uploads
@ini_set("max_execution_time", 1200);
@set_time_limit(1200);
error_reporting(E_ALL ^ E_NOTICE);
@ini_set("display_errors", 1);
@ignore_user_abort(true);

include("includes/app.php");
if ($_GET['startImport']) {
	$obj = new IWP_IMPORT();
	$obj->importSQL();
}

class IWP_IMPORT{
	private $dbFolder;
	private $sqlFile;
	private $dbFile;
	private $mysqlPath;
	public  $loopCount;
	public  $excutedQueryCount;
	public  $nextDBInsertID;

	public function __construct(){
		$this->dbFolder = APP_ROOT.'/uploads/';
		$this->sqlFile  = 'iwp_db.gz';
		$this->dbFile   = $this->dbFolder.$this->sqlFile;
		$this->loopCount= 0;
		$this->nextDBInsertID = 0;
		$this->excutedQueryCount = 0;
	}


	public function importSQL(){
		DB::doQuery("SET FOREIGN_KEY_CHECKS = 0");
		DB::doQuery("SET unique_checks=0");
		DB::doQuery("SET NAMES 'utf8'");
		if (!file_exists($this->dbFile)) {
			die('File not exist under IWP_ROOT/uploads folder');
		}
		$handle = gzopen($this->dbFile, "r");

		if($handle){
			while (!feof($handle)){
				$line = gzgets($handle);
				if($this->loopCount < $this->nextDBInsertID){
					continue;
				}

                if(substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 3) == '/*!'){
                    continue;
				}

				$current_query .= $line;
				if (strlen($current_query) < 10 || $current_query == ";") {
					continue;
				}
				if ($change_collotion == true && strrpos($current_query,'utf8mb4_unicode_520_ci')) {
					$current_query = str_replace('utf8mb4_unicode_520_ci','utf8mb4_unicode_ci',$current_query);
				}

				if(substr(trim($line), -1, 1) == ';'){
				    // Perform the query
					$this->excutedQueryCount ++;
				    $result = DB::doQuery($current_query);
				    if (!$result) {
				    	$failedQueryCount++;
						//------------Due to big query, error msg is not getting saved in IWP Panel DB due to max packet length and other issues-- this is a fix for it------
						$temp_error_replace_text = '...[Big text removed for error]...';
						$max_error_query_length = 1500 + strlen($temp_error_replace_text);
						$temp_current_query = $current_query;
						if(strlen($current_query) > $max_error_query_length){
							$temp_current_query = substr_replace($temp_current_query, '...[Big text removed for error]...', 750, -750);
						}
						$temp_current_query = htmlentities($temp_current_query);
						//------------Due to big query, error msg is not getting saved in IWP Panel DB due to max packet length and other issues-- this is a fix for it------
						echo "line count".$count;
				        $db_error = 'Error performing query "<strong>' . $temp_current_query . '</strong>": ' . DB::error().' Error Number'.DB::errorNo();
				        status("Failed to restore: "  . $db_error, $success=true, $return=false);
				        if (DB::errorNo()==1273) {
				        	$current_query = str_replace('utf8mb4_unicode_520_ci','utf8mb4_unicode_ci',$current_query);
				        	$result = DB::doQuery($current_query);
				        	$change_collotion = true;
				        }
				        // break;
				    }
				    // Reset temp variable to empty
				    $current_query = '';
					
					$is_multicall_break = checkForTime();
					//if($key == 10){
					if($is_multicall_break){
						echo "Current query executed : ".$this->excutedQueryCount;
					}
				}
			}
		}else{
			die('Unable open to the file');
		}

		gzclose($handle);
		echo "Database import successfully done";
		echo "<br>";
		echo "Total query executed : ".$this->excutedQueryCount;
		echo "<br><a href=".APP_URL.">Reload your panel</a>";

	}



}

function checkForTime(){
	global $extract_start_time;
	$extract_time_taken = microtime(1) - $extract_start_time;
	
	if($extract_time_taken >= 22){
		return true;
	}
}