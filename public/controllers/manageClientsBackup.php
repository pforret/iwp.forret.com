<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
class manageClientsBackup{
	
	public static function backupProcessor($siteIDs, $params){
					
		$accountInfo = array('account_info' => $params['accountInfo']);
		if((!empty($accountInfo['account_info']['iwp_gdrive'])))
		{
			//$accountInfo['account_info']['iwp_gdrive']['gDriveEmail'] = unserialize(getOption('googleDriveAccessToken'));
			$repoID = $accountInfo['account_info']['iwp_gdrive']['gDriveEmail'];
			if(function_exists('backupRepositorySetGoogleDriveArgs')){
				$accountInfo['account_info']['iwp_gdrive'] = backupRepositorySetGoogleDriveArgs($accountInfo['account_info']['iwp_gdrive']);
			}else{
				addNotification($type='E', $title='Cloud backup Addon Missing', $message="Check if cloud backup addon exists and is active", $state='U', $callbackOnClose='', $callbackReference='');
				return false;
			}
		}
		$config = $params['config'];
		if(!empty($params['accountInfo']) && empty($config['delHostFile'])){
			$config['delHostFile'] = '1';
		}
		$timeout = (20 * 60);//20 mins
		$type = "backup";
		$action = ($config['mechanism'] == 'multiCall' || $config['mechanism'] == 'advancedBackup') ? "multiCallNow" : "now";
		$requestAction = "scheduled_backup";
		if($config['mechanism'] == 'advancedBackup'){
			$requestAction = "new_scheduled_backup";
		}
		
		if(empty($config['taskName'])){
			$config['taskName'] = 'Backup Now';
		}
			$config['exclude'] = str_replace(array(', ', ' ,'),',',$config['exclude']);
			if ($params['config']['mechanism'] == 'advancedBackup') {
				$exclude = $config['exclude'];
			}else{
				$exclude = explode(',', $config['exclude']);	
			}
			$include = explode(',', $config['include']);	
		   	// array_walk($exclude, 'trimValue');
			array_walk($include, 'trimValue');
			$defaultExcludeTables = getBackupDefaultExcludeTables();
			
			
			$requestParams = array('task_name' => $config['taskName'],'mechanism' => $config['mechanism'], 'args' => array('type' => $type, 'action' => $action, 'what' => $config['what'], 'optimize_tables' => $config['optimizeDB'], 'exclude' => $exclude, 'exclude_file_size' => (int)$config['excludeFileSize'], 'exclude_extensions' => $config['excludeExtensions'], 'include' => $include, 'del_host_file' => $config['delHostFile'], 'disable_comp' => $config['disableCompression'], 'fail_safe_db' => $config['failSafeDB'], 'fail_safe_files' => $config['failSafeFiles'], 'limit' => $config['limit'], 'backup_name' => $config['backupName'],'IWP_encryptionphrase'=> $config['IWP_encryptionphrase']), 'secure' => $accountInfo);
			if (!empty($config['excludeTables'])) {
				$excludeTables = explode(',', $config['excludeTables']);
				$requestParams['args']['exclude_tables'] = array_merge($defaultExcludeTables, $excludeTables);
			}else{
				$requestParams['args']['exclude_tables'] = $defaultExcludeTables;
			}
			
			// The following lines are used for client side activities log
			if(isset($config['backup_repo_type'])) {
				$requestParams['args']['backup_repo_type'] = $config['backup_repo_type'];
			}			
			if(isset($config['when'])) {
				$requestParams['args']['when'] = $config['when'];
			}
			if(isset($config['at'])) {
				$requestParams['args']['at'] = $config['at'];
			}			
			// The above lines are used for client side activities log
			
			if($action == "multiCallNow")
			{
				$timeout = (1 * 60);
				//this function set the multicall options value from config.php if available 
				setMultiCallOptions($requestParams);
			}else{
				if (defined('DISABLE_IWP_CLOUD_VERIFICATION') && DISABLE_IWP_CLOUD_VERIFICATION) {
					$requestParams['args']['disable_iwp_cloud_verification'] = true;
				}
			}			   			
			$historyAdditionalData = array();
			$historyAdditionalData[] = array('uniqueName' => $config['taskName'], 'detailedAction' => $type);
			  		
		$incTime = 20 * 60;//20 mins		
		$i = 0;
		$lastHistoryID = '';
		if(empty($params['timeScheduled'])){ $params['timeScheduled'] = time(); }
		if ($requestParams['mechanism'] == 'advancedBackup') {
			$requestParams['backup_nounce'] = backup_time_nonce();
		}
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID, "true");
			$istaskRunning = isTaskRunningBySiteID($siteID, 'backup');
			if($istaskRunning){
				$notificationMessage = $siteData['name']." - This site is currently being backed up. So, another backup cannot be initiated till it completes." ;
				addNotification('E', $title='SITE IS BACKING UP ALREADY', $message=$notificationMessage, $state='U', $callbackOnClose='', $callbackReference='');
				continue;
			}

			$events=1;
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			$PRP['status'] 			= 'pending';
			$PRP['timeScheduled'] = $params['timeScheduled'];
			
						
			if($lastHistoryID){
				$runCondition = 	array();
				$runCondition['satisfyType'] = 'OR';
				$runCondition['query'] = array('table' => "history_additional_data",
													  'select' => 'historyID',
													  'where' => "historyID = ".$lastHistoryID." AND status IN('success', 'error', 'netError')");
				//$runCondition['maxWaitTime'] = $params['timeScheduled'] + $incTime * $i;
				$PRP['runCondition'] = serialize($runCondition);
				$PRP['status'] = 'scheduled';
			}
			
				$lastHistoryID = prepareRequestAndAddHistory($PRP);
				$i++;
		  }
	}
	
	public static function backupResponseProcessor($historyID, $responseData){

		responseDirectErrorHandler($historyID, $responseData);

				
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
		
		$historyData = DB::getRow("?:history", "*", $where);
		$siteID = $historyData['siteID'];
		
		
		if(!empty($responseData['success']['error']) && is_string($responseData['success']['error'])){		
			$where = array(
	      		'query' =>  "historyID=:historyID",
	      		'params' => array(
	               ':historyID'=>$historyID
					)
			);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => strip_tags($responseData['success']['error']), 'error' => $responseData['success']['error_code']), $where);	
			return false;
		}
		else{
			if($historyData['type'] == 'backup' && $historyData['action'] == 'multiCallNow'){
				$historyResponseStatus[$historyID] = "multiCallWaiting";
				Reg::set("historyResponseStatus", $historyResponseStatus);
				if (!empty($responseData['success']['wp_content_url'])) {
					$wp_content_url = $responseData['success']['wp_content_url'];
					$log = $responseData['success']['backup_id'];
					DB::update("?:history_additional_data", array('successMsg' => "<a class='view_on_site' href='".rtrim($wp_content_url,'/')."/infinitewp/backups/log.".$log.".txt' target='_blank' > View log</a>"), $where);
				}
				updateHistory(array('status' => "multiCallWaiting"), $historyID);
				self::triggerRecheck($responseData, $siteID);
			}
			else{
				$where = array(
		      		'query' =>  "historyID=:historyID",
		      		'params' => array(
		               ':historyID'=>$historyID
						)
				);
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
						
				//---------------------------post process------------------------>
				$siteID = DB::getField("?:history", "siteID", $where);
			
				$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
				
				panelRequestManager::handler($allParams);
			}
		}
	}
	
	public static function triggerRecheck($data, $siteID){
		$parentHistoryID = (!empty($data['parentHistoryID']) ? $data['parentHistoryID'] : (!empty($data['success']['parentHID'])?$data['success']['parentHID']:$data['success']['success']['parentHID']));
				
		$allParams = array('action' => 'triggerRecheck', 'args' => array('params' => array('responseData' => $data['success'], 'backupParentHID' => $parentHistoryID), 'siteIDs' => array($siteID)));
				
		panelRequestManager::handler($allParams);
			
	}
	
	public static function triggerRecheckProcessor($siteIDs, $params, $extras){
		$type = "backup";
		$action = "trigger";
		$requestAction = "trigger_backup_multi";
		$timeout = 180;
		if(empty($params['backupParentHID'])){
			return;	
		}
		$scheduleTime  = time()+10;
		if (!empty($params['cron_data'])) {
			if (time()< $params['cron_data'][0]) {
				$scheduleTime = $params['cron_data'][0];
			}
		}
		/* This feature helps to avoid keep running scenario for certain case but it cause other issue 
		$where = array(
			      		'query' =>  "parentHistoryID = ':parentHistoryID' ORDER BY historyID DESC LIMIT 1",
			      		'params' => array(
			               ':parentHistoryID'=>$params['backupParentHID']
           				)
        			);
		$subTaskData = DB::getField("?:history", "status", $where);

		if (!empty($subTaskData) && $subTaskData == 'pending' && $subTaskData == 'running' && $subTaskData == 'initiated' && $subTaskData == 'scheduled') {
			return;
		}*/
		$where = array(
	      		'query' =>  "historyID=':historyID'",
	      		'params' => array(
	               ':historyID'=>$params['backupParentHID']
					)
			);
		$parentHistoryIDStatus = DB::getField("?:history", "status", $where);
		$parentHistoryRequest = DB::getField("?:history_raw_details", "request", $where);
		$parentHistoryRequest = unserialize(base64_decode($parentHistoryRequest));
		if ($parentHistoryRequest['params']['mechanism'] == 'advancedBackup') {
			$requestAction = 'trigger_backup_multi_new';
		}
	
		if(($parentHistoryIDStatus != 'multiCallWaiting')){
			return;
		}
		
		$where = array(
	      		'query' =>  "type='backup' AND action = 'trigger' AND parentHistoryID = ':historyID'",
	      		'params' => array(
	               ':historyID'=>$params['backupParentHID']
					)
			);
		$getCount = DB::getField("?:history", "count(historyID)", $where);
		if ((defined('MULTICALL_LIMIT_COUNT') && MULTICALL_LIMIT_COUNT)) {
			$limitCount = MULTICALL_LIMIT_COUNT;
		}else{
			$isEnableMulticalOption = getOption('isEnableMulticalOption');
			if (!empty($isEnableMulticalOption)) {
				$limitCount = 3000;
			}else{
				$limitCount = 1600;
			}
		}
		if($getCount >= $limitCount){
			
			updateHistory(array('status' => 'error', 'error' => 'max_trigger_calls_reached'), $params['backupParentHID'], array('status' => 'error', 'error' => 'max_trigger_calls_reached', 'errorMsg' => 'Multi-call limit reached.'));
			
			return;
		}
		$where = array(
	      		'query' =>  "type='backup' AND action = 'trigger' AND parentHistoryID = ':historyID' AND status not IN('completed', 'error', 'netError')",
	      		'params' => array(
	               ':historyID'=>$params['backupParentHID']
					)
			);
		if(DB::getExists("?:history", "historyID", $where)){
			return;
			
		}
		
		$requestParams = array('mechanism' => 'multiCall','backupParentHID' => $params['backupParentHID'], 'params' => $params['responseData']);

		$where = array(
	      		'query' =>  "historyID=':historyID'",
	      		'params' => array(
	               ':historyID'=>$params['backupParentHID']
					)
			);
		
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => "backupTrigger", 'detailedAction' => $action);
		
		$doNotShowUser = true;
			
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID, "true");
				  		
			$events=1;
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			if ($requestAction == 'trigger_backup_multi_new') {
				$PRP['status'] 			= 'scheduled';
				$PRP['timeScheduled'] 	= $scheduleTime;
			}
			$PRP['events'] 			= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			$PRP['doNotShowUser'] 	= $doNotShowUser;
			$PRP['parentHistoryID'] = $params['backupParentHID'];
			
			$historyID = prepareRequestAndAddHistory($PRP);

		  }
			DB::update("?:history", array('param1' => serialize(array('lastCallUpdate' => time(), 'lastHistoryID' => $historyID))), $where);
	}
	
	public static function triggerRecheckResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		if(!empty($responseData))
		{
			$where = array(
	      		'query' =>  "historyID=':historyID'",
	      		'params' => array(
	               ':historyID'=>$historyID
					)
			);
			$historyData = DB::getRow("?:history", "*", $where);
			$siteID = $historyData['siteID'];
			
			if($responseData['success']['success']['status'] == 'partiallyCompleted' || $responseData['success']['status'] == 'partiallyCompleted')
			{
				
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
				DB::update("?:history", array('status' => 'completed'), $where);
				$cronData = array();
				if (!empty($responseData['success']['success']['cron_disable'])) {
					self::checkAndRunPheonixCron($historyID, $siteID, $responseData['success']['success']['cron_params']);
				}
				if (!empty($responseData['success']['success']['cron_do_action'])) {

					self::checkAndRunPheonixCronDoAction($historyID, $siteID, array('backupParentHID' => $historyData['parentHistoryID'],'cron_data' => $cronData,'responseData' => $responseData['success']['success']['params']));
				}
				if ($responseData['success']['success']['params']) {

					$cronData = $responseData['success']['success']['cron_data'];
					$wp_content_url = $responseData['success']['success']['wp_content_url'];
					$log  = $responseData['success']['success']['params']['backup_id'];
					$where = array(
						'query' =>  "historyID=':historyID'",
						'params' => array(
							':historyID'=>$historyData['parentHistoryID']
						)
					);
					DB::update("?:history_additional_data", array('successMsg' => "<a class='view_on_site' href='".rtrim($wp_content_url,'/')."/infinitewp/backups/log.".$log.".txt' target='_blank'>View log</a>"), $where);
					$responseData = $responseData['success']['success']['params'];
				}
				$allParams = array('action' => 'triggerRecheck', 'args' => array('params' => array('backupParentHID' => $historyData['parentHistoryID'],'cron_data' => $cronData,'responseData' => $responseData), 'siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
				
				panelRequestManager::handler($allParams);
			}			
			elseif($responseData['success']['success']['status'] == 'completed' || $responseData['success']['status'] == 'completed')
			{
				$where = array(
					'query' =>  "historyID=':historyID'",
					'params' => array(
						':historyID'=>$historyID
					)
				);
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
				$where = array(
					'query' =>  "historyID=':historyID'",
					'params' => array(
						':historyID'=>$historyData['parentHistoryID']
					)
				);
				if (!empty($responseData['success']['success']['last_backup'])) {
					$siteData = getSiteData($siteID);
					$log = $responseData['success']['success']['last_backup']['backup_nonce'];
					$wp_content_url = $responseData['success']['success']['wp_content_url'];
					DB::update("?:history_additional_data", array('status' => 'success', 'successMsg' => "Complete log - <a class='view_on_site' href='".rtrim($wp_content_url,'/')."/infinitewp/backups/log.".$log.".txt' target='_blank'>View log</a>"), $where);
				}
				DB::update("?:history_additional_data", array('status' => 'success'), $where);

				updateHistory(array('status' => 'completed'), $historyData['parentHistoryID']);
				$allParams = false;
				if (method_exists('manageClientsInstallCloneCommon', 'triggerInstallCloneCommonNewSite')) {
					$allParams = manageClientsInstallCloneCommon::triggerInstallCloneCommonNewSite($historyID, $historyData, $responseData, $siteID);
				}

				if ($allParams == false) {
					$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true, 'byPassAccess' => 1)));
				}

				panelRequestManager::handler($allParams);
			}
			else
			{
				
				$errorMsg = isset($responseData['error']) ? @strip_tags($responseData['error']) : 'Unknown error occurred';
				$errorCode = isset($responseData['error_code']) ? $responseData['error_code'] : 'unknown_error_occurred';
				if (!empty($responseData['error']['error'])) {
					$errorMsg = $responseData['error']['error'];
					$errorCode = $responseData['error']['error_code'];
				}
				if (!empty($responseData['error']['wp_content_url'])) {
					$wp_content_url = $responseData['error']['wp_content_url'];
					$log = $responseData['error']['backup_id'];
					$errorMsg.="<a class='view_on_site' href='".rtrim($wp_content_url,'/')."/infinitewp/backups/log.".$log.".txt' target='_blank' > View log</a>";
				}
				$isEnableMulticalOption = getOption('isEnableMulticalOption');
				if (empty($isEnableMulticalOption) || $isEnableMulticalOption == 'Y') {
					$multicallErrorCode = !empty($responseData['success']['error_code']) ? $responseData['success']['error_code'] : $errorCode;
					$multicallRetryErrorCode = unserialize(getOption('multicallRetryErrorCode'));
					if (in_array($multicallErrorCode, $multicallRetryErrorCode)) {
						updateOption('isEnableMulticalOption', 'Y');
						$return = retryTaskBasedOnErrorCode($historyID);
						if ($return) {
							updateHistory(array('status' => 'error', 'error' => 'service_retrieable_errors'), $historyID);
							return 'notUpdate';
						}
					}
				}
				$tempErrorMsg = array(
					'Failed to connect to content.dropboxapi.com port 443: Connection timed out',
					'Could not resolve host: api.dropboxapi.com'
				);
				$tempErrorCode = array(
					'google_error_multipart_upload',
					'ftp_nb_fput_not_permitted_error'
				);
				if (in_array($errorMsg, $tempErrorMsg) || in_array($errorCode, $tempErrorCode)) {
					updateHistory(array('status' => 'error', 'error' => 'dropbox_error'), $historyID);
					return 'notUpdate';
				}
				$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
						)
				);
				DB::update("?:history_additional_data", array('status' => 'error'), $where);
				$where = array(
		      		'query' =>  "historyID=:historyID",
		      		'params' => array(
		               ':historyID'=>$historyData['parentHistoryID']
						)
				);
				DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => !empty($responseData['success']['error']) ? strip_tags($responseData['success']['error']) : $errorMsg, 'error' => !empty($responseData['success']['error_code']) ? $responseData['success']['error_code'] : $errorCode), $where);
				
				updateHistory(array('status' => 'completed'), $historyData['parentHistoryID']);
				
				return;
								
			}
			
			
		}
	}
	
	public static function restoreBackupProcessor($siteIDs, $params){
		
		$type = "backup";
		$action = "restore";
		$requestAction = "restore";
		$timeout = (20 * 60);//20 mins
		
		$requestParams = array('task_name' => $params['taskName'], 'result_id' => $params['resultID']);
		
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => $params['taskName'], 'detailedAction' => $action);
		
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID);		
			
			$events=1;
			
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			
			prepareRequestAndAddHistory($PRP);
		}	
	}
	
	public static function restoreBackupResponseProcessor($historyID, $responseData){
		
		responseDirectErrorHandler($historyID, $responseData);
		
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
			return;
		}
		
		
		if(!empty($responseData['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);
		}
		
		//---------------------------post process------------------------>
		$siteID = DB::getField("?:history", "siteID", $where);
	
		$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
		
		panelRequestManager::handler($allParams);
		
	}
	
	public static function removeBackupProcessor($siteIDs, $params, $extras = array()){
		
		$type = "backup";
		$action = "remove";
		if ($params['isNewBackup']) {
			$requestAction = "delete_backup_new";
		}else{
			$requestAction = "delete_backup";
		}
		
		$requestParams = array('task_name' => $params['taskName'], 'result_id' => $params['resultID']);
		
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => $params['taskName'], 'detailedAction' => $action);
		
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID);	
			$events=1;	
			
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			
			if(isset($extras['doNotShowUser'])){
				$PRP['doNotShowUser'] 	= $extras['doNotShowUser'];
			}
			
			//if(isset($extras['runCondition']) && $extras['runCondition'] == true){
//				$runCondition = 	array();
//				$runCondition['satisfyType'] = 'AND';
//				$runCondition['query'] = array('table' => "history",
//													  'select' => 'historyID',
//													   'where' => "parentHistoryID = ".$params['resultID']." AND status IN('completed', 'error', 'netError') ORDER BY ID DESC LIMIT 1");
//													  /*'where' => "type IN('backup', 'scheduleBackup') AND action IN('multiCallNow, 'now', 'multiCallRunTask', 'runTask') AND status IN('success', 'error', 'netError')");*/
//													  //'where' => "status NOT IN('multiCallWaiting')");
//				$PRP['runCondition'] = serialize($runCondition);
//				$PRP['status'] = 'scheduled';
//			}
		
			prepareRequestAndAddHistory($PRP);
		}	
	}
	
	public static function removeBackupResponseProcessor($historyID, $responseData){
		
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
			return;
		}
		
		if(!empty($responseData['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);
		}
		
		//---------------------------post process------------------------>
		$siteID = DB::getField("?:history", "siteID", $where);
	
		$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
		
		panelRequestManager::handler($allParams);	
	}

	public static function checkAndRunPheonixCron($historyID, $siteID,$params){
		$disabled = getOption('disablePheonixBackupCron');
		if (!empty($disabled)) {
			return false;
		}
		$isAlreadyScheduled = self::pheonixBackupCronCheck($siteID);
		if ($isAlreadyScheduled == false) {
			$allParams = array('action' => 'pheonixBackupCron', 'args' => array('params' => array('scheduleTime' => $params[0], 'backupParentHID' => $historyID), 'siteIDs' => array($siteID)));
			
			panelRequestManager::handler($allParams);
		}
	}

	public static function pheonixBackupCronProcessor($siteIDs, $params){
		$type = "cronTask";
		$action = "pheonixBackupCron";
		$requestAction = "runCron";
		$timeout = (180);//20 mins
		$scheduleTime = $params['scheduleTime'];
		if (empty($scheduleTime) && $scheduleTime <=0) {
			$scheduleTime = time()+10;
		}
		$historyAdditionalData = array();
		$requestParams['skipPostData'] = 1;
		$historyAdditionalData[] = array('uniqueName' => 'pheonixBackupCron', 'detailedAction' => $action);
		$doNotShowUser = true;
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID);		
			
			$events=1;
			
			$PRP = array();
			$siteData['connectURL'] = 'siteURL';
			$siteData['URL']		= removeTrailingSlash($siteData['URL']).'/wp-cron.php';
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['parentHistoryID'] = $params['backupParentHID'];
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			$PRP['doNotShowUser'] 	= $doNotShowUser;
			$PRP['status'] 			= 'scheduled';
			$PRP['timeScheduled'] 	= $scheduleTime;
			
			prepareRequestAndAddHistory($PRP);
		}	
	}

	public static function pheonixBackupCronResponseProcessor($historyID, $responseData){
		
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
			return;
		}
		
		if(!empty($responseData['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);
		}
		
		//---------------------------post process------------------------>
		$siteID = DB::getField("?:history", "siteID", $where);
	
	}

	public static function pheonixBackupCronCheck($siteID){
		$where = array(
      		'query' =>  "siteID=':siteID' AND status IN('scheduled', 'running', 'initiated', 'pending') AND action = 'pheonixBackupCron'",
      		'params' => array(
               ':siteID'=>$siteID
				)
		);

		$return = DB::getField("?:history", "historyID", $where);
		if (empty($return)) {
			return false;
		}

		return true;

	}

	public static function checkAndRunPheonixCronDoAction($historyID, $siteID,$params){
		$disabled = getOption('disablePheonixBackupCronDoAction');
		if (!empty($disabled)) {
			return false;
		}
		$isAlreadyScheduled = self::pheonixBackupCronDoActionCheck($siteID);
		if ($isAlreadyScheduled == false) {
			$allParams = array('action' => 'pheonixBackupCronDoAction', 'args' => array('params' => array('mechanism' => 'multiCall','backupParentHID' => $params['backupParentHID'], 'responseData' => $params['responseData']), 'siteIDs' => array($siteID)));
			
			panelRequestManager::handler($allParams);
		}
	}


	public static function pheonixBackupCronDoActionProcessor($siteIDs, $params){
		$type = "cronDoAction";
		$action = "pheonixBackupCronDoAction";
		$requestAction = "cronDoAction";
		$timeout = (180);//20 mins
		
		$historyAdditionalData = array();
		$requestParams = array('mechanism' => 'multiCall','backupParentHID' => $params['backupParentHID'], 'params' => $params['responseData']);
		$historyAdditionalData[] = array('uniqueName' => 'pheonixBackupCron', 'detailedAction' => $action);
		$doNotShowUser = true;
		foreach($siteIDs as $siteID){
			$siteData = getSiteData($siteID);		
			
			$events=1;
			
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['events'] 			= $events;
			$PRP['parentHistoryID'] = $params['backupParentHID'];
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['timeout'] 		= $timeout;
			$PRP['doNotShowUser'] 	= $doNotShowUser;
			
			prepareRequestAndAddHistory($PRP);
		}	
	}

	public static function pheonixBackupCronDoActionResponseProcessor($historyID, $responseData){
		
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
      		'query' =>  "historyID=':historyID'",
      		'params' => array(
               ':historyID'=>$historyID
				)
		);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
			return;
		}
		
		if(!empty($responseData['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);
		}
		
		//---------------------------post process------------------------>
		$siteID = DB::getField("?:history", "siteID", $where);
	
	}

	public static function pheonixBackupCronDoActionCheck($siteID){
		$where = array(
      		'query' =>  "siteID=':siteID' AND status IN('scheduled', 'running', 'initiated', 'pending') AND action = 'pheonixBackupCronDoAction'",
      		'params' => array(
               ':siteID'=>$siteID
				)
		);

		$return = DB::getField("?:history", "historyID", $where);
		if (empty($return)) {
			return false;
		}

		return true;

	}

	
	
}
manageClients::addClass('manageClientsBackup');

?>