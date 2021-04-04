<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
class manageClientsSites{
	
	  public static function addSiteProcessor($dummy, $params){ // Add a site
	  
		if (defined('APP_V3')) {
			if(empty($params['isStaging']) && (panelRequestManagerV3::checkIsAddonPlanLimitExceededV3() && panelRequestManagerV3::getAddonSuitePlanActivityV3()=='installed')) return(false);
	  	}else{
	  		if(empty($params['isStaging']) && (panelRequestManager::checkIsAddonPlanLimitExceeded() && panelRequestManager::getAddonSuitePlanActivity()=='installed')) return(false);
	  	}
		
		$requestAction = "add_site";
		$action = "add";
		$type = "site";
		$actionID = Reg::get('currentRequest.actionID');
		$timeout = DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT;
		$params['URL'] = trim($params['URL']);
		$params['websiteURL'] = trim($params['websiteURL']);
		manageClientsSites::preAddSiteCheckForNetwork($params);
		if (!empty($params['isNetworkSite'])) {
			$parentSiteDetails = getSiteData($params['parentSiteID']);
			$params['username'] = trim($parentSiteDetails['adminUsername']);
			$params['activationKey'] = trim($parentSiteDetails['activationKey']);
			if(!empty($parentSiteDetails['callOpt'])){
				$params['callOpt'] = unserialize($parentSiteDetails['callOpt']);
			}
			$params['connectURL'] = $parentSiteDetails['connectURL'];
			$params['websiteURL'] = $params['URL'];
			if(!empty($parentSiteDetails['httpAuth'])){
				$params['httpAuth'] = unserialize($parentSiteDetails['httpAuth']);
			}
		}else{
			$params['username'] = trim($params['username']);
			$params['activationKey'] = trim($params['activationKey']);
		}
		$params['parentHistoryID'] = trim($params['parentHistoryID']);
		
		$events = 1;
		
		if(!empty($params['URL'])){
			$params['URL'] = $params['URL'].(substr($params['URL'], -1) == '/' ? '' : '/');
		}
		if(!empty($params['websiteURL'])){
			$params['websiteURL'] = $params['websiteURL'].(substr($params['websiteURL'], -1) == '/' ? '' : '/');
		}
		
		$connectURL = !empty($params['connectURL']) ? $params['connectURL'] : 'default';

		if ($connectURL == 'siteURL') {
			$requestURL = $params['websiteURL'];
		}else{
			$requestURL = $params['URL'];
		}

		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => $requestURL, 'detailedAction' => $action);
		
		
		$historyData = array('siteID' => '0', 'actionID' => $actionID, 'userID' => $GLOBALS['userID'], 'type' => $type, 'action' => $action, 'events' => $events, 'URL' => $requestURL, 'timeout' => $timeout, 'parentHistoryID' => $params['parentHistoryID']);	
		
		$callOpt = array();
		
		if(!empty($params['callOpt'])){
			$callOpt = $params['callOpt'];
		}
		
		if(!empty($params['httpAuth']['username'])){
			
			$callOpt['httpAuth'] = $params['httpAuth'];
			$historyData['callOpt'] = @serialize($callOpt);
		}
		if(!empty($params['doNotShowUser'])){
			$historyData['showUser'] = 'N';
		}

		if (!empty($params['actionID'])) {
			$historyData['actionID'] = $params['actionID'];
		}

		$historyID = addHistory($historyData, $historyAdditionalData);
				
		if (checkOpenSSL()) {//use when remote WP has openssl installed or not installed
			if (!empty($params['isNetworkSite'])) {
				$publicKey 	= $parentSiteDetails['publicKey'];
				$privateKey = $parentSiteDetails['privateKey'];
			}else{
				$key = @openssl_pkey_new();
				@openssl_pkey_export($key, $privateKey);
				$privateKey	= base64_encode($privateKey);
				$publicKey 	= @openssl_pkey_get_details($key);
				$publicKey 	= $publicKey["key"];
				$publicKey 	= base64_encode($publicKey);
			}
			openssl_sign($requestAction.$historyID ,$signData ,base64_decode($privateKey));
			$signData 	= base64_encode($signData);
			
			$GLOBALS['storage']['newSite']['addSitePrivateKey'] = $privateKey;
		}
		else{//if HOST Manager doesnt have openssl installed
			if(!defined('USE_RANDOM_KEY_SIGNINIG')){
				define('USE_RANDOM_KEY_SIGNINIG', true);
			}

			srand();
			
			//some random text
			$publicKey = 'FMGJUKHFKJHKHEkjfcjkshdkhauiksdyeriaykfkzashbdiadugaisbdkbasdkh36482763872638478sdfkjsdhkfhskdhfkhsdfi323798435h453h4d59h4iu5ashd4ui5ah4sd5fih65fd958345454h65fkjsa4fhd5649dasf86953q565kb15ak1b';		  			
			$publicKey = sha1($publicKey).substr($publicKey, rand(0, 50), rand(50, strlen(rand(0, strlen($publicKey)))));
			
			$publicKey = md5(rand(0, getrandmax()) . base64_encode($publicKey) . rand(0, getrandmax()));;
			
			$signData = md5($requestAction.$historyID.$publicKey);
			
		}
		
		if(!empty($params['managerID'])){
			$GLOBALS['storage']['newSite']['managerID'] = $params['managerID'];
		}
		if(!empty($params['addSiteFtpDetails'])){
			$GLOBALS['storage']['newSite']['addSiteFtpDetails'] = $params['addSiteFtpDetails'];
		}
		
		//using GLOBALS on the assumption addSite is always direct call not async call
		$GLOBALS['storage']['newSite']['addSiteAdminUsername'] = $params['username'];
		$GLOBALS['storage']['newSite']['siteName'] = $params['siteName'];
		$GLOBALS['storage']['newSite']['groupsPlainText'] =  $params['groupsPlainText'];
		$GLOBALS['storage']['newSite']['groupIDs'] =  $params['groupIDs'];
		$GLOBALS['storage']['newSite']['httpAuth'] = $params['httpAuth'];
		$GLOBALS['storage']['newSite']['callOpt'] = $params['callOpt'];
		$GLOBALS['storage']['newSite']['connectURL'] = !empty($params['connectURL']) ? $params['connectURL'] : 'default';
		$GLOBALS['storage']['newSite']['advancedCUCT'] = intval($params['advancedCUCT']);
		$GLOBALS['storage']['newSite']['publicKey'] = $publicKey;
		$GLOBALS['storage']['newSite']['activationKey'] = $params['activationKey'];
		$GLOBALS['storage']['newSite']['parentSiteID'] = $params['parentSiteID'];
		$GLOBALS['storage']['newSite']['websiteURL'] = $params['websiteURL'];

		
		$requestParams = array('site_url' => $requestURL, 'action' => $requestAction, 'public_key' => $publicKey, 'id' => $historyID, 'signature' => $signData, 'username' => $params['username'], 'activation_key' => $params['activationKey']);
		if(defined('USE_RANDOM_KEY_SIGNINIG')){
			$requestParams['user_random_key_signing'] = 1;
		}
		
		$requestData = array('iwp_action' => $requestAction, 'params' => $requestParams, 'iwp_admin_version' => APP_VERSION);
		if($GLOBALS['storage']['newSite']['advancedCUCT'])
			$GLOBALS['storage']['newSite']['requestData'] = $requestData;
		
		$updateHistoryData = array('status' => 'pending', 'param1'=>serialize($GLOBALS['storage']['newSite']));
		
		updateHistory($updateHistoryData, $historyID);
		
		DB::insert("?:history_raw_details", array('historyID' => $historyID, 'request' => base64_encode(serialize($requestData)), 'panelRequest' => serialize($_REQUEST) ) );
		
		return executeRequest($historyID, $type, $action, $requestURL, $requestData, $timeout, true, $callOpt);
	  }
	  
	  public static function addSiteResponseProcessor($historyID,$responseData){
		    
		responseDirectErrorHandler($historyID, $responseData);
		$where = array(
				'query' =>  "historyID=':historyID'",
				'params' => array(
		       ':historyID'=>$historyID
				)
		);
		if (empty($GLOBALS['storage']['newSite'])) {
			$param1 = DB::getField('?:history', 'param1', $where);
			if (!empty($param1)) {
				$GLOBALS['storage']['newSite'] = unserialize($param1);
			}
		}
		$GLOBALS['storage']['newSite']['recursiveCount'] = -1;
		/*

		//We can do retry if network errors, why we need to retry proper plugin errors?.
		if( empty($responseData['success']) && $responseData['error_code'] != 'iwp_mmb_shutdown' && empty($GLOBALS['storage']['newSite']['lastCall']) ){
			$isAddSiteRetried = addSiteRetry($historyID);
			if ($isAddSiteRetried != false) {
				return false;
			}
		}
	  	  */

		  if(!empty($responseData['success']['error'])){ //There is no variable "$responseData['error_data']" exists in plugin. especially, in add_site task. Error wont come in success array ...
	
			  DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);	
		  }
		  elseif(!empty($responseData['success'])){
			  DB::update("?:history_additional_data", array('status' => 'success'), $where);
			  
			  $privateKey = $GLOBALS['storage']['newSite']['addSitePrivateKey'];
			  $isOpenSSLActive = '1';
			  if($responseData['success']['no_openssl']){
				  $privateKey = NULL;
				  $isOpenSSLActive = '0';
			  }
			  
			  $URLParts = explode('/', $responseData['success']['site_home']);
			  $siteName = str_replace(array('http://www.', 'https://www.', 'http://', 'https://'), '', $responseData['success']['site_home']);
			  if(!empty($GLOBALS['storage']['newSite']['siteName'])){
			  		$siteName = $GLOBALS['storage']['newSite']['siteName'];
			  }			 			  
			  $siteData = array( "URL" 				=> $responseData['success']['site_home'].'/',
								 "adminURL"			=> $responseData['success']['admin_url'],
								 "name" 			=> $siteName,
								 "privateKey" 		=> $privateKey,
								 "adminUsername" 	=> $GLOBALS['storage']['newSite']['addSiteAdminUsername'],
								 "isOpenSSLActive" 	=> $isOpenSSLActive,
								 "randomSignature" 	=> $responseData['success']['no_openssl'],
								 "WPVersion"		=> $responseData['success']['wordpress_version'],
								 "pluginVersion" 	=> $responseData['success']['worker_version'],
								 "IP" 				=> gethostbyname($URLParts[2]),
								 "network" 			=> ($responseData['success']['network_install'] == -1) ? 1 : 0,
								 "multisiteID" 		=> empty($responseData['success']['wp_multisite']) ? 0 : $responseData['success']['wp_multisite'],
								 "parent" 			=> ($responseData['success']['site_home'] == $responseData['success']['network_parent']) ? 1 : 0,
								 "connectURL" 		=> $GLOBALS['storage']['newSite']['connectURL'],
								 "activationKey"    => $GLOBALS['storage']['newSite']['activationKey'],
								 "publicKey"    	=> $GLOBALS['storage']['newSite']['publicKey'],
								 "parentSiteID"		=> ($GLOBALS['storage']['newSite']['parentSiteID'])?$GLOBALS['storage']['newSite']['parentSiteID']:0
							 	); // save data
								
			  if(!empty($GLOBALS['storage']['newSite']['httpAuth']['username'])){
					$siteData['httpAuth']['username'] = $GLOBALS['storage']['newSite']['httpAuth']['username'];
					$siteData['httpAuth']['password'] = $GLOBALS['storage']['newSite']['httpAuth']['password'];
					$siteData['httpAuth'] = @serialize($siteData['httpAuth']);
			  }
			  
			  if(!empty($GLOBALS['storage']['newSite']['callOpt']) || $responseData['success']['use_cookie'] == 1){		
			  		
					$callOpt = array();
					if($responseData['success']['use_cookie'] == 1){
						$callOpt['useCookie'] = 1;
					}
					
					if(!empty($GLOBALS['storage']['newSite']['callOpt'])){
						$callOpt = array_merge($callOpt, $GLOBALS['storage']['newSite']['callOpt']);
					}
					
			  		$siteData['callOpt'] = @serialize($callOpt);
			  }

			$parentHisID = getParentHistoryID($historyID);
			$detaileAction = DB::getFields("?:history_additional_data","detailedAction", "historyID = ".$parentHisID);
			$parentActionID = DB::getField('?:history', 'actionID', 'historyID = '.$parentHisID);
			if (function_exists('stagingSaveExistingStaging')) {
			  	$panelRequest = DB::getField("?:history_raw_details", 'panelRequest', $where);
			  	$panelRequest = unserialize($panelRequest);
			  	if (!empty($panelRequest['args']['params']['isStaging']) && isset($panelRequest['args']['params']['isStaging']) ) {
			  		$isStaging = 1;
					$siteData['stagingBaseSiteID'] = ($panelRequest['args']['params']['saveExisingSiteFtpDetails']['siteID'])?$panelRequest['args']['params']['saveExisingSiteFtpDetails']['siteID']:0;
			  	}

			}
			if((!empty($detaileAction[0]) && $detaileAction[0] == 'staging') || !empty($isStaging)){
				$siteData['type'] = 'staging';
			}

			  $siteID = DB::insert('?:sites', $siteData);
			  if($detaileAction[0] != 'staging' && empty($isStaging)){
			  	DB::replace("?:user_access", array('userID' => $GLOBALS['userID'], 'siteID' => $siteID));
			  }

			  if(!empty($GLOBALS['storage']['newSite']['addSiteFtpDetails'])){
			  	// based on ftpdetails in edit site
			  	$ftpDetails = $GLOBALS['storage']['newSite']['addSiteFtpDetails'];
			  	$ftpDetails['siteID'] = $siteID;
			  	$ftpDetails['sourceSiteID'] = $siteID;
			  	panelRequestManager::saveSiteFtpDetails($ftpDetails);
			  }
			  
			  
			  $groupsPlainText = $GLOBALS['storage']['newSite']['groupsPlainText'];
		  	  $groupIDs = $GLOBALS['storage']['newSite']['groupIDs'];

			  $managerIDs = $GLOBALS['storage']['newSite']['managerID'];
		  	  if(isset($managerIDs) && !empty($managerIDs) && ($detaileAction[0] != 'staging' && empty($isStaging))){
				  foreach($managerIDs as $key => $managerID){
					 DB::replace("?:user_access", array('userID' => $managerID, 'siteID' => $siteID));
				  }
			  }	
			  if (function_exists('stagingSaveExistingStaging')) {
			  	if (!empty($panelRequest['args']['params']['saveExisingSiteFtpDetails']) && isset($panelRequest['args']['params']['saveExisingSiteFtpDetails']) ) {
			  		stagingSaveExistingStaging($panelRequest['args']['params']['saveExisingSiteFtpDetails']);
			  	}
			  }	  
			  panelRequestManager::addSiteSetGroups($siteID, $groupsPlainText, $groupIDs);			  
			  unset($GLOBALS['storage']['newSite']);
			  if (($responseData['success']['site_home'] == $responseData['success']['network_parent'])) {
			  	saveNetworkSite($siteID, $responseData['success']['network_blogs']);
			  }
			  
			  //---------------------------post process------------------------>
		  
			  $allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('directExecute' => true, 'doNotShowUser' => true)));
			  
			  panelRequestManager::handler($allParams);
			  panelRequestManager::getSiteFavicon($siteID);		
			  /*if (!empty($isStaging)) { This causing the issue if site uploaded same 
			  	return;
			  }*/
			  if( $detaileAction[0] != 'staging'){
			  	setHook('postAddSite', $siteID, $historyID);//check this once
			  } else{
			  	setHook('postAddStagingSite', $siteID, $historyID);//check this once
			  }
  
			  
		  }	
	  }
	  
	  public static function removeSiteProcessor($siteIDs, $params){
		  
		  if(empty($siteIDs)){ return false; }
		  		 
		  $type = 'site';
		  $action = 'remove';
		  $requestAction = 'remove_site';
		  $events = 1;
		  $requestParams = array('deactivate' => $params['iwpPluginDeactivate']);
		  
		  $historyAdditionalData = array();
	      $historyAdditionalData[] = array('uniqueName' => 'remove_site', 'detailedAction' => 'remove');
		  foreach($siteIDs as $siteID){
		  	panelRequestManager::removeFavicon($siteID);		
			$siteData = getSiteData($siteID);
			/* removing site from admin panel without getting confirmation from client plugin */
			$where = array(
			      		'query' =>  "siteID = ':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
			$updateInfo = DB::getField('?:site_stats', 'updateInfo', $where);
			$updateInfoIDs = manageUpdates::formatPattern($updateInfo);
			if (!empty($updateInfoIDs)) {
						$IDs = ""; $i=0;
				foreach ($updateInfoIDs as $key => $item) {
					$IDs = $IDs.implode(", ",$item);
					if($i!=count($updateInfoIDs)-1){
						$IDs=$IDs.", ";
					}
					$i++;	
				}

				DB::update('?:update_stats','updatePresentCount = updatePresentCount - 1', "ID IN(".DB::esc($IDs).")");
				//DB::delete('?:update_stats','updatePresentCount = 0');
			}
			DB::delete("?:sites", $where);
			DB::delete("?:site_stats", $where );
			DB::delete("?:groups_sites", $where );
			DB::delete("?:hide_list", $where );
			DB::delete("?:user_access", $where );
			removeNetworkSite($siteID);
			$checkV3Installed = getOption('V3Installed');
			if(!empty($checkV3Installed) && ($checkV3Installed == 1)){
				DB::delete("?:additional_stats", $where);
			}
			setHook('removeSite', $siteID);
			$where = array(
			      		'query' =>  "parentSiteID = ':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
           				)
        			);
			DB::delete("?:sites", $where);
			/* removing site from admin panel done */
			
						
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['requestParams'] 	= $requestParams;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 		= $action;
			$PRP['events'] 		= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['doNotExecute'] 			= false;
			$PRP['directExecute'] = true;
			$PRP['doNotShowUser'] = $params['doNotShowUser'];
			  
			prepareRequestAndAddHistory($PRP);

		 }
	  }
	  
	 public static function removeSiteResponseProcessor($historyID, $responseData){
		  $where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		  responseDirectErrorHandler($historyID, $responseData);		  	  
		  if(!empty($responseData['success']['error'])){
			  DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['error_data']), $where);	
		  }
		  elseif(!empty($responseData['success'])){
			  DB::update("?:history_additional_data", array('status' => 'success'), $where);
		  }
	 }	  
	 public static function loadSiteProcessor($siteIDs, $params){
		 
		$timeout = DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT;
		//$siteID = reset($siteIDs);
		$siteID = $_REQUEST['siteID'];
		if(empty($siteID)){ echo 'Invalid Site ID'; }
		//$where = $params['where'] ? $params['where'].".php" : '';
		$where = $_REQUEST['where'] ? $_REQUEST['where'].".php" : '';
		$loadSiteVars = array();
		
		if(isset($_REQUEST['var_0'])){
			for($i=0;$i<5;$i++){
				if(isset($_REQUEST['var_'.$i]) && strpos($_REQUEST['var_'.$i], '__IWPVAR__') !== false){
					$temp = explode('__IWPVAR__', $_REQUEST['var_'.$i]);
					$loadSiteVars[$temp[0]] = $temp[1];
				}				
			}					
		}
		
		//if(!empty($params['vars']) && is_array($params['vars'])){
//			$loadSiteVars = $params['vars'];
//		}

		$siteData = DB::getRow("?:sites", "*", "siteID=".DB::realEscapeString($siteID));
		if(empty($siteData)){ echo 'Invalid Site ID'; }
		$type = 'site';
		$action = 'load';
		$events = 1;
		
		$historyData = array('siteID' => $siteData['siteID'], 'actionID' => Reg::get('currentRequest.actionID'), 'userID' => $GLOBALS['userID'], 'type' => $type, 'action' => $action, 'events' => $events, 'URL' => $siteData['URL'], 'status' => 'completed', 'timeout' => $timeout);
		
		$historyAdditionalData[] = array('detailedAction' => 'loadSite', 'uniqueName' => 'loadSite', 'status' => 'success');
			
		$historyID = addHistory($historyData, $historyAdditionalData);
	
		$signature = signData($where.$historyID, $siteData['isOpenSSLActive'], $siteData['privateKey'], $siteData['randomSignature']);
		
		$URLQueryArray = array('auto_login' => 1, 'iwp_goto' => $where, 'signature' => base64_encode($signature), 'message_id' => $historyID, 'username' => $siteData['adminUsername']);//signature urlencode will be taken care by httpBuildURLCustom()
		
		if(!empty($loadSiteVars) && is_array($loadSiteVars)){
			$URLQueryArray = array_merge($URLQueryArray, $loadSiteVars);	
		}
		
		$adminURLArray = parse_url($siteData['adminURL']);
		
		
		if(!empty($adminURLArray['query'])){
			$parsedQuery = array();
			parse_str($adminURLArray['query'], $parsedQuery);
			if(!empty($parsedQuery) && is_array($parsedQuery)){
				$URLQueryArray = array_merge($parsedQuery, $URLQueryArray);
			}
		}
		$adminURLArray['query'] = $URLQueryArray;
		$adminURLArray['path'] .= $where ? $where : '';
		
		
		$URL = httpBuildURLCustom($adminURLArray);


		//$URL .='&signature='.$tempSignature;
		
		//$URL = $siteData['adminURL'].$where.'?'."auto_login=1&iwp_goto=".$where."&signature=".urlencode(base64_encode($signature))."&message_id=".$historyID."&username=".$siteData['adminUsername'];
		
		
		
		if(!empty($siteData['httpAuth'])){
			$siteData['httpAuth'] = @unserialize($siteData['httpAuth']);	
			if(!empty($siteData['httpAuth']['username'])){
				$URL = str_replace('://', '://'.$siteData['httpAuth']['username'].':'.urlencode($siteData['httpAuth']['password']).'@', $URL);
			}
		}		
		
		$updateHistoryData = array('param3' => $URL);	
		updateHistory($updateHistoryData, $historyID);
		//Reg::set('currentRequest.loadSiteURL', $URL);
		header("Location: ".$URL);
		exit;	

	}
	
public static function readdSiteProcessor($siteIDs, $params){
		if(empty($siteIDs)){ return false; }
		$siteID = $siteIDs[0];
		$requestAction = "readd_site";
		$action = "readd";
		$type = "site";
		$actionID = Reg::get('currentRequest.actionID');
		$timeout = DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT;
		$params['activationKey'] = trim($params['activationKey']);
		$params['parentHistoryID'] = trim($params['parentHistoryID']);
		//$paramVars = DB::getRow("?:sites", "*","siteID = '".$siteID."'");
                
                /*if( ($paramVars['connectURL'] == 'default' && defined('CONNECT_USING_SITE_URL') && CONNECT_USING_SITE_URL == 1) || $paramVars['connectURL'] == 'siteURL'){
                        $URL = $paramVars['URL'];
                }
                else{//if($siteData['connectURL'] == 'default' || $siteData['connectURL'] == 'adminURL')
                        $URL = $paramVars['adminURL'];
                }
    
                 */
		//$params['URL'] = $URL;
		//$params['username'] = trim($paramVars['adminUsername']);
		
		$events = 1;
		
//		if(!empty($params['URL'])){
//			$params['URL'] = $params['URL'].(substr($params['URL'], -1) == '/' ? '' : '/');
//		}
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => $params['URL'], 'detailedAction' => $action);
		
		
		/*$historyData = array('siteID' => $siteID, 'actionID' => $actionID, 'userID' => $_SESSION['userID'], 'type' => $type, 'action' => $action, 'events' => $events, 'URL' => $params['URL'], 'timeout' => $timeout);	
		
		$callOpt = array();
		
		if(!empty($paramVars['callOpt'])){
			$callOpt = $paramVars['callOpt'];
		}
		
		if(!empty($paramVars['httpAuth']['username'])){
			
			$callOpt['httpAuth'] = $paramVars['httpAuth'];
			$historyData['callOpt'] = $callOpt;
		}*/
		//$historyID = addHistory($historyData, $historyAdditionalData);
// some codes are similar in add site module, whenever updating add site update here also
		if (checkOpenSSL()) {//use when remote WP has openssl installed or not installed
		
			$key = @openssl_pkey_new();
			@openssl_pkey_export($key, $privateKey);
			$privateKey	= base64_encode($privateKey);
			$publicKey 	= @openssl_pkey_get_details($key);
			$publicKey 	= $publicKey["key"];
			$publicKey 	= base64_encode($publicKey);
			//openssl_sign($requestAction.$historyID ,$signData ,base64_decode($privateKey));
			//$signData 	= base64_encode($signData);
			$signData = false;
                        $isOpenSSLActive = 1;
                        
			$GLOBALS['storage']['oldSite']['readdSitePrivateKey'] = $privateKey;
		}
		else{//if HOST Manager doesnt have openssl installed
			if(!defined('USE_RANDOM_KEY_SIGNINIG')){
				define('USE_RANDOM_KEY_SIGNINIG', true);
			}


			srand();
			
			//some random text
			$publicKey = 'FMGJUKHFKJHKHEkjfcjkshdkhauiksdyeriaykfkzashbdiadugaisbdkbasdkh36482763872638478sdfkjsdhkfhskdhfkhsdfi323798435h453h4d59h4iu5ashd4ui5ah4sd5fih65fd958345454h65fkjsa4fhd5649dasf86953q565kb15ak1b';		  			
			$publicKey = sha1($publicKey).substr($publicKey, rand(0, 50), rand(50, strlen(rand(0, strlen($publicKey)))));
			
			$publicKey = md5(rand(0, getrandmax()) . base64_encode($publicKey) . rand(0, getrandmax()));;
			
			$signData = md5($requestAction.$historyID.$publicKey);
                        $isOpenSSLActive = 0;
			
		}

		$requestParams = array('site_url' => $params['URL'], 'action' => $requestAction, 'public_key' => $publicKey, /*'id' => $historyID, 'signature' => $signData,*/ 'username' => $params['username'], 'activation_key' => $params['activationKey']);
		if(defined('USE_RANDOM_KEY_SIGNINIG')){		  
			$requestParams['user_random_key_signing'] = 1;
		}
		
		//$requestData = array('iwp_action' => $requestAction, 'params' => $requestParams, 'iwp_admin_version' => APP_VERSION);
		
		//$updateHistoryData = array('status' => 'pending');
		
		//updateHistory($updateHistoryData, $historyID);
		
		//DB::insert("?:history_raw_details", array('historyID' => $historyID, 'request' => base64_encode(serialize($requestData)), 'panelRequest' => serialize($_REQUEST) ) );
		
		//return executeRequest($historyID, $type, $action, $params['URL'], $requestData, $timeout, true, $callOpt);
		$staging = true;
		if (!empty($params['isStaging'])) {
			$staging = false;
		}

		$siteData = getSiteData(intval($siteID), '' , $staging);

		//overide
		$siteData['privateKey'] = $privateKey;
		$siteData['isOpenSSLActive'] = $isOpenSSLActive;

		$PRP = array();
		$PRP['requestAction'] 	= $requestAction;
		$PRP['siteData'] 		= $siteData;
		$PRP['type'] 			= $type;
		$PRP['action'] 			= $action;
		$PRP['requestParams'] 	= $requestParams;
		$PRP['directExecute'] 	= true;
		$PRP['events'] 			= $events;
		$PRP['sendAfterAllLoad'] = false;
		$PRP['historyAdditionalData'] 	= $historyAdditionalData;
        $PRP['signature'] = $signData;
		$PRP['parentHistoryID'] = $params['parentHistoryID'];

		if(!empty($params['doNotShowUser'])){
			$PRP['doNotShowUser'] = 'N';
		}
		if (!empty($params['actionID'])) {
			$PRP['actionID'] = $params['actionID'];
		}
		return prepareRequestAndAddHistory($PRP);


		// return false;
	}
	public static function readdSiteResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		  
		if( empty($responseData['success']) ){
		  return false;
		}
		$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		if(!empty($responseData['success']['error'])){ //There is no variable "$responseData['error_data']" exists in plugin. especially, in add_site task. Error wont come in success array ...

		  DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);	
		}
		elseif(!empty($responseData['success'])){
		  DB::update("?:history_additional_data", array('status' => 'success'), $where);
		  
		  $privateKey = $GLOBALS['storage']['oldSite']['readdSitePrivateKey'];
		  $isOpenSSLActive = '1';
		  if($responseData['success']['no_openssl']){
			  $privateKey = NULL;
			  $isOpenSSLActive = '0';
		  }
		  
		  $URLParts = explode('/', $responseData['success']['site_home']);
		  			 			  
		  
			$siteData = array("privateKey" 		=> $privateKey,"randomSignature" 	=> $responseData['success']['no_openssl']);

			$siteID = DB::getField("?:history", "siteID", $where);
			$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			);
		  DB::update("?:sites", $siteData, $where);
		  $parentHisID = getParentHistoryID($historyID);
		  $detaileAction = DB::getFields("?:history_additional_data","detailedAction", "historyID = ".$parentHisID);
		  if( $detaileAction[0] != 'staging'){
		  	DB::replace("?:user_access", array('userID' => $GLOBALS['userID'], 'siteID' => $siteID));			  
		  }
		  
		  			  
		  unset($GLOBALS['storage']['oldSite']);
		  
		  
		  
		  //---------------------------post process------------------------>

		  $allParams = array('action' => 'getStats', 'args' => array('siteIDs' => array($siteID), 'extras' => array('directExecute' => true, 'doNotShowUser' => true)));
		  
		  panelRequestManager::handler($allParams);
		  panelRequestManager::getSiteFavicon($siteID);		
		  if( $detaileAction[0] != 'staging'){
		  	setHook('postReaddSite', $siteID, $historyID);//check this once
		  } else{
		  	setHook('postReaddStagingSite', $siteID, $historyID);//check this once
		  }
		}
	}

	public static function iwpMaintenanceProcessor($siteIDs,$params){
		if(empty($siteIDs)){ return false; }
		if(isset($params['mHTML'])){
        	$params['mHTML'] = str_replace("\\",'',html_entity_decode(str_replace('\n',PHP_EOL,$params['mHTML'])));
        }
		$siteID = $siteIDs[0];
		$type = "site";
		$action = "maintain";
		$requestAction = "maintain_site";
		$events=1;
		$requestParams = array();
		$requestParams['maintenance_mode'] = intval(trim($params['mcheck']));
		$requestParams['maintenance_html'] = trim($params['mHTML']);
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'maintenance'.$requestParams['maintenance_mode'], 'detailedAction' => ($requestParams['maintenance_mode']==0)?"deactive":"active");
		$siteData = getSiteData(intval($siteID));
		$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			);
        DB::update("?:sites",array('lastMaintenanceModeHTML'=>trim($params['mHTML'])),$where);
		$PRP = array();
		$PRP['requestAction'] 	= $requestAction;
		$PRP['siteData'] 		= $siteData;
		$PRP['type'] 			= $type;
		$PRP['action'] 			= $action;
		$PRP['requestParams'] 	= $requestParams;
		$PRP['directExecute'] 	= true;
		$PRP['events'] 			= $events;
		$PRP['sendAfterAllLoad'] = false;
		$PRP['historyAdditionalData'] 	= $historyAdditionalData;
		prepareRequestAndAddHistory($PRP);
		
	}


	public static function iwpMaintenanceResponseProcessor($historyID, $responseData){
		responseDirectErrorHandler($historyID, $responseData);
		if( empty($responseData['success']) ){
		  return false;
		}
		$where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
		if(!empty($responseData['success']['error'])){ //There is no variable "$responseData['error_data']" exists in plugin. especially, in add_site task. Error wont come in success array ...

		  DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error'], 'error' => $responseData['success']['error_code']), $where);	
		}
		elseif(!empty($responseData['success'])){
		  DB::update("?:history_additional_data", array('status' => 'success'), $where);
		}
	}


        public static function backupTestProcessor($siteIDs, $params){
		  
		  if(empty($siteIDs)){ return false; }
		  		 
		  $type = 'site';
		  $action = 'backupTest';
		  $requestAction = 'backup_test_site';
		  $events = 1;
		  
                    $historyAdditionalData = array();
                    $historyAdditionalData[] = array('uniqueName' => 'backupTest', 'detailedAction' => 'Backup Test');
		  foreach($siteIDs as $siteID){
			
			$siteData = getSiteData($siteID);
						
			$PRP = array();
			$PRP['requestAction'] 	= $requestAction;
			$PRP['siteData'] 		= $siteData;
			$PRP['type'] 			= $type;
			$PRP['action'] 		= $action;
			$PRP['events'] 		= $events;
			$PRP['historyAdditionalData'] 	= $historyAdditionalData;
			$PRP['doNotExecute'] 			= false;
			$PRP['directExecute'] = true;		
			$PRP['doNotShowUser'] = true;		
			  
			prepareRequestAndAddHistory($PRP);
		 }
	  }
	  
	 public static function backupTestResponseProcessor($historyID, $responseData){
		  
		  responseDirectErrorHandler($historyID, $responseData);		  	  
          $where = array(
		      		'query' =>  "historyID=':historyID'",
		      		'params' => array(
		               ':historyID'=>$historyID
       				)
    			);
    	         
		  if(!empty($responseData['success']['error'])){
			  DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['error_data']), $where);	
		  }
		  elseif(!empty($responseData['success'])){
	            $historyData = DB::getRow("?:history", "type, actionID, siteID", $where);
	            $siteID = $historyData['siteID'];
	            $where2 = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			); 
	            DB::update("?:sites", array('siteTechinicalInfo' => serialize($responseData['success']), 'infoLastUpdate'=>date("Y-m-d H:i:s")), $where2);
	            DB::update("?:history_additional_data", array('status' => 'success'), $where);
		  }
	 }

	 public static function preAddSiteCheckForNetwork(&$params){
	 	$parentSiteID = '';
	 	if (!empty($params['activationKey'])) {
		    $where = array(
			      		'query' =>  "activationKey=':activationKey'",
			      		'params' => array(
			               ':activationKey'=>$params['activationKey']
		   				)
					);
	   		$parentSiteID = DB::getField("?:sites", "siteID", $where);
	 	}
	    if (empty($parentSiteID)) {
		 	$url = trim($params['URL'], '/');
		 	$urlWithoutLastPart = explode('/', $url);
		 	array_pop($urlWithoutLastPart);
		 	$urlWithoutLastPart = implode('/', $urlWithoutLastPart); 
		 	$urlWithoutprotocal = str_ireplace(array('www.', 'http://', 'https://'), '', $url);
		 	 $where = array(
		      		'query' =>  "URL=':URL' OR URL=':urlWithoutLastPart' OR formatedURL=':urlWithoutprotocal'",
		      		'params' => array(
		               ':URL'=>$url,
		               ':urlWithoutLastPart' => $urlWithoutLastPart,
		               ':urlWithoutprotocal' => $urlWithoutprotocal
						)
				);
		 	$parentSiteID = DB::getField("?:multisites", "parentSiteID", $where);
	    }
	    if (!empty($parentSiteID)) {
	    	$params['isNetworkSite'] = 1;
	    	$params['parentSiteID']  = $parentSiteID;
	    }

	 }

	 public static function buildOptionalParams($urlType, $http, $contentType){
	 	if (empty($urlType) || empty($http) || empty($contentType)) {
	 		return false;
	 	}
	 	$connectUsing = array('default','adminURL','siteURL');
	 	$httpVersion = array('auto', 'CURL_HTTP_VERSION_1_0', 'CURL_HTTP_VERSION_1_1', 'CURL_HTTP_VERSION_2_0');
	 	$connectType = array('application/x-www-form-urlencoded', 'multipart/form-data', 'text/plain');

	 	if ($urlType == 'siteURL' && $http == 'CURL_HTTP_VERSION_2_0' && $contentType == 'text/plain') {
	 		return false;
	 	}

	 	if ($urlType != 'siteURL' && $http == 'CURL_HTTP_VERSION_2_0' && $contentType == 'text/plain') {
	 		$return['connectURL'] = 'siteURL';
	 		$return['HTTPVersion'] = 'auto';
	 		$return['contentType'] = 'application/x-www-form-urlencoded';
	 		return $return;
	 	}

	 	if ($http != 'CURL_HTTP_VERSION_2_0' && $contentType == 'text/plain') {
	 		$return['connectURL'] = $urlType;
	 		$return['HTTPVersion'] = array_next_element($httpVersion, $http, true);
	 		$return['contentType'] = 'application/x-www-form-urlencoded';
	 		return $return;
	 	}

	 	if ($contentType != 'text/plain') {
	 		$return['connectURL'] = $urlType;
	 		$return['HTTPVersion'] = $http;
	 		$return['contentType'] = array_next_element($connectType, $contentType);
	 		return $return;
	 	}
	 	return false;

	 }
        
}

manageClients::addClass('manageClientsSites'); 
?>