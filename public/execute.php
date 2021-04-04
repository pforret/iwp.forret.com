<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
$executeFileTimeStart = $timeStart = microtime(true);
@ignore_user_abort(true);
define('IS_EXECUTE_FILE', true);

$isExecuteJobs = false;

if(@$_REQUEST['check'] == 'sameURL'){
	echo 'same_url_connection';
	exit;
}

require_once('includes/app.php');

if(@$_REQUEST['check'] == 'sameURLUsingDB'){
	echo 'same_url_connection';
	sleep(5);
	updateOption('connectionMethodDBValue', $_REQUEST['connectionMethodDBValue']);
	exit;
}

set_time_limit(3600);//3600 = 1hr, this is only for safety, we are controlling timeout in CURL 

if($_REQUEST['runOffBrowserLoad'] == 'true'){
	runOffBrowserLoad();
	exit;
} else if ($_REQUEST['runWhileBrowserIdle'] == 'true'){
	runWhileBrowserIdle();
	exit;
} else if(!empty($_REQUEST['historyID']) && !empty($_REQUEST['actionID'])){

	$historyID = $_REQUEST['historyID'];
	$actionID = $_REQUEST['actionID'];
	
	//if(empty($historyID) || empty($actionID)){ echo 'invalidRequest'; exit; }
	//fix: add some security
	$where = array(
      		'query' =>  "historyID=':historyID' AND actionID=':actionID' ",
      		'params' => array(
               ':historyID'=>$historyID,
               ':actionID'=>$actionID
				)
		);
	$isValid = DB::getExists("?:history", "historyID", $where);
	if($isValid){
		
		if(empty($GLOBALS['userID'])){
			//setting userID of the task to session, because when this file running by fsock, it will not have the same session IWP Admin Panel
			$userID = DB::getField("?:history", "userID", $where);
			$GLOBALS['userID'] = $userID;
			$GLOBALS['offline'] = true;
		}
		echo 'executingRequest';
		executeRequest($historyID);
		
		$isExecuteJobs = true; 	
		
	}
} else if(isset($_SERVER['argc']) && $_SERVER['argc'] > 1){
    $command_args = $_SERVER['argv'];
    if(in_array('commandnb', $command_args)){
        foreach ($command_args as $arg){
           if(strpos($arg, 'historyID')!==false){
               $historyIDArg = explode('=', $arg);
               $historyID = $historyIDArg[1];
           }
           if(strpos($arg, 'actionID')!==false){
               $actionIDArg = explode('=', $arg);
               $actionID = $actionIDArg[1];
           }
           if(strpos($arg, 'sameURLUsingDB')!==false){
               $sameURLUsingDBArg = explode('=', $arg);
               $sameURLUsingDB = $sameURLUsingDBArg[1];
           }
           if(strpos($arg, 'connectionMethodDBValue')!==false){
               $dbValueArg = explode('=', $arg);
               $dbValue = $dbValueArg[1];
           }
        }
        
        if($sameURLUsingDB!=''){
            updateOption('connectionMethodDBValue', $dbValue);
            exit;
        }
    	$where = array(
          		'query' =>  "historyID=':historyID' AND actionID=':actionID' ",
          		'params' => array(
                   ':historyID'=>$historyID,
                   ':actionID'=>$actionID
    				)
    		);
        $isValid = DB::getExists("?:history", "historyID", $where);
	if($isValid){
		
		if(empty($GLOBALS['userID'])){
			$userID = DB::getField("?:history", "userID", $where);
			$GLOBALS['userID'] = $userID;
			$GLOBALS['offline'] = true;
		}
		echo 'executingRequest';
		executeRequest($historyID);
		
		$isExecuteJobs = true; 	
		
	}
    }
}

if($isExecuteJobs || $_REQUEST['executeJobs'] == 'true'){
	$noNewTaskAfterNSecs = 15;
    $loopCount = 0 ;
	//do additional jobs
	if(($executeFileTimeStart + $noNewTaskAfterNSecs) > time()){
		do{
			autoPrintToKeepAlive("keepAliveExecuteJobs");
			$status = executeJobs(++$loopCount);
		}
		while($status['requestInitiated'] > 0 && $status['requestPending'] > 0 && ($GLOBALS['executeFileTimeStart'] + $noNewTaskAfterNSecs) > time());
	}	
	exit;
}
	
?>