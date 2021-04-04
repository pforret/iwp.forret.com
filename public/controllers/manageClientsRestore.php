<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
include_once APP_ROOT.'/controllers/manageClientsInstallCloneCommon.php';
class manageClientsRestore {

	public static function restoreBackupDownlaodProcessor($siteIDs=array(), $params){
		$taskName = $params['taskName'];
		$isNewBackup = false;
		$backupID = $params['resultID'];
		if (empty($params['parentSiteID'])) {
			$siteData = getSiteData($siteIDs[0]);
		}else{
			$siteData = getSiteData($params['parentSiteID']);
		}

		if (!empty($params['isSiteDown']) && empty($siteData['ftpDetails'])) {
			addNotification($type='E', $title='FTP details missing', 'Fill your FTP details by edit site', $state='U', $callbackOnClose='', $callbackReference='');
			return false;
		}
		if (!empty($params['isNewBackup'])) {
			$isNewBackup = $params['isNewBackup'];
		}
		if (empty($params['parentSiteID'])) {
			$backupDetails = panelRequestManager::getBackupsByTaskName($siteIDs[0], $taskName, $isNewBackup, $backupID);
		}else{
			$backupDetails = panelRequestManager::getBackupsByTaskName($params['parentSiteID'], $taskName, $isNewBackup, $backupID);
		}
		$params['backupDetails'] = $backupDetails;
		$job_nonce = dechex($backupID).substr(md5($taskName), 0, 5);
		$params['job_nonce'] = $job_nonce;
		// $params['types_to_downlaod'] = array('plugins','themes','others','more','uploads','db');
		$type = "backup";
		$requestAction = "backup_downlaod";
		$action = "restoreBackupDownlaod";
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'restore', 'detailedAction' => 'restore');
		$events=1;
		if (empty($params['parentSiteID'])) {
			$siteData = getSiteData($siteIDs[0]);
		}else{
			$siteData = getSiteData($params['parentSiteID']);
		}


		$PRP = array();
		// if (!empty($params['backupParentHID'])) {
		// 	$PRP['parentHistoryID'] = $params['backupParentHID'];
		// 	$PRP['doNotShowUser'] 	= true;
		// }
		$URL = trim($siteData['URL'],'/');
		addNotification($typeN='N', $title='Restore Backup', $message='Cloud backup download started<br><a href="'.$URL.'/wp-content/infinitewp/backups/log.'.$job_nonce.'.txt" target="_blank">View log</a>', $state='U', $callbackOnClose='', $callbackReference='');
		$siteData['connectURL'] = 'siteURL';
		$PRP['requestAction'] 	= $requestAction;
		$PRP['siteData'] 		= $siteData;
		$PRP['type'] 			= $type;
		$PRP['action'] 			= $action;
		$PRP['requestParams'] 	= $params;
		$PRP['directExecute'] 	= false;
		$PRP['events'] 			= $events;
		$PRP['timeout'] 			= 1200;
		$PRP['sendAfterAllLoad'] = true;
		$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			
		prepareRequestAndAddHistory($PRP);
	}

	public static function restoreBackupDownlaodResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		$where = array(
		      		'query' =>  "historyID = ':historyID'",
		      		'params' => array(
		      		   ':historyID' => $historyID
       				)
    			);
		$historyData = DB::getRow("?:history", "*", $where);
		$historyRawData = DB::getRow("?:history_raw_details", "panelRequest", $where);
		$panelRequest = unserialize($historyRawData['panelRequest']);
		$params = $panelRequest['args']['params'];
		$job_nonce = dechex($params['resultID']).substr(md5($params['taskName']), 0, 5);
		$siteID = $historyData['siteID'];
		if(isset($responseData['success']) && $responseData['success']['success']=='completed'){
			if($historyData['type'] == 'backup' && $historyData['action'] == 'restoreBackupDownlaod'){
				$historyResponseStatus[$historyID] = "multiCallWaiting";
				Reg::set("historyResponseStatus", $historyResponseStatus);
				
				updateHistory(array('status' => "multiCallWaiting"), $historyID);
				$params['backupParentHID'] = $historyID;
						
				$allParams = array('action' => 'restoreBridgeUpload', 'args' => array('params' => $params, 'siteIDs' => array($siteID)));
				panelRequestManager::handler($allParams);
			}
			return;
		}elseif (isset($responseData['success']) && $responseData['success']['success']=='downloaded') {

			$historyResponseStatus[$historyID] = "multiCallWaiting";
			Reg::set("historyResponseStatus", $historyResponseStatus);
			
			updateHistory(array('status' => "multiCallWaiting"), $historyID);
			$params['backupParentHID'] = $historyID;
			$params['backupDir'] = !empty($responseData['success']['success']['backup_dir'])?$responseData['success']['success']['backup_dir']:false;
			$allParams = array('action' => 'triggerBackupDownlaod', 'args' => array('params' => $params, 'siteIDs' => array($siteID)));
			panelRequestManager::handler($allParams);
			
		}elseif (isset($responseData['success']) && $responseData['success']['success']=='download_failed') {
			DB::update("?:history_additional_data", array('status' => 'error'), $where);
			$where = array(
		      		'query' =>  "historyID=:historyID",
		      		'params' => array(
		               ':historyID'=>$historyID
						)
				);
			$siteData = getSiteData($siteID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => "Download Failed <br> see log <a class='view_on_site' href='".rtrim($siteData['URL'],'/')."/wp-content/infinitewp/backups/log.".$job_nonce.".txt' target='_blank'>View log</a>", 'error' => isset($responseData['success']['error_code']) ? $responseData['success']['error_code'] : $errorCode), $where);
			updateHistory(array('status' => 'completed'), $historyID);
		}
	}

	public static function triggerBackupDownlaodProcessor($siteIDs=array(), $params){
		$taskName = $params['taskName'];
		$isNewBackup = false;
		if (!empty($params['isNewBackup'])) {
			$isNewBackup = $params['isNewBackup'];
		}
		$backupID = $params['resultID'];
		if (empty($params['parentSiteID'])) {
			$backupDetails = panelRequestManager::getBackupsByTaskName($siteIDs[0], $taskName, $isNewBackup, $backupID);
		}else{
			$backupDetails = panelRequestManager::getBackupsByTaskName($params['parentSiteID'], $taskName, $isNewBackup, $backupID);
		}
		$params['backupDetails'] = $backupDetails;
		// $params['types_to_downlaod'] = array('plugins','themes','db','others','more','uploads');
		$type = "backup";
		$requestAction = "backup_downlaod";
		$action = "triggerBackupDownlaod";
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'restore', 'detailedAction' => 'restore');
		$events=1;
		$siteData = getSiteData($siteIDs[0]);

		$PRP = array();
		// if (!empty($params['backupParentHID'])) {
		// }
		$siteData['connectURL'] = 'siteURL';
		$PRP['parentHistoryID'] = $params['backupParentHID'];
		$PRP['doNotShowUser'] 	= true;
		$PRP['requestAction'] 	= $requestAction;
		$PRP['siteData'] 		= $siteData;
		$PRP['type'] 			= $type;
		$PRP['action'] 			= $action;
		$PRP['requestParams'] 	= $params;
		$PRP['directExecute'] 	= false;
		$PRP['events'] 			= $events;
		$PRP['timeout'] 			= 1200;
		$PRP['sendAfterAllLoad'] = true;
		$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			
		prepareRequestAndAddHistory($PRP);
	}

	public static function triggerBackupDownlaodResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		$where = array(
		      		'query' =>  "historyID = ':historyID'",
		      		'params' => array(
		      		   ':historyID' => $historyID
       				)
    			);
		$historyData = DB::getRow("?:history", "*", $where);
		if (!empty($historyData['parentHistoryID'])) {
				$where = array(
				      		'query' =>  "historyID = ':historyID'",
				      		'params' => array(
				      		   ':historyID' => $historyData['parentHistoryID']
	           				)
	        			);
			}
		$historyRawData = DB::getRow("?:history_raw_details", "panelRequest", $where);
		$panelRequest = unserialize($historyRawData['panelRequest']);
		$params = $panelRequest['args']['params'];
		$job_nonce = dechex($params['resultID']).substr(md5($params['taskName']), 0, 5);
		$siteID = $historyData['siteID'];
		if(isset($responseData['success']) && $responseData['success']['success']=='completed'){
			
				// $historyResponseStatus[$historyID] = "multiCallWaiting";
				// Reg::set("historyResponseStatus", $historyResponseStatus);
				$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			      		   ':historyID' => $historyID
	       				)
	    			);
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
				updateHistory(array('status' => "completed"), $historyID);
				$params['backupParentHID'] = $historyData['parentHistoryID'];
						
				$allParams = array('action' => 'restoreBridgeUpload', 'args' => array('params' => $params, 'siteIDs' => array($siteID)));
				panelRequestManager::handler($allParams);
			
			return;
		}elseif (isset($responseData['success']) && $responseData['success']['success']=='downloaded') {

			$where = array(
		      		'query' =>  "historyID = ':historyID'",
		      		'params' => array(
		      		   ':historyID' => $historyID
       				)
    			);
			Reg::set("historyResponseStatus", $historyResponseStatus);
			DB::update("?:history_additional_data", array('status' => 'success'), $where);
			updateHistory(array('status' => "completed"), $historyID);
			$params['backupParentHID'] = $historyData['parentHistoryID'];
			$params['backupDir'] = !empty($responseData['success']['success']['backup_dir'])?$responseData['success']['success']['backup_dir']:false;
					
			$allParams = array('action' => 'triggerBackupDownlaod', 'args' => array('params' => $params, 'siteIDs' => array($siteID)));
			panelRequestManager::handler($allParams);
			
		}elseif (isset($responseData['success']) && $responseData['success']['success']=='download_failed') {
			DB::update("?:history_additional_data", array('status' => 'error'), $where);
			$where = array(
		      		'query' =>  "historyID=:historyID",
		      		'params' => array(
		               ':historyID'=>$historyData['parentHistoryID']
						)
				);
			$siteData = getSiteData($siteID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => "Download Failed <br> see log <a class='view_on_site' href='".rtrim($siteData['URL'],'/')."/wp-content/infinitewp/backups/log.".$job_nonce.".txt' target='_blank'>View log</a>", 'error' => isset($responseData['success']['error_code']) ? $responseData['success']['error_code'] : $errorCode), $where);
			updateHistory(array('status' => 'completed'), $historyData['parentHistoryID']);
		}
	}

	public static function restoreBridgeUploadProcessor($siteIDs=array(), $params){
		$type = "backup";
		$action = 'multiCallRestore';
		$requestAction = "file_editor_upload";
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => $params['taskName'], 'detailedAction' => 'restore');
		$events=1;
		$file = APP_ROOT."/includes/bridge/restore.txt";
		// $fileOrig = substr($file,0,-4);
		$ext = 'php';
		$fileContent = '';
		if(file_exists($file)){
			$fileHandler = fopen($file,'r');
			if (!function_exists('gzdeflate')) {
				addNotification($type='E', $title='Gzip library missing', 'Gzip library functions are not available.', $state='U', $callbackOnClose='', $callbackReference='');
				return false;
	        }else{
				$fileContent = gzdeflate(fread($fileHandler, filesize($file)));
				$fileContentEncode = base64_encode($fileContent);
	        }
			fclose($fileHandler);
		}else{
			addNotification($type='E', $title='Uploaded file corrupt', 'Uploaded file is corrupt/missing.', $state='U', $callbackOnClose='', $callbackReference='');
			return false;
		}
		foreach ($siteIDs as $index => $siteID) {
			$siteData = getSiteData(intval($siteID));
			$requestParams = array('filePath'=>array('clone_controller', 'restore.php'),'folderPath'=>'root','fileContent'=>$fileContentEncode,'ext'=>$ext);
			$PRP = array();
			if (!empty($params['backupParentHID'])) {
				$PRP['parentHistoryID'] = $params['backupParentHID'];
				$PRP['doNotShowUser'] 	= true;
			}
			$siteData['connectURL'] = 'siteURL';
			$PRP['requestAction'] 	= $requestAction;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 			= $action;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['directExecute'] 	= false;
			$PRP['events'] 			= $events;
			$PRP['sendAfterAllLoad'] = true;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			prepareRequestAndAddHistory($PRP);
		}
	}

	public static function restoreBridgeUploadResponseProcessor($historyID, $responseData){
		responseDirectErrorHandlerForRestore($historyID, $responseData);
		$response = array();
		if(isset($responseData['success'])){
			$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			      		   ':historyID' => $historyID
           				)
        			);
			$historyData = DB::getRow("?:history", "*", $where);
			if (!empty($historyData['parentHistoryID'])) {
				$where = array(
				      		'query' =>  "historyID = ':historyID'",
				      		'params' => array(
				      		   ':historyID' => $historyData['parentHistoryID']
	           				)
	        			);
			}
			$historyRawData = DB::getRow("?:history_raw_details", "panelRequest", $where);
			$panelRequest = unserialize($historyRawData['panelRequest']);
			$params = $panelRequest['args']['params'];
			$siteID = $historyData['siteID'];
			if($historyData['type'] == 'backup' && $historyData['action'] == 'multiCallRestore'){
				if (empty($historyData['parentHistoryID'])) {
					$historyResponseStatus[$historyID] = "multiCallWaiting";
					Reg::set("historyResponseStatus", $historyResponseStatus);
					
					updateHistory(array('status' => "multiCallWaiting"), $historyID);
					$params['backupParentHID'] = $historyID;
				}else{
					$where = array(
				      		'query' =>  "historyID = ':historyID'",
				      		'params' => array(
				      		   ':historyID' => $historyID
		       				)
		    			);
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
					$params['backupParentHID'] = $historyData['parentHistoryID'];
					updateHistory(array('status' => "completed"), $historyID);
				}
						
				$allParams = array('action' => 'restoreNewBackup', 'args' => array('params' => $params, 'siteIDs' => array($siteID)));
						
				panelRequestManager::handler($allParams);
			}
			return;
		}
	}

	public static function restoreNewBackupProcessor($siteIDs, $params){
		$taskName = $params['taskName'];
		$backupID = $params['resultID'];
		$isNewBackup = false;
		$siteData = getSiteData($siteIDs[0]);
		if (!empty($params['isSiteDown']) && empty($siteData['ftpDetails'])) {
			addNotification($type='E', $title='FTP details missing', 'Fill your FTP details by edit site', $state='U', $callbackOnClose='', $callbackReference='');
			return false;
		}
		if (!empty($siteData['ftpDetails'])) {
			$params['param1'] = base64_encode($siteData['ftpDetails']);
		}
		if (!empty($params['isNewBackup'])) {
			$isNewBackup = $params['isNewBackup'];
		}
		if (empty($params['parentSiteID'])) {
			$siteID = $siteIDs[0];
			$backupDetails = panelRequestManager::getBackupsByTaskName($siteIDs[0], $taskName, $isNewBackup, $backupID);
		}else{
			$siteID = $params['parentSiteID'];
			$backupDetails = panelRequestManager::getBackupsByTaskName($params['parentSiteID'], $taskName, $isNewBackup, $backupID);
		}
		if (!empty($params['isNewBackup'])) {
			if (empty($params['parentSiteID'])) {
				$backupFiles = panelRequestManager::getBackupFilesByTaskName($siteIDs[0], $taskName, $isNewBackup, $backupID);
			}else{
				$backupFiles = panelRequestManager::getBackupFilesByTaskName($params['parentSiteID'], $taskName, $isNewBackup, $backupID);
			}
			$wpContentPath = $backupDetails['wp_content_path'];
			$wpContentURL = $backupDetails['wp_content_url'];
			$service = $backupDetails['service'][0];
			$params['backupFiles'] = $backupFiles;
			$params['wp_content_url'] = $wpContentURL;
			$params['wp_content_path'] = $wpContentPath;
		}else{
			if (!empty($backupDetails['server'])) {
				$params['backupFiles'] = $backupDetails['server']['file_path'];
			}elseif (!empty($backupDetails['dropbox'])) {
				$params['backupFiles'] = $backupDetails['dropbox'];
			}elseif (!empty($backupDetails['gDriveOrgFileName'])){
				$params['backupFiles'] = $backupDetails['gDriveOrgFileName'];
			}elseif (!empty($backupDetails['amazons3'])){
				$params['backupFiles'] = $backupDetails['amazons3'];
			}elseif (!empty($backupDetails['ftp'])){
				$params['backupFiles'] = $backupDetails['ftp'];
			}
			// $params['wp_content_path'] = $params['backupDir'];
		}
		if (!empty($params['backupParentHID'])) {
			$where = array(
				'query' =>   "historyID=':historyID'",
					'params' => array(
						':historyID' => $params['backupParentHID'],
					)
				);
			$actionID = DB::getField('?:history', 'actionID', $where);
		}else{
			$actionID = Reg::get('currentRequest.actionID');
		}

		$databaseDetails = manageClientsFetch::loadGetDBDetails($actionID, $siteID);
		if (!empty($databaseDetails['dbHost'])) {
			$params['dbHost'] = $databaseDetails['dbHost'];
	    	$params['dbName'] = $databaseDetails['dbName'];
	    	$params['dbUser'] = $databaseDetails['dbUser'];
	    	$params['dbPassword'] = $databaseDetails['dbPassword'];
	    	$params['db_table_prefix'] = $databaseDetails['db_table_prefix'];
	    	if(!empty($databaseDetails['site_url'])){
		    	$params['site_url'] = $databaseDetails['site_url'];
	    	}
		}

		$type = "backup";
		$action = "restoreNew";
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'restore', 'detailedAction' => 'restore');
		$events=1;
		$params['cloneCommunication'] = 1;
		if (!empty($siteData['adminUsername'])) {
			$params['old_user'] = $siteData['adminUsername'];
		}
		$params['toIWP'] = 1;  // restore backup after re-connect the site client plugin connection error happen missmatch activation key
		
		$siteData['connectURL'] = 'siteURL';

		$URL = trim($siteData['URL'],'/');
		
		addNotification($typeN='N', $title='Restore Backup', $message='Download completed. Backup extraction started<br><a href="'.$URL.'/iwp-restore-log.txt" target="_blank">View log</a>', $state='U', $callbackOnClose='', $callbackReference='');

		$requestURL = getSiteURLFromStatsBySiteID($siteIDs[0]);
		if(!empty($params['site_url'])){
			$requestURL = $params['site_url']; //implemented in iwp-client plugin >= 1.9.4.7
		}else{
			if(empty($requestURL)){
				$requestURL  = $siteData['URL'];
			}
		}

		$siteData['URL'] = trim($requestURL, '/').'/clone_controller/restore.php';

		$PRP = array();
		if (!empty($params['backupParentHID'])) {
			$PRP['parentHistoryID'] = $params['backupParentHID'];
			$params['extractParentHID'] = $params['backupParentHID'];
			$PRP['doNotShowUser'] 	= true;
		}
		$PRP['requestAction'] 	= $action;
		$PRP['requestParams'] 	= $params;
		$PRP['siteData'] 		= $siteData;
		$PRP['signature'] 		= false;
		$PRP['type'] 			= $type;
		$PRP['action'] 			= $action;
		$PRP['events'] 			= $events;
		$PRP['historyAdditionalData'] 	= $historyAdditionalData;
		// $PRP['timeout'] 		= $timeout;
		// $PRP['doNotShowUser'] 	= true;
		// $PRP['parentHistoryID'] = $params['backupParentHID'];
		$PRP['isPluginResponse'] = 0;
			
		prepareRequestAndAddHistory($PRP);

	}

	public static function restoreNewBackupResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		$start = '#Status(';
		$end = ')#';
		
		$strBetArray = getStrBetAll($responseData,$start,$end);
		$statusData = unserializeBase64DecodeArray($strBetArray);
		$finalStateReached = false;
		$responseDataReadable = false;
		$setBreak = false;
		
		$fullHistoryData = getHistory($historyID, true);
		
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);

		foreach($statusData as $d1){
			$responseDataReadable = true;
			if($setBreak){
				break;
			}
			foreach($d1 as $d2 => $d3){
				if($d2 == "error"){
					$finalStateReached = true;
					if(stripos($d3, "test-connection") !== false){
						$historyData = DB::getRow("?:history", "type, actionID, siteID", $where);
						$type = $historyData['type'];
						$actionID = $historyData['actionID'];
						DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('error' => $d3))));
					}
					DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => $d3), $where);
					$updateWhere = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $fullHistoryData['parentHistoryID'],
							)
						);
					DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => $d3), $updateWhere);
					
					DB::update("?:history", array('status' => 'error'), $updateWhere);
				} else if($d2 == "success" && $d3 == "multicall"){
					$finalStateReached = true;
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
					DB::update("?:history", array('status' => 'completed'), $where);
					
					$multiCallResponse = array();
					$multiCallResponse = $d1['options'];
					$multiCallResponse['parentHistoryID'] = (!empty($d1['options']['extractParentHID'])) ? $d1['options']['extractParentHID'] : $historyID;
					self::triggerBridgeExtractRestoreMulticall($multiCallResponse, 0);
					$setBreak = true;
					break;
				} else if($d2 == "success" && (stripos($d3, "test-connection") !== false)){
				
					$historyData = DB::getRow("?:history", "type, actionID, siteID", "historyID=".$historyID);
					$type = $historyData['type'];
					$actionID = $historyData['actionID'];
					$siteID = $historyData['siteID'];
					
					$finalStateReached = true;
					DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('success' => $d3))));
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
					
					$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
					
					panelRequestManager::handler($allParams);
				} else if($d2 == "success" && (strpos($d3, "clone_completed") !== false)){
					$finalStateReached = true;
					DB::update("?:history_additional_data", array('status' => 'success'), $where);

					$updateWhere = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $fullHistoryData['parentHistoryID'],
							)
					
						);
					$type = DB::getField("?:history","type", $where);
					DB::update("?:history_additional_data", array('status' => 'success'), $updateWhere);
					
					DB::update("?:history", array('status' => 'completed'), $updateWhere);
				} else if($d2 == "options" && (!empty($d3))){
					$historyData = DB::getRow("?:history", "type, actionID, siteID, parentHistoryID", $where);
					$where = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $historyData['parentHistoryID'],
							)
						);
					$type = DB::getField("?:history","type", $where);
					$d3['isStaging'] = ($type == "staging") ? true : false;
		  			if( $type == 'staging'){
						manageClientsInstallCloneCommon::triggerAddSite($historyData['parentHistoryID'], $d3);
					}else{
						manageClientsInstallCloneCommon::triggerAddSite('', $d3);

					}
					return;
				}	
			}
		}
		
		if($responseDataReadable === true && $finalStateReached === false){
			DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
		}
	}

	public static function triggerBridgeExtractRestoreMulticall($data, $siteID){
		$allParams = array('action' => 'bridgeExtractMulticallRestore', 'args' => array('params' => array('responseData' => $data, 'extractParentHID' => $data['parentHistoryID']), 'siteIDs' => array($siteID)));
		panelRequestManager::handler($allParams);
		
	}
	
	public static function bridgeExtractMulticallRestoreProcessor($siteIDs, $params, $extras){
		$type = "backup";
		$action = "bridgeExtractMulticallRestore";
		$requestAction = "bridgeExtractMulticallRestore";
		if ($param['responseData']['is_file_append']) {
			$timeout = 300;// File appending process happing in single call so timeout increased. This need to come in multical 
		}
		$timeout = 180;
		
		if(empty($params['extractParentHID'])){
			return;	
		}
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $params['extractParentHID'],
				)
			);
		$parentHistoryIDStatus = DB::getField("?:history", "status", $where);
	
		if(($parentHistoryIDStatus != 'multiCallWaiting')){
			return;
		}
		
		$getCount = DB::getField("?:history", "count(historyID)", "type='backup' AND action = 'bridgeExtractMulticall' AND parentHistoryID = '".$params['extractParentHID']."'" );
		if($getCount >= 500){
			updateHistory(array('status' => 'error', 'error' => 'max_trigger_calls_reached'), $params['extractParentHID'], array('status' => 'error', 'error' => 'max_trigger_calls_reached', 'errorMsg' => 'Multi-call limit reached.'));
			return;
		}
		
		if(DB::getExists("?:history", "historyID", "type='backup' AND action = 'bridgeExtractMulticall' AND parentHistoryID = '".$params['extractParentHID']."' AND status not IN('completed', 'error', 'netError')")){
			return;
			
		}
		
		$oldHistoryData = getHistory($params['extractParentHID'], true);
		
		$requestParams = array('mechanism' => 'multiCall','extractParentHID' => $params['extractParentHID'], 'responseData' => $params['responseData'], 'param1' => $oldHistoryData['param1']);
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => "bridgeExtractTrigger", 'detailedAction' => $action);
		
		$doNotShowUser = true;
		$siteData = getSiteData($oldHistoryData['siteID']);	
		$siteData['connectURL'] = 'siteURL';
		$siteData['URL'] = trim($oldHistoryData['URL'], '/').'/clone_controller/restore.php';
	
		$requestParams['cloneCommunication'] = 1;		  		
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
		$PRP['doNotShowUser'] 	= $doNotShowUser;
		$PRP['parentHistoryID'] = $params['extractParentHID'];
		$PRP['isPluginResponse'] = 0;
			
		prepareRequestAndAddHistory($PRP);

	}
	
	public static function bridgeExtractMulticallRestoreResponseProcessor($historyID, $responseData){
		
		$start = '#Status(';
		$end = ')#';
		
		$strBetArray = getStrBetAll($responseData,$start,$end);
		$statusData = unserializeBase64DecodeArray($strBetArray);
		$finalStateReached = false;
		$responseDataReadable = false;
		$setBreak = false;
		
		$fullHistoryData = getHistory($historyID, true);
		
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);

		foreach($statusData as $d1){
			$responseDataReadable = true;
			if($setBreak){
				break;
			}
			foreach($d1 as $d2 => $d3){
				if($d2 == "error"){
					$finalStateReached = true;
					if(stripos($d3, "test-connection") !== false){
						$historyData = DB::getRow("?:history", "type, actionID, siteID", $where);
						$type = $historyData['type'];
						$actionID = $historyData['actionID'];
						DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('error' => $d3))));
					}
					DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => $d3), $where);
					$updateWhere = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $fullHistoryData['parentHistoryID'],
							)
						);
					DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => $d3), $updateWhere);
					
					DB::update("?:history", array('status' => 'error'), $updateWhere);
				} else if($d2 == "success" && $d3 == "multicall"){
					$finalStateReached = true;
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
					DB::update("?:history", array('status' => 'completed'), $where);
					
					$multiCallResponse = array();
					$multiCallResponse = $d1['options'];
					$multiCallResponse['parentHistoryID'] = (!empty($d1['options']['extractParentHID'])) ? $d1['options']['extractParentHID'] : $historyID;
					self::triggerBridgeExtractRestoreMulticall($multiCallResponse, 0);
					$setBreak = true;
					break;
				} else if($d2 == "success" && (stripos($d3, "test-connection") !== false)){
				
					$historyData = DB::getRow("?:history", "type, actionID, siteID", "historyID=".$historyID);
					$type = $historyData['type'];
					$actionID = $historyData['actionID'];
					$siteID = $historyData['siteID'];
					
					$finalStateReached = true;
					DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('success' => $d3))));
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
					
					$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false, 'doNotShowUser' => true)));
					
					panelRequestManager::handler($allParams);
				} else if($d2 == "success" && (strpos($d3, "clone_completed") !== false)){
					$finalStateReached = true;
					DB::update("?:history_additional_data", array('status' => 'success'), $where);

					$updateWhere = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $fullHistoryData['parentHistoryID'],
							)
					
						);
					$type = DB::getField("?:history","type", $where);
					DB::update("?:history_additional_data", array('status' => 'success'), $updateWhere);
					
					DB::update("?:history", array('status' => 'completed'), $updateWhere);
				} else if($d2 == "options" && (!empty($d3))){
					$historyData = DB::getRow("?:history", "type, actionID, siteID, parentHistoryID", $where);
					$where = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $historyData['parentHistoryID'],
							)
						);
					$type = DB::getField("?:history","type", $where);
					$d3['isStaging'] = ($type == "staging") ? true : false;
		  			if( $type == 'staging'){
						manageClientsInstallCloneCommon::triggerAddSite($historyData['parentHistoryID'], $d3);
					}else{
						manageClientsInstallCloneCommon::triggerAddSite('', $d3);

					}
					return;
				}	
			}
		}
		
		if($responseDataReadable === true && $finalStateReached === false){
			DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
		}
	}

	public static function restoreNewBackupPreProcessor($historyID){

		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
		$parentHistoryRequest = DB::getField("?:history_raw_details", "request", $where);
		$parentHistoryRequest = unserialize(base64_decode($parentHistoryRequest));
		if (empty($parentHistoryRequest['params']['isSiteDown'])) {
			DB::update("?:history", array('status' => 'pending'), $where);
			return true;
		}
		$siteID = DB::getField("?:history", "siteID", $where);		

		$where = array(
			'query' =>   "siteID=':siteID'",
				'params' => array(
					':siteID' => $siteID,
				)
			);
		$compactVars = DB::getField("?:sites", "ftpDetails", $where);
		$updateWhere = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
		if (empty($compactVars)) {
			DB::update("?:history", array('status' => 'pending'), $updateWhere);
			return true;
		}		
		$compactVars = unserialize($compactVars);
		$params = $compactVars;		
		$hostName = trim($params['hostName']);
		$hostUserName = trim($params['hostUserName']); 
		$hostPassword = trim($params['hostPassword']);
		$hostPort = trim($params['hostPort']) ? trim($params['hostPort']) : 22; //trim($params['hostPort']); $port = $ftp_port ? $ftp_port : 22;
		$hostSSL = trim($params['hostSSL']);
		$hostPassive = trim($params['hostPassive']);
        $use_sftp = trim($params['use_sftp']);
		$parts = parse_url($params['newSiteURL']);
		
		if(isset($use_sftp) && $use_sftp==1) {
			$conResult = self::initIWPSftpConn($historyID, $params, $sftp);
			if(!$conResult){
				return false;
			}
			
			$return = self::createSftpCloneDirectory($cloneTempPath, $params, $conResult, $historyID);
			if(!$return){
				return false;
			}
			
		} else {

			$conResult = self::initIWPFtpConn($historyID, $params, $connection);
			if(!$connection || !$conResult){
				return false; //array('error' => 'Error creating the directory'); 
			}
				
			$uploadPath = '/'.trim($params['remoteFolder'], '/').'/clone_controller';
			$cloneTempPath = $uploadPath.'/clone_temp';
			
			$return = self::createCloneDirectory($cloneTempPath, $connection, $historyID, $params);
			if(!$return){
				return false;
			}
				
			$file_bridge_source		= "/restore.txt";
			$file_bridge_des 		= "/restore.php";
			$bridge_path = self::getCloneBridgePath();
			$uploadBridge 	= @ftp_put($connection, $uploadPath.$file_bridge_des, $bridge_path.$file_bridge_source, FTP_ASCII);
			
			
			if (!$uploadBridge) {
				updateHistory(array('status' => 'error'), $historyID);
				DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'FTP upload failed.'), $updateWhere);
				self::installCloneTestLog('FTP upload failed.', $historyID, $params['isTestConnection']);
				return false; //array('error' => 'FTP upload failed!'); 
			}

			@ftp_close($connection);
        }
		DB::update("?:history", array('param1' => base64_encode(serialize($params)), 'status' => 'pending'), $updateWhere);
		return true;
	}

	public static function createCloneDirectory($cloneTempPath, &$connection, $historyID, $params){
		$parts = explode("/", $cloneTempPath);
		$countParts = count($parts);
		
		$return = true;
		$fullpath = "";						
		$i = 0;
		foreach($parts as $part){
			$i++;
			if(empty($part)){
				$fullpath .= "/";
				continue;
			}
			$fullpath .= $part."/";
			if(@ftp_chdir($connection, $fullpath)){
				ftp_chdir($connection, $fullpath);
			} else{
				if(@ftp_mkdir($connection, $part)){
					ftp_chdir($connection, $part);						
				} else{
					$return = false;
				}
			}
		}
		if($return == false){
			updateHistory(array('status' => 'error'), $historyID);
			$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Unable to create a directory using the FTP credentials.'), $where);
			self::installCloneTestLog('Unable to create a directory using the FTP credentials.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Error creating the directory'); 
		}
		return $return;
	}

	public static function initIWPFtpConn($historyID, $params, &$connection){
		$hostName = trim($params['hostName']);
		$hostUserName = trim($params['hostUserName']); 
		$hostPassword = trim($params['hostPassword']);
		$hostPort = trim($params['hostPort']) ? trim($params['hostPort']) : 22; //trim($params['hostPort']); $port = $ftp_port ? $ftp_port : 22;
		$hostSSL = trim($params['hostSSL']);
		$hostPassive = trim($params['hostPassive']);
		$use_sftp = trim($params['use_sftp']);
		$parts = parse_url($params['newSiteURL']);
		
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);

		if(!empty($hostSSL) && function_exists('ftp_ssl_connect')){
			$connection = @ftp_ssl_connect($hostName, $hostPort);
		} else{
			$connection = @ftp_connect($hostName, $hostPort);
		}
		if (!$connection){
			updateHistory(array('status' => 'error'), $historyID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Connection to the Host failed. Check your Hostname.'), $where);
			self::installCloneTestLog('Connection to the Host failed. Check your Hostname.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Connect to the Host failed, Check your hostName');
		}		
		
		$login = @ftp_login($connection, $hostUserName, $hostPassword);
		if (!$login) {
			updateHistory(array('status' => 'error'), $historyID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Could not login to FTP. Please check the credentials.'), $where);
			self::installCloneTestLog('Could not login to FTP. Please check the credentials.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Connection attempt failed!');
		}		
	
		if(!empty($hostPassive)){
			@ftp_pasv($connection, true);
		}
		return true;
	}

	public static function initIWPSftpConn($historyID ,$params){
		$hostName = trim($params['hostName']);
		$hostUserName = trim($params['hostUserName']); 
		$hostPassword = trim($params['hostPassword']);
		$hostPort = trim($params['hostPort']) ? trim($params['hostPort']) : 22; //trim($params['hostPort']); $port = $ftp_port ? $ftp_port : 22;
		$hostSSL = trim($params['hostSSL']);
		$hostPassive = trim($params['hostPassive']);
		$use_sftp = trim($params['use_sftp']);
		$parts = parse_url($params['newSiteURL']);
		
		include APP_ROOT.'/lib/phpseclib/vendor/autoload.php';
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
		$sftp = new  \phpseclib\Net\SFTP($hostName, $hostPort);
		if(!$sftp) {
			updateHistory(array('status' => 'error'), $historyID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Connection to the SFTP Host failed. Check your Hostname.'), $where);
			self::installCloneTestLog('Connection to the SFTP Host failed. Check your Hostname.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Connect to the Host failed, Check your hostName');
		}
		
		if (!$sftp->login($hostUserName, $hostPassword)) {
			updateHistory(array('status' => 'error'), $historyID);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Could not login to SFTP. Please check the credentials.'), $where);
			self::installCloneTestLog('Could not login to SFTP. Please check the credentials.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Connect to the Host failed, Check your hostName');
		}
		return $sftp;
	}
	public static function installCloneTestLog($errMsg, $historyID, $isTestConnection){
		if($isTestConnection == 1)
		{
			$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
			$historyData = DB::getRow("?:history", "type, actionID, siteID", $where);
			$type = $historyData['type'];
			$actionID = $historyData['actionID'];
			$siteID = $historyData['siteID'];
					
			DB::insert("?:temp_storage", array('type' => 'getICTestConnection', 'paramID' => $actionID, 'time' => time(), 'data' =>  serialize(array('error' => $errMsg))));
		}
	}

	public static function createSftpCloneDirectory($cloneTempPath, $params, &$sftp, $historyID){
		$uploadPath = '/'.trim($params['remoteFolder'], '/').'/clone_controller';
		$cloneTempPath = $uploadPath.'/clone_temp';

		$file_bridge_source 	= "restore.txt";  
		$file_bridge_des 		= "restore.php";  
		
		$sftp->mkdir($uploadPath,-1,true);
		$sftp->chmod(0777, $uploadPath);

		$sftp->mkdir($uploadPath,-1,true);
		$sftp->chmod(0777, $uploadPath);
		
		$sftp->mkdir($cloneTempPath,07777,true);
		$sftp->chmod(0777, $cloneTempPath);
		$sftp->chdir($uploadPath);
		
		
		$bridge_path = self::getCloneBridgePath();
		
		$uploadSftpResult = true;
		$uploadSftpResult = @$sftp->put(basename($file_bridge_des), $bridge_path.$file_bridge_source,1);
		
		if((!$uploadSftpResult) && ($params['isTestConnection'] == 1))
		{
			self::installCloneTestLog('SFTP Upload failed.', $historyID, $params['isTestConnection']);
			return false; //array('error' => 'Connect to the Host failed, Check your hostName');
		}
		
		/*
		 * PHP Lib Upload Start here
		 */

		$sftp->mkdir($uploadPath.'/phpseclib/vendor/composer',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/ASN1/',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Math',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System/SSH',-1,true);
		$sftp->mkdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System/SSH/Agent',-1,true);
		
		$sftp->chdir($uploadPath.'/phpseclib/vendor');
		@$sftp->put('autoload.php', APP_ROOT."/lib/phpseclib/vendor/autoload.php",1);

		$sftp->chdir($uploadPath.'/phpseclib/vendor/composer');
		@$sftp->put('autoload_classmap.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_classmap.php",1);
		@$sftp->put('autoload_files.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_files.php",1);
		@$sftp->put('autoload_namespaces.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_namespaces.php",1);
		@$sftp->put('autoload_psr4.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_psr4.php",1);
		@$sftp->put('autoload_real.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_real.php",1);
		@$sftp->put('autoload_static.php', APP_ROOT."/lib/phpseclib/vendor/composer/autoload_static.php",1);
		@$sftp->put('ClassLoader.php', APP_ROOT."/lib/phpseclib/vendor/composer/ClassLoader.php",1);

		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt');
		@$sftp->put('AES.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/AES.php",1);
		@$sftp->put('Base.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Base.php",1);
		@$sftp->put('Blowfish.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Blowfish.php",1);
		@$sftp->put('DES.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/DES.php",1);
		@$sftp->put('Hash.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Hash.php",1);
		@$sftp->put('Random.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Random.php",1);
		@$sftp->put('RC2.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/RC2.php",1);
		@$sftp->put('RC4.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/RC4.php",1);
		@$sftp->put('Rijndael.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Rijndael.php",1);
		@$sftp->put('RSA.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/RSA.php",1);
		@$sftp->put('TripleDES.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/TripleDES.php",1);
		@$sftp->put('Twofish.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Crypt/Twofish.php",1);
		
		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File');
		@$sftp->put('ANSI.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/ANSI.php",1);
		@$sftp->put('ASN1.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/ASN1.php",1);
		@$sftp->put('X509.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/X509.php",1);

		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/ASN1');
		@$sftp->put('Element.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/File/ASN1/Element.php",1);

		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Math');
		@$sftp->put('BigInteger.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Math/BigInteger.php",1);
		
		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net');
		@$sftp->put('SCP.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SCP.php",1);
		@$sftp->put('SFTP.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php",1);
		@$sftp->put('SSH1.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SSH1.php",1);
		@$sftp->put('SSH2.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SSH2.php",1);
		
		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP');
		@$sftp->put('Stream.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP/Stream.php",1);

		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System/SSH');
		@$sftp->put('Agent.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System/SSH/Agent.php",1);


		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System/SSH/Agent');
		@$sftp->put('Identity.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/System//SSH/Agent/Identity.php",1);
		
		$sftp->chdir($uploadPath.'/phpseclib/vendor/phpseclib/phpseclib/phpseclib');
		@$sftp->put('openssl.cnf', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/openssl.cnf",1);
		@$sftp->put('bootstrap.php', APP_ROOT."/lib/phpseclib/vendor/phpseclib/phpseclib/phpseclib/bootstrap.php",1);
		
		$sftp->chdir($uploadPath);
		return true;
	}

	public static function getCloneBridgePath(){
		return APP_ROOT.'/includes/bridge/';
	}
}

manageClients::addClass('manageClientsRestore');