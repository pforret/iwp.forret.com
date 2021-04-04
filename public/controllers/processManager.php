<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

$settings = Reg::get('settings');
define('MAX_SIMULTANEOUS_REQUEST_PER_IP', $settings['MAX_SIMULTANEOUS_REQUEST_PER_IP'] > 0 ? $settings['MAX_SIMULTANEOUS_REQUEST_PER_IP'] : 2 );

define('MAX_SIMULTANEOUS_REQUEST', $settings['MAX_SIMULTANEOUS_REQUEST'] > 0 ? $settings['MAX_SIMULTANEOUS_REQUEST'] : 3 );
//define('MAX_SIMULTANEOUS_REQUEST_PER_SERVERGROUP', $settings['MAX_SIMULTANEOUS_REQUEST_PER_SERVERGROUP']);

define('TIME_DELAY_BETWEEN_REQUEST_PER_IP', $settings['TIME_DELAY_BETWEEN_REQUEST_PER_IP'] >= 0 ? $settings['TIME_DELAY_BETWEEN_REQUEST_PER_IP'] : 200 );

function executeJobs($callCount = 1){
	
	if(isset($GLOBALS['IS_EXECUTE_JOBS_OPEN']) && $GLOBALS['IS_EXECUTE_JOBS_OPEN']){
		echo 'recurrsive execute jobs call';
		return false;//recurrsive call
	}
	$GLOBALS['IS_EXECUTE_JOBS_OPEN'] = true;
	
	$settings = Reg::get('settings');
        $connectionMethod = getOption('connectionMethod');
        $connectionMode = getOption('connectionMode');
        $upperConnectionLevel = getOption('upperConnectionLevel');
	  $noRequestRunning = true;
	  $requestInitiated = 0;
	  $requestPending 	= 0;
	  $isExecuteRequest = false;
	  static $lastIPRequestInitiated = '';
	  

	  $totalCurrentRunningRequest = DB::getField("?:history H LEFT JOIN ?:sites S ON H.siteID = S.siteID", "COUNT(H.historyID)", "H.status IN ('initiated', 'running')");
	  
	  if($totalCurrentRunningRequest >= MAX_SIMULTANEOUS_REQUEST){ $GLOBALS['IS_EXECUTE_JOBS_OPEN'] = false; return false; }//dont execute any request
			  
	  $runningRequestByIP = DB::getFields("?:history H LEFT JOIN ?:sites S ON H.siteID = S.siteID", "COUNT(H.historyID), S.IP", "H.status IN ('initiated', 'running') AND H.isPluginResponse = '1' GROUP BY S.IP", "IP");//H.isPluginResponse = 1 only WP sites call
	  
	  if(!empty($runningRequestByIP)){ //some request(s) are running
		  $noRequestRunning = false;
		  $runningRequestByServer = DB::getFields("?:history H LEFT JOIN ?:sites S ON H.siteID = S.siteID", "COUNT(H.historyID), S.serverGroup", "H.status IN ('initiated', 'running') GROUP BY S.serverGroup", "serverGroup");			
	  }
	  
	  //get pending request
	  $where = array(
		      		'query' =>  "(H.status = 'pending' OR (H.status = ':status' AND H.timescheduled <= :timescheduled AND H.timescheduled > 0) OR H.status = 'retry') ORDER BY H.historyID",
		      		'params' => array(
		               ':status'=>'scheduled',
		               ':timescheduled'=>time()
	   				)
				); 
	  $pendingRequests = DB::getArray("?:history H LEFT JOIN ?:sites S ON H.siteID = S.siteID", "H.historyID, S.IP, S.serverGroup, H.actionID, H.type, H.status, H.runCondition, H.isPluginResponse, H.retried, H.siteID", $where);
	  
	  if($noRequestRunning){		
		  $runningRequestByIP 	= array();
		  $runningRequestByServer = array();				
	  }
	  
	  
	  if(!empty($runningRequestByIP) && $settings['CONSIDER_3PART_IP_ON_SAME_SERVER'] == 1){//running IP information
		  $tempRunningRequestByIP = $runningRequestByIP;
		  $runningRequestByIP 	= array();
		  foreach($tempRunningRequestByIP as $tempIP => $tempCount){//only for IPv4
			  $IP3Part = explode('.', $tempIP);
			  array_pop($IP3Part);
			  $newTempIP = implode('.', $IP3Part);			  
			  $runningRequestByIP[$newTempIP] = $tempCount;
			  
		  }
	  }
	  
	if(!empty($pendingRequests) && is_array($pendingRequests)){
	  foreach($pendingRequests as $request){
		  $checkIPRestriction = true;
		  
		  $IPConsidered = $request['IP'];
		  
		  if($request['isPluginResponse'] === '0'){
			  $request['IP'] = '';
			  $checkIPRestriction = false;
		  }
		  
		  if($checkIPRestriction && $settings['CONSIDER_3PART_IP_ON_SAME_SERVER'] == 1){//only for IPv4
			  $IP3Part = explode('.', $IPConsidered);
			  array_pop($IP3Part);
			  $IP3Part = implode('.', $IP3Part);			  
			  $IPConsidered = $IP3Part;
		  }
		  
		  $isWritingRequest = updatePTCHistoryStatus($request, 'writingRequest');

		  if ($request['type'] === 'PTC' && $request['status'] !== 'retry' && $isWritingRequest<=0){
		  	 continue;
		  }

		  if(!empty($request['runCondition']) && !isTaskRunConditionSatisfied($request['runCondition'])){
		  	   updatePTCHistoryStatus($request, $request['status']);
			   continue;  
		  }
		  if (manageClientsUpdate::isUpdateRunning($request['historyID'], $request['siteID'])) { //Right now we changed the check in runCondition
		  		updatePTCHistoryStatus($request, $request['status']);
		  		continue;
		  }
		  if (!isV3Compatible($request['historyID'])) {
		  		updatePTCHistoryStatus($request, $request['status']);
		  		continue;
		  }
		  if($checkIPRestriction && !isset($runningRequestByIP[ $IPConsidered ])) $runningRequestByIP[ $IPConsidered ] = 0;
		 // if(!isset($runningRequestByServer[ $request['serverGroup'] ])) $runningRequestByServer[ $request['serverGroup'] ] = 0;
		  
		  if($totalCurrentRunningRequest >= MAX_SIMULTANEOUS_REQUEST){ 
		  	$GLOBALS['IS_EXECUTE_JOBS_OPEN'] = false; 
		  	updatePTCHistoryStatus($request, $request['status']);
		  	return false; 
		  }
		  
		  //check already request are running in allowed level 
		  if($checkIPRestriction && $runningRequestByIP[ $IPConsidered ] >= MAX_SIMULTANEOUS_REQUEST_PER_IP /*|| $runningRequestByServer[ $request['serverGroup'] ] >=  MAX_SIMULTANEOUS_REQUEST_PER_SERVERGROUP*/){
			 
			  
			  if($runningRequestByIP[ $IPConsidered ] >= MAX_SIMULTANEOUS_REQUEST_PER_IP ){}
			 /* if($runningRequestByServer[ $request['serverGroup'] ] >=  MAX_SIMULTANEOUS_REQUEST_PER_SERVERGROUP)
			  echo 'MAX_SIMULTANEOUS_REQUEST_PER_SERVERGROUP<br>';*/
			  updatePTCHistoryStatus($request, $request['status']);
			  continue; //already request are running on the limits
		  }

		  isIPReqAndTotalRunReqOne($callCount, $runningRequestByIP[ $IPConsidered ], $totalCurrentRunningRequest, $request);
		  
		  $updateRequest = array('H.status' => 'initiated', 'H.microtimeInitiated' => microtime(true));
		  
		  $where = array(
		      		'query' =>   "(H.status = ':pending' OR (H.status = ':scheduled' AND H.timescheduled <= :timescheduled AND H.timescheduled > 0) OR (H.status = ':writingRequest' AND H.type = 'PTC')) AND H.historyID = :historyID",
		      		'params' => array(
		               ':pending'=>'pending',
		               ':scheduled'=>'scheduled',
		               ':timescheduled'=>time(),
		               ':writingRequest' => 'writingRequest',
		               ':historyID'=>$request['historyID']
	   				)
				);
		  $isUpdated = DB::update("?:history H", $updateRequest, $where);
		  //panelRequestManager::addSyatemActiveJobs($request['historyID']); For now this functionality no need 			 	
		  $isUpdated = DB::affectedRows();
		  
		  if($isUpdated){
			  //ready to run a child php to run the request
			  
			  if($lastIPRequestInitiated == $IPConsidered){
				  usleep((TIME_DELAY_BETWEEN_REQUEST_PER_IP * 1000));
			  }
			  $forceCurlMode = false;
			  if (forceCurlMode($request['historyID'])) {
			  	  $forceCurlMode = true;
			  }

			  //(defined('CRON_MODE')  && ( CRON_MODE == 'systemCronShortTime'  || CRON_MODE == 'systemCronDefault') ) //need to avoid balance idle time when systemCron is triggered. so that new trigger call(multiCall) can be called soon
			 // echo '<br>executing child process';
			  if(/*defined('IS_EXECUTE_FILE') || */(defined('CRON_MODE')  && ( CRON_MODE == 'systemCronShortTime'  || CRON_MODE == 'systemCronDefault') ) || /*$settings['executeUsingBrowser'] == 1*/ $connectionMode == 'curlMode' || ($connectionMethod=='auto' && $upperConnectionLevel=='curlMode') || $forceCurlMode){//this will also statisfy Reg::get('settings.executeUsingBrowser') == 1
                  $curlConnection = array('mode'=>'curl');
				  //echo '<br>executing_directly';
				  executeRequest($request['historyID']);
                                  updateConnectionMode($curlConnection,$request['historyID']);
				  $isExecuteRequest = true;
				  $requestPending++;
			  }
			  else{
				 // echo '<br>executing_async';
				 //$callAsyncInfo = callURLAsync(APP_URL.EXECUTE_FILE, array('historyID' =>  $request['historyID'], 'actionID' => $request['actionID']));					 
                                 $callAsyncInfo = nonBlockingBackgroundJob(array('historyID' =>  $request['historyID'], 'actionID' => $request['actionID']));
                                 if(!$callAsyncInfo['status'] && $connectionMethod==='auto'){
                                 	$curlConnection = array('mode'=>'curl');
                                 	Reg::set('settings.executeUsingBrowser', true);
                                    executeRequest($request['historyID']);
                                    updateConnectionMode($curlConnection,$request['historyID']);
                                    $isExecuteRequest = true;
                                    $requestPending++;
                                 }else{
                                    onAsyncFailUpdate($request['historyID'], $callAsyncInfo);
                                 }
				 // echo '<pre>callExecuted:'; echo'</pre>';
			  }

			  $requestInitiated++; 
			  
			  if($checkIPRestriction) $runningRequestByIP[ $IPConsidered ]++;
			 // $runningRequestByServer[ $request['serverGroup'] ] ++;
			  $totalCurrentRunningRequest++;
			  
			  
			  if($checkIPRestriction) $lastIPRequestInitiated = $IPConsidered;
			  
			  if($isExecuteRequest){ break; }//breaking here once executeRequest runs(direct call) next forloop job might be executed by other instance because that job loaded in array which already loaded from DB, still only the job inititated here will run  $isUpdated = DB::affectedRows();
		  }
		  else{
		  	updatePTCHistoryStatus($request, $request['status']);
			// echo 'update error, this request might be executed by someother instance.';  
		  }
	  }
	 } 
	  //return process
	  $GLOBALS['IS_EXECUTE_JOBS_OPEN'] = false;
	  return array('requestInitiated' => $requestInitiated, 'requestPending' => $requestPending);
}


function retryFailedTasks($historyID, $actionID, $request){
	$where = array(
		      		'query' =>  "actionID = ':actionID' AND status NOT IN ('retry', 'completed', 'netError', 'error')",
			      	'params' => array(
		               ':actionID'=>$actionID
					)
				);
	$runningTask = DB::getField("?:history", "COUNT(historyID)", $where);
	if($runningTask == 0) {
		if ($request['type'] === 'PTC' && !empty($request['runCondition'])) {
			$runCondition = unserialize($request['runCondition']);
			$where = $runCondition['query'];
			$lastHistoryID = $where['lastHistoryID'];
			if (!empty($lastHistoryID)) {
				$lastHistoryIDWhere = array(
				      		'query' =>  "historyID=':historyID'",
					      	'params' => array(
				               ':historyID'=>$lastHistoryID
							)
						);
				$lastHistoryIDStatus = DB::getField("?:history", "status", $lastHistoryIDWhere);
				if ($lastHistoryIDStatus != 'completed' && $lastHistoryIDStatus != 'error' && $lastHistoryIDStatus != 'netError' ) {
					return;
				}
			}
			$changedCondition = 	array();
			$changedCondition['satisfyType'] = 'OR';
			$changedCondition['query'] = array('table' => "history_additional_data",
											  'select' => 'historyID',
											  'where' => "historyID = ".$lastHistoryID." AND status IN('success', 'error', 'netError')",
											  'lastHistoryID' => $lastHistoryID
											);
			$updateRequest = array('H.status' => 'pending', 'runCondition' => serialize($changedCondition), 'H.retried' => '1', 'H.microtimeInitiated' => microtime(true));
		}else{
			$updateRequest = array('H.status' => 'pending', 'H.retried' => '1', 'H.microtimeInitiated' => microtime(true));
		}
		$where = array(
		      		'query' =>  "H.historyID=':historyID'",
			      	'params' => array(
		               ':historyID'=>$historyID
					)
				);
		$isUpdated = DB::update("?:history H", $updateRequest, $where);
	}
}

function isIPReqAndTotalRunReqOne($callCount, $runningRequestByIP, $totalCurrentRunningRequest, $request){
	if ($callCount == 1 && $runningRequestByIP <= 1 && $totalCurrentRunningRequest <= 1) {
		if ($request['retried'] == '0'){
		 	if($request['status'] == 'retry') {
				retryFailedTasks($request['historyID'], $request['actionID'], $request);
			} 
		}
	}
}

function isTaskRunConditionSatisfied($runCondition){
	
	if(empty($runCondition)){ return true; }
	
	$runCondition = unserialize($runCondition);
	
	if(empty($runCondition['satisfyType'])){
		$runCondition['satisfyType'] = 'OR';
	}
	
	if($runCondition['satisfyType'] != 'AND' && $runCondition['satisfyType'] != 'OR'){
		return ;
	}
	
	$OK = true;
	
	
	if(!empty($runCondition['query'])){
		$query = $runCondition['query'];
		$tempResult = DB::getExists('?:'.$query['table'], $query['select'], $query['where']);
		if($runCondition['satisfyType'] == 'OR' && !empty($tempResult)){
			return true;
		}elseif ($runCondition['satisfyType'] == 'OR' && empty($tempResult) && isHistoryCompleted($runCondition)) {
			return true;
		}
		elseif($runCondition['satisfyType'] == 'AND' && empty($tempResult)){
			$OK = false;
		}
	}
	if(!empty($runCondition['maxWaitTime'])){
		$tempResult = ($runCondition['maxWaitTime'] <= time());
		if($runCondition['satisfyType'] == 'OR' && !empty($tempResult)){
			return true;
		}
		elseif($runCondition['satisfyType'] == 'AND' && empty($tempResult)){
			$OK = false;
		}
	}
	
	if($runCondition['satisfyType'] == 'OR'){
		return false;
	}
	elseif($runCondition['satisfyType'] == 'AND'){
		return $OK;
	}
	return ;	
}

function forceCurlMode($hisID){
	if (empty($hisID)) {
		return false;
	}
	$forceCurlModeActions = array('staging');
	$where = array(
		      		'query' =>  "historyID=':historyID'",
			      	'params' => array(
		               ':historyID'=>$hisID
					)
				);
	$forceCurlMode = DB::getField("?:history", 'type', $where);
	if (!empty($forceCurlMode) && in_array($forceCurlMode, $forceCurlModeActions)) {
		return true;
	}
	return false;

}

function isHistoryCompleted($runCondition){
	if (empty($runCondition['query']['where'])) {
		return false;
	}
	$where = $runCondition['query']['where'];
	$conditionHisID = str_replace(array('historyID = ', " AND status IN('success', 'error', 'netError')"), '', $where);
	if (empty($conditionHisID)) {
		return false;
	}
	$where = array(
		      		'query' =>  "historyID=':historyID'",
			      	'params' => array(
		               ':historyID'=>$conditionHisID
					)
				);
	$status = DB::getField("?:history", 'status', $where);
	if (!empty($status) && $status == 'completed') {
		$where = array(
		      		'query' =>  "historyID=':historyID'",
			      	'params' => array(
		               ':historyID'=>$conditionHisID
					)
				);
		$additionaStatus = DB::getField("?:history_additional_data", 'status', $where);
		if (!empty($additionaStatus) && $additionaStatus == 'pending') {
			$success = DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Task killed because history table marked as completed', 'error' =>'not_history_add'), $where);
			if ($success) {
				return true;
			}

		}
	}
	return false;
}

function isV3Compatible($historyID){
	$responseProcessor = array();
	$responseProcessor['plugins']['install'] = $responseProcessor['themes']['install'] = 'installPluginsThemes';
	$responseProcessor['plugins']['manage'] = $responseProcessor['themes']['manage'] = 'managePluginsThemes';	
	$responseProcessor['plugins']['get'] = $responseProcessor['themes']['get'] = 'getPluginsThemes';
	$responseProcessor['stats']['getStats'] = 'getStats';
	$responseProcessor['PTC']['update'] = 'updateAll';
	$responseProcessor['backup']['now'] = 'backup';
	$responseProcessor['backup']['multiCallNow'] = 'backup';
	$responseProcessor['backup']['restore'] = 'restoreBackup';
	$responseProcessor['backup']['multiCallRestore'] = 'restoreBridgeUpload';
	$responseProcessor['backup']['bridgeExtractMulticallRestore'] = 'bridgeExtractMulticallRestore';
	$responseProcessor['backup']['restoreNew'] = 'restoreNewBackup';
	$responseProcessor['backup']['restoreBackupDownlaod'] = 'restoreBackupDownlaod';
	$responseProcessor['backup']['triggerBackupDownlaod'] = 'triggerBackupDownlaod';
	$responseProcessor['backup']['remove'] = 'removeBackup';
	$responseProcessor['cronTask']['runCron'] = 'pheonixBackupCron';
	$responseProcessor['cronDoAction']['pheonixBackupCronDoAction'] = 'pheonixBackupCronDoAction';
	$responseProcessor['site']['add'] = 'addSite';
	$responseProcessor['site']['readd'] = 'readdSite';
	$responseProcessor['site']['maintain'] = 'iwpMaintenance';
	$responseProcessor['site']['auto_updater_settings'] = 'editSite';
	$responseProcessor['site']['remove'] = 'removeSite';
	$responseProcessor['site']['backupTest'] = 'backupTest';
	$responseProcessor['clientPlugin']['update'] = 'updateClient';
	$responseProcessor['backup']['trigger'] = 'triggerRecheck';
	$responseProcessor['cookie']['get'] = 'getCookie';
	setHook('responseProcessors', $responseProcessor);
	$historyData = getHistory($historyID);
	$actionResponse = $responseProcessor[$historyData['type']][$historyData['action']];
	if(manageClients::methodResponseExists($actionResponse)){
		return true;
	}

	return false;
}

function updatePTCHistoryStatus($request, $status){
	$isUpdated = false;
	if ($request['type'] === 'PTC' && $request['status'] !== 'retry') {
		$historyID = $request['historyID'];
		$updateRequest = array('status' => $status);
		if ($status == 'writingRequest') {
			$where = array(
			      		'query' =>  "historyID=':historyID' AND status IN( 'pending', 'scheduled')",
				      	'params' => array(
			               ':historyID'=>$historyID
						)
					);
		}else{
			$where = array(
			      		'query' =>  "historyID=':historyID'",
				      	'params' => array(
			               ':historyID'=>$historyID
						)
					);
		}
		DB::update("?:history", $updateRequest, $where);
		$isUpdated = DB::affectedRows();
	}
	return $isUpdated;
}

?>