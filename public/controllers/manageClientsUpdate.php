<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

class manageClientsUpdate {
	
	public static function updateAllProcessor($siteIDs, $allParams){
		

		if(empty($allParams)) return false;

		$updateInStaging = 0;
		if ( isset($allParams['type']) && $allParams['type'] == 'staging') {
			if (function_exists("stagingUpdateInExistingStaging")) {
				$result = stagingUpdateInExistingStaging($allParams);
				$stagingParams = $result['stagingSite'];
				$allParams = $result['mainSite'];
				$stagingSiteID = $result['stagingSiteID'];
				$updateInStaging = 1;
			}
		}
		$requestAction = 'do_upgrade';
		$type = 'PTC';
		$action = 'update';
		$sitesStats = DB::getFields("?:site_stats", "stats, siteID", "siteID IN (".implode(',', array_keys(DB::esc($allParams))).")", "siteID");
		
		foreach($sitesStats as $siteID => $sitesStat){
			$sitesStats[$siteID] = unserialize(base64_decode($sitesStat));
		}
		
		if ($updateInStaging == 1) {
			$sitesData = getSitesData(array_keys($stagingParams));
		} else {
			$sitesData = getSitesData(array_keys($allParams));
		}
		
		foreach($allParams as $siteID => $siteParams){
			$lastHistoryID = '';
			$siteIDs = array($siteID);
			$events = 0;
			$requestParams = $historyAdditionalData = array();
			$timeout = DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT;
			
			//for staging
			$parentHistoryID = $siteParams['parentHistoryID'];
			if(!empty($siteParams['parentActionID'])){
				$parentActionID = $siteParams['parentActionID'];
			}
			foreach($siteParams as $PTC => $PTCParams){				
				
				if($PTC == 'plugins'){
					
					if(!empty($sitesStats[$siteID]['premium_updates']))
					{
						foreach($sitesStats[$siteID]['premium_updates'] as $item){						
							if(in_array($item['slug'], $PTCParams)){
								$uniqueName = $item['Name'];
								$requestParams['upgrade_plugins'][] = array_change_key_case($item, CASE_LOWER);
								$historyAdditionalData[] = array('uniqueName' => $uniqueName, 'detailedAction' => 'plugin');
								$timeout += 20;
								$events++;
							}
						}
					}
					
					if(!empty($sitesStats[$siteID]['upgradable_plugins']))
					{
						foreach($sitesStats[$siteID]['upgradable_plugins'] as $item){
							$item =  objectToArray($item);
							$filePath = $item['file'];
							if(in_array($filePath, $PTCParams)){
								 $uniqueName = $filePath ;
								 $requestParams['upgrade_plugins'][] = $item;
								 $timeout += 20;
								 $events++;
							}
						}
					}
				}
				
				elseif($PTC == 'themes'){
					foreach($sitesStats[$siteID]['upgradable_themes'] as $item){
						if(in_array($item['theme_tmp'], $PTCParams) || in_array($item['name'], $PTCParams)){
							$requestParams['upgrade_themes'][] = $item;
							$uniqueName = $item['theme_tmp'] ? $item['theme_tmp'] : $item['name'];
							$timeout += 20;
							$events++;
						}
					}
				}
				elseif($PTC == 'core'){
					$item =  objectToArray($sitesStats[$siteID]['core_updates']);
					$currentVersion = $item['current'];
					if($currentVersion == $PTCParams){
						$requestParams['wp_upgrade'] = $sitesStats[$siteID]['core_updates'];
						$timeout += 120;
						$events++;
					}
				}
				elseif($PTC == 'translations'){
					if ($sitesStats[$siteID]['upgradable_translations']) {
						$requestParams['upgrade_translations'] = true;
						$timeout += 60;
						$events++;
					}
				}
			}

			foreach ($requestParams as $updateKey => $updateValue) {
				$singleRequestParams = array();
				if ($updateInStaging == 1) {
					$siteData = $sitesData[$stagingSiteID];
				} else {
					$siteData = $sitesData[$siteID];
				}
				if ($updateKey == 'wp_upgrade' || $updateKey == 'upgrade_translations') {
					$singleRequestParams[$updateKey] = $updateValue; 
					$singleRequestParams['bulkActionParams'] = $requestParams;
					if($updateKey == 'wp_upgrade'){
						$historyAdditionalData[0] = array('uniqueName' => 'core', 'detailedAction' => 'core');
					}elseif($updateKey == 'upgrade_translations'){
						$historyAdditionalData[0] = array('uniqueName' => 'translations', 'detailedAction' => 'translations');
					}
					$where = array(
					      		'query' =>  "siteID = ':siteID'",
					      		'params' => array(
					               ':siteID'=>$siteID
		           				)
		        			);
					$ftpDetails = DB::getField('?:sites', 'ftpDetails', $where);
					if (!empty($ftpDetails)) {
						$singleRequestParams['secure']['account_info'] = $ftpDetails;
					}
					$PRP = array();
					$PRP['requestAction'] 	= $requestAction;
					$PRP['requestParams'] 	= $singleRequestParams;
					$PRP['siteData'] 		= $siteData;
					$PRP['type'] 			= $type;
					$PRP['action'] 			= $action;
					$PRP['events'] 			= $events;
					$PRP['historyAdditionalData'] 	= $historyAdditionalData;
					$PRP['timeout'] 		= $timeout;
					$PRP['doNotExecute'] 			= false;
					$PRP['sendAfterAllLoad'] = true;
					$PRP['parentHistoryID'] = $parentHistoryID;
					if (!empty($parentActionID)) {
						$PRP['actionID'] = $parentActionID;
					}
					if($lastHistoryID){
						self::prepareRunCondition($PRP, $lastHistoryID);
					}else{
						$lastWaitingHisID = self::getLastWaitingHistoryIDBySiteID($siteData['siteID']);
						if ($lastWaitingHisID !== false) {
							self::prepareRunCondition($PRP, $lastWaitingHisID, false);
						}
					}
					
					$lastHistoryID = prepareRequestAndAddHistory($PRP);
				}else{
					foreach ($updateValue as $itemKey => $itemValue) {
						$singleRequestParams = array();
						$singleRequestParams[$updateKey][0] = $itemValue; 
						$singleRequestParams['bulkActionParams'] = $requestParams;
						if($updateKey == 'upgrade_plugins'){
							$item =  objectToArray($itemValue);
							$uniqueName = $item['file'];
							$historyAdditionalData[0] = array('uniqueName' => $uniqueName, 'detailedAction' => 'plugin');
						}elseif($updateKey == 'upgrade_themes'){
							$uniqueName = $itemValue['theme_tmp'] ? $itemValue['theme_tmp'] : $itemValue['name'];
							$historyAdditionalData[0] = array('uniqueName' => $uniqueName, 'detailedAction' => 'theme');
						}
						$where = array(
						      		'query' =>  "siteID = ':siteID'",
						      		'params' => array(
						               ':siteID'=>$siteID
			           				)
			        			);
						$ftpDetails = DB::getField('?:sites', 'ftpDetails', $where);
						if (!empty($ftpDetails)) {
							$singleRequestParams['secure']['account_info'] = $ftpDetails;
						}
						$PRP = array();
						$PRP['requestAction'] 	= $requestAction;
						$PRP['requestParams'] 	= $singleRequestParams;
						$PRP['siteData'] 		= $siteData;
						$PRP['type'] 			= $type;
						$PRP['action'] 			= $action;
						$PRP['events'] 			= $events;
						$PRP['historyAdditionalData'] 	= $historyAdditionalData;
						$PRP['timeout'] 		= $timeout;
						$PRP['doNotExecute'] 			= false;
						$PRP['sendAfterAllLoad'] = true;
						$PRP['parentHistoryID'] = $parentHistoryID;
						if (!empty($parentActionID)) {
							$PRP['actionID'] = $parentActionID;
						}
						if($lastHistoryID){
							self::prepareRunCondition($PRP, $lastHistoryID);
						}else{
							$lastWaitingHisID = self::getLastWaitingHistoryIDBySiteID($siteData['siteID']);
							if ($lastWaitingHisID !== false) {
								self::prepareRunCondition($PRP, $lastWaitingHisID, false);
							}
					}
						
						$lastHistoryID = prepareRequestAndAddHistory($PRP, false);
					}
				}
			}
		}	
	}

	public static function updateAllPreProcessor($historyID){
		$isRunning = self::isUpdateRunningAditionalCheck($historyID);
		if ($isRunning) {
			updateHistory(array('status' => 'scheduled'), $historyID);
		}else{
			updateHistory(array('status' => 'pending'), $historyID);
		}
	}
	
	public static function updateAllResponseProcessor($historyID, $responseData){
		$isTranslationUpdate = false;
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		if (!empty($responseData['success']['success_code']) && $responseData['success']['success_code'] == 'WPTC_TAKES_CARE_OF_IT_LATEST') {
			$historyData = getHistory($historyID);
			$where = array(
			      		'query' =>  "actionID = ':actionID' AND siteID =':siteID'",
			      		'params' => array(
			               ":actionID"=>$historyData['actionID'],
			               ":siteID" => $historyData['siteID']
           				)
        			);
			$perHistoryIDs = DB::getFields("?:history", 'historyID', $where);
			DB::update("?:history", array('status' => 'completed'), $where);
			$keyword = "'".implode("','", $perHistoryIDs)."'" ;
			$where = "historyID IN (".$keyword.") ";
			DB::update("?:history_additional_data", array('status' => 'success', 'successMsg' => $responseData['success']['success'],'error'=>$responseData['success']['success_code']), $where);
		}
		if (!empty($responseData['success']['success_code']) && $responseData['success']['success_code'] == 'WPTC_TAKES_CARE_OF_IT') {
			$historyData = getHistory($historyID);
			$where = array(
			      		'query' =>  "actionID = ':actionID' AND siteID =':siteID'",
			      		'params' => array(
			               ":actionID"=>$historyData['actionID'],
			               ":siteID" => $historyData['siteID']
           				)
        			);
			$perHistoryIDs = DB::getFields("?:history", 'historyID', $where);
			DB::update("?:history", array('status' => 'error'), $where);
			$keyword = "'".implode("','", $perHistoryIDs)."'" ;
			$where = "historyID IN (".$keyword.") ";
			DB::update("?:history_additional_data", array('status' => 'error', 'successMsg' => "Please update your WPTC plugin to 1.16.0 or greater to support backup before update feature"), $where);
		}
		if(!empty($responseData['success']['error']) || !empty($responseData['success']['failed'])){
			
			$errorMsg = !empty($responseData['success']['error']) ? $responseData['success']['error'] : $responseData['success']['failed'];
			$where = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $errorMsg, 'error' => $responseData['success']['error_code']), $where);	
		}		  
		else{
			foreach($responseData['success'] as $PTC => $PTCResponse){
				
				if($PTC == 'core') $itemType = 'core';
				elseif($PTC == 'plugins') $itemType = 'plugin';
				elseif($PTC == 'themes') $itemType = 'theme';				
				elseif($PTC == 'translations') $itemType = 'translations';				
				
				if(!empty($PTCResponse['error'])){
					
					$historyAdditionalUpdateData['status'] = 'error';
					$historyAdditionalUpdateData['errorMsg'] = $PTCResponse['error'];						
					$historyAdditionalUpdateData['error'] = $PTCResponse['error_code'];	
					$where = array(
			      		'query' =>  "historyID=':historyID'  AND detailedAction=':itemType'",
			      		'params' => array(
			               ':historyID'=>$historyID,
			               ':itemType'=>$itemType
           				)
        			);					
					DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
				}
				else{
					
					if($PTC == 'core'){
						$historyAdditionalUpdateData = array();
						$historyAdditionalUpdateData['status']= 'error';
						
						if(trim($PTCResponse['upgraded']) == 'updated'){
							$historyAdditionalUpdateData['status'] = 'success';
						}
						$where = array(
				      		'query' =>  "historyID=':historyID' AND uniqueName = ':uniqueName'",
				      		'params' => array(
				               ':historyID'=>$historyID,
				               ':uniqueName'=>'core'
	           				)
	        			);
						DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
					}
					elseif($PTC == 'plugins' || $PTC == 'themes'){

						foreach($PTCResponse['upgraded'] as $name => $success){
							$where = array(
						      		'query' =>  "historyID=':historyID' AND (uniqueName=':uniqueName' OR MD5(uniqueName)=':uniqueName') AND detailedAction=':detailedAction'",
						      		'params' => array(
						               ':historyID'=>$historyID,
						               ':uniqueName'=>$name,
						               ':detailedAction'=>$itemType
			           				)
			        			);		
							if($success == 1){
								$status = 'success';
								DB::update("?:history_additional_data", array('status' => $status), $where);
							}
							elseif(!empty($success)){
								$status = 'error';
								DB::update("?:history_additional_data", array('status' => $status, 'errorMsg' => $success['error'], 'error' => $success['error_code']), $where);
							}
							else{
								$status = 'error';
								DB::update("?:history_additional_data", array('status' => $status, 'error' => 'unknown', 'errorMsg' => 'An Unknown error occurred.', 'error' => 'unknown_error_occurred_updateall_res_proc'), $where);
							}
						}
					}
					elseif( $PTC == 'translations'){
						$isTranslationUpdate = true;
						$historyAdditionalUpdateData = array();
						$historyAdditionalUpdateData['status']= 'error';
						$where = array(
				      		'query' =>  "historyID=':historyID' AND uniqueName = ':uniqueName'",
				      		'params' => array(
				               ':historyID'=>$historyID,
				               ':uniqueName'=>'translations'
	           				)
	        			);
						if(trim($PTCResponse['upgraded']) == 'updated'){
							$historyAdditionalUpdateData['status'] = 'success';
							DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
						}else{
							$historyAdditionalUpdateData['status']= 'error';
							$historyAdditionalUpdateData['errorMsg']= $PTCResponse['upgraded']['error'];
							$historyAdditionalUpdateData['error']= $PTCResponse['upgraded']['error_code'];
							DB::update("?:history_additional_data", $historyAdditionalUpdateData, $where);
						}
					}
				}
			}
		}
		// self::triggerNextUpdateCall($historyID); //This could cause the issue more 
		$return = self::getUpdatePendingTasks($historyID);
		if ($return == true) {
			return;
		}
		//---------------------------callback process------------------------>
		$params = array();
		$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		$siteID = DB::getField("?:history", "siteID, actionID", $where);
		
		$params['parentHistoryID'] = $historyID;
		if ($isTranslationUpdate) {
			$params = array('forceRefresh' => 1);
		}

		$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'params' => $params, 'extras' => array('sendAfterAllLoad' => true, 'doNotShowUser' => true, 'directExecute'=> 1)));

		//Staging site will not be removed after updates
		// if (method_exists('manageClientsInstallCloneCommon', 'removeStagingSiteAfterUpdate')) {
		// $isRemove = manageClientsInstallCloneCommon::removeStagingSiteAfterUpdate($historyID, $siteID);
		// }

		panelRequestManager::handler($allParams);
	}

	public static function updateAllErrorResponseProcessor($historyID, $responseData){
		$return = self::getUpdatePendingTasks($historyID);
		if ($return == true) {
			return true;
		}

		$params = array();
		$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		$siteID = DB::getField("?:history", "siteID, actionID", $where);
		
		$params['parentHistoryID'] = $historyID;

		$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'params' => $params, 'extras' => array('sendAfterAllLoad' => true, 'doNotShowUser' => true, 'directExecute'=> 1)));

		//Staging site will not be removed after updates
		// if (method_exists('manageClientsInstallCloneCommon', 'removeStagingSiteAfterUpdate')) {
		// $isRemove = manageClientsInstallCloneCommon::removeStagingSiteAfterUpdate($historyID, $siteID);
		// }

		panelRequestManager::handler($allParams);
		return true;

	}

	public static function triggerNextUpdateCall($historyID){
	
		$historyData = getHistory($historyID);
		$return = false;
		$where = array(
		      		'query' =>  "actionID=':actionID' AND status='scheduled' ORDER BY historyID",
		      		'params' => array(
		               ':actionID'=>$historyData['actionID']
       				)
    			);
		$nextUpdateHistoryIDs = DB::getFields("?:history", "historyID", $where);
		if (!empty($nextUpdateHistoryIDs)) {
			foreach ($nextUpdateHistoryIDs as $key => $historyID) {
				$isRunning = self::isUpdateRunning($historyID);
				if ($isRunning == false) {
					$return = true;
					updateHistory(array('status' => 'pending'), $historyID);
					break;
				}
			}
		}else{
			$nextUpdateHistoryIDs = DB::getFields("?:history", "historyID", "type ='PTC' AND status ='scheduled' ORDER BY historyID");
			if (!empty($nextUpdateHistoryIDs)) {
				foreach ($nextUpdateHistoryIDs as $key => $historyID) {
					$isRunning = self::isUpdateRunning($historyID);
					if ($isRunning == false) {
						updateHistory(array('status' => 'pending'), $historyID);
						break;
					}
				}
			}
		}

		return $return;

	}
	
	public static function updateClientProcessor($siteIDs, $params){		
		$requestAction = 'update_client';
		$type = 'clientPlugin';
		$action = 'update';
		$events = 1;
		
		$clientPluginUpdate = panelRequestManager::getClientUpdateAvailableSiteIDs();
		$clientUpdatePackage = base64_decode($clientPluginUpdate['clientUpdatePackage']);
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('detailedAction' => 'update', 'uniqueName' => 'clientPlugin');
		foreach($siteIDs as $siteID){
				$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			);
				$currentVersion = DB::getField("?:sites", "pluginVersion", $where);
				if(version_compare($currentVersion,  $params['clientUpdateVersion'] ) == -1){
					$siteData = getSiteData($siteID);
					$requestParams = array('download_url' =>$clientUpdatePackage);
					$ftpDetails = DB::getField('?:sites', 'ftpDetails', $where);
					if (!empty($ftpDetails)) {
						$requestParams['secure']['account_info'] = $ftpDetails;
					}
					$PRP = array();
					$PRP['requestAction'] 	= $requestAction;
					$PRP['requestParams'] 	= $requestParams;
					$PRP['siteData'] 		= $siteData;
					$PRP['type'] 			= $type;
					$PRP['action'] 			= $action;
					$PRP['events'] 			= $events;
					$PRP['historyAdditionalData'] 	= $historyAdditionalData;
					$PRP['sendAfterAllLoad'] 		= true;			
					
					prepareRequestAndAddHistory($PRP);
			}
		}
	}
	
	public static function updateClientResponseProcessor($historyID, $responseData){
			
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}
		$where = array(
					'query' =>  "historyID=':historyID'",
					'params' => array(
						':historyID'=>$historyID,
					)
				);
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);
		}
		if(!empty($responseData['success']['success'])){
			DB::update("?:history_additional_data", array('status' => 'success'), $where);

			//---------------------------callback process------------------------>
			$siteID = DB::getField("?:history", "siteID", $where);
			// 'directExecute'=>true for client plugin update notification
			$allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('sendAfterAllLoad' => false,'directExecute' => true, 'doNotShowUser' => true))); 
			panelRequestManager::handler($allParams);
			
		}
	}

	public static function isUpdateRunning($historyID, $siteID){
		if (empty($siteID)) {
			return false;
		}
		$where = array(
			      		'query' =>  "siteID = ':siteID' AND historyID != ':historyID' AND type = 'PTC' AND status IN('writingRequest', 'initiated', 'running')",
			      		'params' => array(
			               ':siteID'=>$siteID,
			               ':historyID'=>$historyID
           				)
        			);
		$isRunning = DB::getExists("?:history", "siteID", $where);
		if ($isRunning) {
			return true;
		}

		return false;
	}

	public static function isUpdateRunningAditionalCheck($historyID){
		$whereHis = array(
			      		'query' =>  "historyID = ':historyID' AND type = 'PTC'",
			      		'params' => array(
			               ':historyID'=>$historyID
           				)
        			);
		$siteID = DB::getField("?:history", "siteID", $whereHis);
		if (empty($siteID)) {
			return false;
		}
		$where = array(
			      		'query' =>  "siteID = ':siteID' AND historyID != ':historyID' AND type = 'PTC' AND status IN('initiated', 'running')",
			      		'params' => array(
			               ':siteID'=>$siteID,
			               ':historyID'=>$historyID
           				)
        			);
		$isRunning = DB::getExists("?:history", "siteID", $where);
		if ($isRunning) {
			return true;
		}

		return false;
	}

	public static function getUpdatePendingTasks($historyID){
		$whereHis = array(
			      		'query' =>  "historyID = ':historyID'",
			      		'params' => array(
			               ':historyID'=>$historyID
		   				)
					);
		$historyData = DB::getRow("?:history", "actionID, siteID", $whereHis);
		$where = array(
			      		'query' =>  "actionID = ':actionID' AND siteID = ':siteID' AND type = 'PTC' AND status IN ('pending', 'scheduled', 'retry', 'running')",
			      		'params' => array(
			               ':actionID'=>$historyData['actionID'],
			               ':siteID'=>$historyData['siteID']
           				)
        			);
		$isRunning = DB::getExists("?:history", "siteID", $where);
		if ($isRunning) {
			return true;
		}
		return false;
	}

	public static function getLastWaitingHistoryIDBySiteID($siteID){
		$where = array(
			      		'query' =>  "siteID = ':siteID' AND type = 'PTC' AND status NOT IN('completed', 'error', 'netError') ORDER BY historyID DESC LIMIT 1",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
		$historyID = DB::getField("?:history", "historyID", $where);
		if (!empty($historyID)) {
			return $historyID;
		}

		return false;
	}

	public static function prepareRunCondition(&$PRP, $lastHistoryID, $isRetryCondition = true){
		$runCondition = 	array();
		$runCondition['satisfyType'] = 'OR';

		if ($isRetryCondition) {
			$whereCondtion = "historyID = ".$lastHistoryID." AND status IN('success', 'error', 'netError') OR historyID = (select `historyID` from `".Reg::get('config.SQL_TABLE_NAME_PREFIX')."history` where `historyID` = ".$lastHistoryID." AND `status`= 'retry')";
		}else{
			$whereCondtion = "historyID = ".$lastHistoryID." AND status IN('success', 'error', 'netError')";
		}

		$runCondition['query'] = array('table' => "history_additional_data",
										  'select' => 'historyID',
										  'where' => $whereCondtion,
										  'lastHistoryID' => $lastHistoryID
										);
		$PRP['runCondition'] = serialize($runCondition);
		$PRP['status'] = 'scheduled';
		$PRP['timeScheduled'] = time();
	}
}

manageClients::addClass('manageClientsUpdate');
