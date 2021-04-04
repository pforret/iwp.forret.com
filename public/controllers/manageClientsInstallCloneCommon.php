<?php
class manageClientsInstallCloneCommon{
	
	public static function installCloneCommonNewSiteProcessor($siteIDs=array(), $params){
		$type = "installClone";
		$action = "newSite";
		if (!empty($params['isStaging']) && $params['isTestConnection'] == 1) {
			$type = "staging";
			$initResult = self::initStagingTestConnectionProcess($params);
			if(empty($initResult) || (is_array($initResult) && array_key_exists('error', $initResult))){
				return $initResult;
			}
		}
		if(!empty($params['isStaging']) && $params['isTestConnection'] != 1){
			$type = "staging";
			$initResult = self::initStagingProcess($params);
			if(empty($initResult) || (is_array($initResult) && array_key_exists('error', $initResult))){
				return $initResult;
			}
			$actionID = $params['parentActionID'];
			if (empty($actionID)) {
				$actionID = Reg::get('currentRequest.actionID');
			}
		}elseif (!empty($params['isStagingToLive']) && $params['isTestConnection'] != 1) {
			$type = "stagingToLive";
			$initResult = self::initStagingToLiveProccess($params);
			if(empty($initResult) || (is_array($initResult) && array_key_exists('error', $initResult))){
				return $initResult;
			}
			$actionID = $params['parentActionID'];
			if (empty($actionID)) {
				$actionID = Reg::get('currentRequest.actionID');
			}
		} else {
			$actionID = Reg::get('currentRequest.actionID');
		}
		//exit;
		if (!empty($siteIDs) && !empty($params['isExistingSite'])) {
			$where = array(
			'query' =>   "siteID=':siteID'",
				'params' => array(
					':siteID' => $siteIDs[0],
				)
			);
			DB::update('?:sites', "ftpDetails='".serialize($params)."'", $where);

		}
		if(($params['backupURL'] != 'localPackage' && empty($params['cloneFromURL']) && empty($params['sourceSiteID']) ) && ($params['isTestConnection'] != 1) ){//exsiting site to new location
		 	return;
		}
		$showUser = 'Y';
		if($params['isTestConnection'] == 1){
			$action = "testConnection";
			Reg::set('currentRequest.sendAfterAllLoad', true); //if test connection
			$showUser = 'N';
		}
		if (isset($params['newSiteURL'])) {
			$params['newSiteURL'] = addProtocolCommon($params['newSiteURL']);
		} else {
			if (isset($params[$params['stagingType']]['newSiteURL'])) {
				$params[$params['stagingType']]['newSiteURL'] = addProtocolCommon($params[$params['stagingType']]['newSiteURL']);
			}
		}

		set_time_limit(0);
		
		$userID = (isset($GLOBALS['userID']) && !empty($GLOBALS['userID']))?$GLOBALS['userID']:$_SESSION['userID'];
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'newSite', 'detailedAction' => $type, 'successMsg' => '<a href="'.$params['newSiteURL'].'/iwp-clone-log.txt" target="_blank">View log</a>');

		$historyData = array('siteID' => 0, 'actionID' => $actionID, 'userID' => $userID, 'type' => $type, 'action' => $action, 'events' => 1, 'status' => 'pending', 'URL' => $params['newSiteURL'], 'timeout' => 120, 'isPluginResponse' => '0', 'showUser' => $showUser, 'parentHistoryID' => $params['parentHistoryID']);
		$historyID = addHistory($historyData, $historyAdditionalData);
		
		$compactVars = compact('params');
		DB::insert("?:temp_storage", array('type' => 'installCloneCommonNewSite', 'paramID' => $historyID, 'time' => time(), 'data' =>  serialize($compactVars)));
		//rest of functionality will happen in method installCloneCommonNewSitePreProcessor()
	}
	
	public static function initStagingProcess(&$params){
		$type = "staging";
		$action = "newSite";
		
		if(empty($params['stagingType'])){
			return array('error' => 'Staging Clone Error');
		} else{
			$stagingType = $params['stagingType'];
		}
		$toSave = array();
		if($stagingType == 'stagingDomainServer'){
			$defaultFtpSettings = panelRequestManager::getSiteFtpDetails(array('siteID' => $params['siteID']));
			if($defaultFtpSettings && !array_key_exists('error', $defaultFtpSettings)){
				$uniquePrefix = self::uniquePrefixGenerator();
				$defaultFtpSettings['dbPrefix'] = $uniquePrefix;
				$defaultFtpSettings['sourceSiteID'] = $params['siteID'];
				$defaultFtpSettings['siteID'] = $params['siteID'];
				$defaultFtpSettings['remoteFolder'] = $params[$stagingType]['remoteFolder'];
				$defaultFtpSettings['newSiteURL'] = $params[$stagingType]['newSiteURL'];
				$defaultFtpSettings['dbHost'] = $params[$stagingType]['dbHost'];
				$defaultFtpSettings['dbName'] = $params[$stagingType]['dbName'];
				$defaultFtpSettings['dbUser'] = $params[$stagingType]['dbUser'];
				$defaultFtpSettings['dbPassword'] = $params[$stagingType]['dbPassword'];
				$defaultFtpSettings['isSameServerStaging'] = 1;
				$defaultFtpSettings['createdTime'] = time();
				
				if(!empty($params[$stagingType])){ unset($params[$stagingType]); }
				
				$params = array_merge($params, $defaultFtpSettings);
				$toSave[$stagingType] = $defaultFtpSettings;
			} else{
				self::saveWithIsSiteExistingOption($stagingType, $toSave, $params['newSiteURL']);
				return $defaultFtpSettings;
			}
			self::saveWithIsSiteExistingOption($stagingType, $toSave, $params['newSiteURL']);
		} else if($stagingType == 'stagingDefaultServer'){
			$defaultStagingSettings = stagingGetMainStagingFtpDetails();
			if($defaultStagingSettings && !array_key_exists('error', $defaultStagingSettings)){
				$uniquePrefix = self::uniquePrefixGenerator();
				$defaultStagingSettings['dbPrefix'] = $uniquePrefix;
				$defaultStagingSettings['sourceSiteID'] = $params['siteID'];
				$defaultStagingSettings['siteID'] = $params['siteID'];
				$defaultStagingSettings['remoteFolder'] = $params[$stagingType]['remoteFolder'];
				$defaultStagingSettings['newSiteURL'] = $params[$stagingType]['newSiteURL'];
				$defaultStagingSettings['createdTime'] = time();
				if(!empty($params[$stagingType])){ unset($params[$stagingType]); }
	
				$params = array_merge($params, $defaultStagingSettings);
				$toSave[$stagingType] = $defaultStagingSettings;
			} else{
				self::saveWithIsSiteExistingOption($stagingType, $toSave, $params['newSiteURL']);
				return $defaultStagingSettings;
			}
			saveStagingFtpDetails($toSave);
			self::saveWithIsSiteExistingOption($stagingType, $toSave, $params['newSiteURL']);
		} else if($stagingType == 'stagingCustomServer'){
			$uniquePrefix = self::uniquePrefixGenerator();
			$customFtpSettings = $params[$stagingType];
			$customFtpSettings['dbPrefix'] = $uniquePrefix;
			$customFtpSettings['sourceSiteID'] = $params['siteID'];
			$customFtpSettings['siteID'] = $params['siteID'];
			$customFtpSettings['createdTime'] = time();
			
			if(!empty($params[$stagingType])){ unset($params[$stagingType]); }
			
			$params = array_merge($params, $customFtpSettings);
			$toSave[$stagingType] = $customFtpSettings;
			
			self::saveWithIsSiteExistingOption($stagingType, $toSave, $params['newSiteURL']);
		} else if($stagingType == 'stagingCopyToLive'){
			$thisSiteOldStagingSettings = stagingGetStagingFtpDetails($params);
			$tempThisSiteOldStagingSettings = $thisSiteOldStagingSettings;
			if($tempThisSiteOldStagingSettings && !array_key_exists('error', $tempThisSiteOldStagingSettings)){
				foreach($tempThisSiteOldStagingSettings as $key => $tempThisSiteOldStagingSetting){
					foreach($params as $key2 => $param){
						$tempThisSiteOldStagingSettings[$key][$key2] = $params[$key2];
					}
				}
				foreach ($tempThisSiteOldStagingSettings as $key3 => $tempThisSiteOldStagingSetting) {
					unset($tempThisSiteOldStagingSettings[$key3]['stagingCopyToLive']);
					$params = $tempThisSiteOldStagingSettings[$key3];
				}
				self::saveWithIsSiteExistingOption($stagingType, $tempThisSiteOldStagingSettings, $params['newSiteURL']);
			} else{
				return $thisSiteOldStagingSettings;
			}
			if (key($thisSiteOldStagingSettings) == 'stagingDomainServer') {
				$params['isSameServerStaging'] = 1;
			}
		}
		return true;
	}

	public static function initStagingTestConnectionProcess(&$params){
		$type = "staging";
		$action = "newSite";
		
		if(empty($params['stagingType'])){
			return array('error' => 'Staging Clone Error');
		} else{
			$stagingType = $params['stagingType'];
		}
		$toSave = array();
		if($stagingType == 'stagingDomainServer'){
			$defaultFtpSettings = panelRequestManager::getSiteFtpDetails(array('siteID' => $params['siteID']));
			if($defaultFtpSettings && !array_key_exists('error', $defaultFtpSettings)){
				$uniquePrefix = self::uniquePrefixGenerator();
				$defaultFtpSettings['dbPrefix'] = $uniquePrefix;
				$defaultFtpSettings['sourceSiteID'] = $params['siteID'];
				$defaultFtpSettings['siteID'] = $params['siteID'];
				$defaultFtpSettings['remoteFolder'] = $params['remoteFolder'];
				$defaultFtpSettings['newSiteURL'] = $params['newSiteURL'];
				$defaultFtpSettings['dbHost'] = $params['dbHost'];
				$defaultFtpSettings['dbName'] = $params['dbName'];
				$defaultFtpSettings['dbUser'] = $params['dbUser'];
				$defaultFtpSettings['dbPassword'] = $params['dbPassword'];
				$defaultFtpSettings['isSameServerStaging'] = 1;
				$defaultFtpSettings['createdTime'] = time();
				$params = array_merge($params, $defaultFtpSettings);
			}
		} else if($stagingType == 'stagingDefaultServer'){
			$defaultStagingSettings = stagingGetMainStagingFtpDetails();
			if($defaultStagingSettings && !array_key_exists('error', $defaultStagingSettings)){
				$uniquePrefix = self::uniquePrefixGenerator();
				$defaultStagingSettings['dbPrefix'] = $uniquePrefix;
				$defaultStagingSettings['sourceSiteID'] = $params['siteID'];
				$defaultStagingSettings['siteID'] = $params['siteID'];
				$defaultStagingSettings['remoteFolder'] = $params['remoteFolder'];
				$defaultStagingSettings['newSiteURL'] = $params['newSiteURL'];
				$defaultStagingSettings['createdTime'] = time();	
				$params = array_merge($params, $defaultStagingSettings);
			}
		}
		return true;
	}

	public static function initStagingToLiveProccess(&$params){
		$defaultFtpSettings = panelRequestManager::getSiteFtpDetails(array('siteID' => $params['baseSiteID']));
		$defaultFtpSettings['dbPrefix'] = '';
		$defaultFtpSettings['siteID'] = $params['siteID'];
		$defaultFtpSettings['sourceSiteID'] = $params['siteID'];
		$defaultFtpSettings['dbHost'] = $params['dbDetails']['dbHost'];
		$defaultFtpSettings['dbName'] = $params['dbDetails']['dbName'];
		$defaultFtpSettings['dbUser'] = $params['dbDetails']['dbUser'];
		$defaultFtpSettings['dbPassword'] = $params['dbDetails']['dbPassword'];
		$params = array_merge($params, $defaultFtpSettings);
		return $params;
	}


	
	public static function saveWithIsSiteExistingOption($stagingType, $toSave, $thisUrl){
		$isAlreadyExistingSite = false;
		$where = array(
			'query' =>   "URL=':URL'",
				'params' => array(
					':URL'=> $thisUrl,
				)
			);
		$siteAlreadyExists = DB::getRow("?:sites", "siteID", $where);
			if ($stagingType === 'stagingCopyToLive') {
				foreach ($toSave as $key => $value) {
					if($siteAlreadyExists && !empty($siteAlreadyExists['siteID'])){
						$toSave[$key]['isAlreadyExistingSite'] = 'true';
					} else {
						$toSave[$key]['isAlreadyExistingSite'] = '';
					}
				}
			} else {
				if($siteAlreadyExists && !empty($siteAlreadyExists['siteID'])){
					$toSave[$stagingType]['isAlreadyExistingSite'] = 'true';
				} else {
					$toSave[$stagingType]['isAlreadyExistingSite'] = '';
				}
			}

		saveStagingFtpDetails($toSave);
	}
	
	public static function uniquePrefixGenerator(){
		return 'siwp' .  mt_rand (0, 1000000 ) . '_wp_';;
	}

	public static function installCloneCommonNewSitePreProcessor($historyID){

		$where = array(
			'query' =>   "type=':type' AND paramID=':paramID'",
				'params' => array(
					':type'=> 'installCloneCommonNewSite',
					':paramID' => $historyID,
				)
			);
		$compactVars = DB::getField("?:temp_storage", "data", $where);
		DB::delete("?:temp_storage", $where);
		
		$compactVars = unserialize($compactVars);
		@extract($compactVars);
		
		$hostName = trim($params['hostName']);
		$hostUserName = trim($params['hostUserName']); 
		$hostPassword = trim($params['hostPassword']);
		$hostPort = trim($params['hostPort']) ? trim($params['hostPort']) : 22; //trim($params['hostPort']); $port = $ftp_port ? $ftp_port : 22;
		$hostSSL = trim($params['hostSSL']);
		$hostPassive = trim($params['hostPassive']);
        $use_sftp = trim($params['use_sftp']);
		$parts = parse_url($params['newSiteURL']);
		
		$updateWhere = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);
		if(isset($use_sftp) && $use_sftp==1) {
			$conResult = self::initIWPSftpConn($historyID, $params, $sftp);
			if(!$conResult){
				return false;
			}
			
			$return = self::createSftpCloneDirectory($cloneTempPath, $params, $conResult, $historyID);
			if(!$return){
				return false;
			}
			
			if($params['isTestConnection'] != 1){
				if($params['backupURL'] == 'localPackage'){
					$packagePath = installCloneGetWPPackage();
					if(empty($packagePath)){
							updateHistory(array('status' => 'error'), $historyID);
							DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Unable to locate WP package.'), $updateWhere);
							self::installCloneTestLog('Unable to locate WP package.', $historyID, $params['isTestConnection']);
							return false; //array('error' => 'Unable to locate WP package.!'); 
					}
					@$conResult->put('WPPackage.zip', $packagePath,1);
					$uploadWP = 1;

					$oldUser = '';
					$oldURL = '';
				} else if(!empty($params['cloneFromURL'])){
					$oldUser = '';
					$oldURL = '';
				} else{
					$sourceSiteData = getSiteData($params['sourceSiteID'], "true");
					$params['oldUser'] = $sourceSiteData['adminUsername'];
					$params['oldURL'] = $sourceSiteData['URL'];
				}
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
				
			$file_bridge_source 	= "/bridge.txt";  
			$file_bridge_des 		= "/bridge.php";  
			$file_fileSystem 	= "/fileSystem.php";
			$file_pclzip 		= "/class-pclzip.php";
			$file_db                = "/db.php";
			$bridge_path = self::getCloneBridgePath();
			$uploadBridge 	= @ftp_put($connection, $uploadPath.$file_bridge_des, $bridge_path.$file_bridge_source, FTP_ASCII);
			$uploadFS 		= @ftp_put($connection, $uploadPath.$file_fileSystem, $bridge_path.$file_fileSystem, FTP_ASCII);
			$uploadPCL 		= @ftp_put($connection, $uploadPath.$file_pclzip, $bridge_path.$file_pclzip, FTP_ASCII);
			$uploadDB 		= @ftp_put($connection, $uploadPath.$file_db, $bridge_path.$file_db, FTP_ASCII);
			
			if($params['isTestConnection'] != 1){
				$params['oldUser'] = '';
				$params['oldURL'] = '';
				if($params['backupURL'] == 'localPackage'){
					$packagePath = installCloneGetWPPackage();
					if(empty($packagePath)){

						updateHistory(array('status' => 'error'), $historyID);
						DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'Unable to locate WP package.'), $updateWhere);	
						self::installCloneTestLog('Unable to locate WP package.', $historyID, $params['isTestConnection']);
						return false; //array('error' => 'Unable to locate WP package.!'); 
					}
					$uploadWP = @ftp_put($connection, $uploadPath.'/WPPackage.zip', $packagePath, FTP_BINARY);
					$uploadWP = 1;
					
					$oldUser = '';
					$oldURL = '';
				} elseif(!empty($params['cloneFromURL'])){
					$oldUser = '';
					$oldURL = '';
				} else{
					$sourceSiteData = getSiteData($params['sourceSiteID'], 'true');
					$params['oldUser'] = $sourceSiteData['adminUsername'];
					$params['oldURL'] = $sourceSiteData['URL'];
				}
			}
			
			if (!$uploadBridge || !$uploadFS || !$uploadPCL || !$uploadDB || ($params['backupURL'] == 'localPackage' && !$uploadWP && ($params['isTestConnection'] != 1))) {
				updateHistory(array('status' => 'error'), $historyID);
				DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'FTP upload failed.'), $updateWhere);
				self::installCloneTestLog('FTP upload failed.', $historyID, $params['isTestConnection']);
				return false; //array('error' => 'FTP upload failed!'); 
			}

			@ftp_close($connection);
        }
		
		$destinationURL = removeTrailingSlash($params['newSiteURL'])."/clone_controller/bridge.php";
		self::prepareBridgeRequest($historyID, $destinationURL, $params);
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
	
	public static function prepareBridgeRequest($historyID, $destinationURL, $params){
		$newUserPassword = !empty($params['newUserPassword']) ? md5($params['newUserPassword']) : '';
		$excludeFromTarget = array();
		$oneShotdownlaod = false;
		if (defined('EXCLUDE_FROM_CLONE_TARGET_FOLDER') && is_array(EXCLUDE_FROM_CLONE_TARGET_FOLDER)) {
			$excludeFromTarget = EXCLUDE_FROM_CLONE_TARGET_FOLDER;
		}

		if (defined('IWP_CLONE_ONE_SHOT_DOWNLOAD') && IWP_CLONE_ONE_SHOT_DOWNLOAD) {
			$oneShotdownlaod = IWP_CLONE_ONE_SHOT_DOWNLOAD;
		}
		$requestParams = array('dbHost' => $params['dbHost'], 'dbUser' => $params['dbUser'], 'dbPassword' => $params['dbPassword'], 'dbName' => $params['dbName'], 'oldSite' => $params['oldURL'], 'oldUser' => $params['oldUser'], 'newSiteURL' => $params['newSiteURL'], 'newUser' => $params['newUserName'], 'newPassword' => $newUserPassword, 'admin_email' => $params['adminEmail'], 'memorySize' => '256', 'toIWP' => $params['toIWP'],'emptyTargetDir' => $params['emptyCloneDir'], 'backupURL' => $params['backupURL'], 'manualBackupFile' => $params['manualBackupFile'], 'db_table_prefix' => $params['dbPrefix'] , 
		'ftpHost' => $params['hostName'], 
		'ftpPort' => $params['hostPort'],
		'ftpUser' => $params['hostUserName'],
		'ftpPass' => $params['hostPassword'],		
		'ftpKey' => $params['hostKey'],		
		'ftpBase' => '/'.trim($params['remoteFolder'], '/'),
		'ftpSSL' => $params['hostSSL'],
		'ftpPassive' => $params['hostPassive'],
		'excludeFolder' => $excludeFromTarget,
		'isTestConnection' => (!empty($params['isTestConnection'])) ? 1 : 0,
		'extractParentHID' => $historyID,
		'isStaging' => $params['isStaging'],
		'isExistingSite' => $params['isExistingSite'],
		'oneShotdownlaod' => $oneShotdownlaod,
		'isStagingToLive' => $params['isStagingToLive'],
		'isSameServerStaging' => $params['isSameServerStaging'],
		/*, 'owner' => 'infinitewp.com'*/);
		//
		if(isset($params['use_sftp'])) {
			$requestParams['ftpUseSftp'] = $params['use_sftp'];
		}
		if(!empty($params['manualBackupFile'])){
			//BackURL From Manual file, We removeing the ftp path, we send only the file name. Basicly the client side we have the http access only, 
			//so we need to place the backup file by root folder of install new site
			$requestParams['manualBackupFile'] = basename($requestParams['manualBackupFile']);
		}
		if(!empty($params['isDeleteStagingSite'])){
			$requestParams['isDeleteStagingSite'] = true;
		}

		if (empty($requestParams['dbHost']) && (!empty($params['siteID']) || !empty($params['sourceID']) || !empty($params['sourceSiteID']))) {
			// sourceID ---> tositeID && sourceSiteID ----> From SiteID
			$siteID = $params['siteID'];
			if (empty($siteID)) {
				$siteID = $params['sourceID'];
			}
			if (!empty($historyID)) {
				$where = array(
					'query' =>   "historyID=':historyID'",
						'params' => array(
							':historyID' => $historyID,
						)
					);
				$actionID = DB::getField('?:history', 'actionID', $where);
			}else{
				$actionID = Reg::get('currentRequest.actionID');
			}

			$databaseDetails = manageClientsFetch::loadGetDBDetails($actionID, $siteID);
			if (!empty($databaseDetails['dbHost'])) {
				$requestParams['dbHost'] = $databaseDetails['dbHost'];
		    	$requestParams['dbName'] = $databaseDetails['dbName'];
		    	$requestParams['dbUser'] = $databaseDetails['dbUser'];
		    	$requestParams['dbPassword'] = $databaseDetails['dbPassword'];
		    	if (empty($requestParams['db_table_prefix'])) {
		    		$requestParams['db_table_prefix'] = $databaseDetails['db_table_prefix'];
		    	}
			}
		}
		$updateHistoryData = array('param1' => base64_encode(serialize($requestParams)), 'param2' => $params['newSiteURL'], 'status' => 'pending', 'URL' => $destinationURL);
		updateHistory($updateHistoryData, $historyID);
		DB::insert("?:history_raw_details", array('historyID' => $historyID, 'request' => base64_encode(serialize($requestParams)), 'panelRequest' => serialize($_REQUEST)));
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
	
	public static function initIWPSftpConn($historyID ,$params){
		$hostName = trim($params['hostName']);
		$hostUserName = trim($params['hostUserName']); 
		$hostPassword = trim($params['hostPassword']);
		$hostKey = trim($params['hostKey']);
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
		if (!empty($hostKey)) {
			
			$rsa = new  \phpseclib\Crypt\RSA();
			if (false === $rsa->loadKey($hostKey)) {
				updateHistory(array('status' => 'error'), $historyID);
				DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => 'The key provided was not in a valid format, or was corrupt'), $where);
				self::installCloneTestLog('The key provided was not in a valid format, or was corrupt', $historyID, $params['isTestConnection']);
			}
			$hostPassword = $rsa;
	    }
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
	
	public static function createSftpCloneDirectory($cloneTempPath, $params, &$sftp, $historyID){
		$uploadPath = '/'.trim($params['remoteFolder'], '/').'/clone_controller';
		$cloneTempPath = $uploadPath.'/clone_temp';

		$file_bridge_source = "bridge.txt";  
		$file_bridge_des	= "bridge.php";  
		$file_fileSystem 	= "fileSystem.php";  
		$file_pclzip 		= "class-pclzip.php";
		$file_db                = "db.php";
		
		$sftp->mkdir($uploadPath,-1,true);
		// $sftp->chmod(0777, $uploadPath); //this is not works with SuPHP, FCGI or PHP-FPM

		$sftp->mkdir($uploadPath,-1,true);
		// $sftp->chmod(0777, $uploadPath); this is not works with SuPHP, FCGI or PHP-FPM
		
		$sftp->mkdir($cloneTempPath,07777,true);
		$sftp->chmod(0777, $cloneTempPath);
		$sftp->chdir($uploadPath);
		
		
		$bridge_path = self::getCloneBridgePath();
		
		$uploadSftpResult = true;
		$uploadSftpResult = @$sftp->put(basename($file_bridge_des), $bridge_path.$file_bridge_source,1);
		$uploadSftpResult = @$sftp->put(basename($file_fileSystem), $bridge_path.$file_fileSystem,1);
		$uploadSftpResult = @$sftp->put(basename($file_pclzip), $bridge_path.$file_pclzip,1);
		$uploadSftpResult = @$sftp->put(basename($file_db), $bridge_path.$file_db,1);
		
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
	
	public static function installCloneCommonNewSiteResponseProcessor($historyID, $responseData){
		
		$start = '#Status(';
		$end = ')#';
		
		$strBetArray = getStrBetAll($responseData,$start,$end);
		$statusData = unserializeBase64DecodeArray($strBetArray);
		
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
			);

		$finalStateReached = false;
		$responseDataReadable = false;
		$setBreak = false;
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
				} else if($d2 == "success" && $d3 == "multicall"){
					$finalStateReached = true;
					$historyResponseStatus[$historyID] = "multiCallWaiting";
					Reg::set("historyResponseStatus", $historyResponseStatus);
					
					$multiCallResponse = array();
					$multiCallResponse = $d1['options'];
					$multiCallResponse['parentHistoryID'] = (!empty($d1['options']['extractParentHID'])) ? $d1['options']['extractParentHID'] : $historyID;
					updateHistory(array('status' => "multiCallWaiting"), $multiCallResponse['parentHistoryID']);
					if (!empty($d1['options']['DBDetails'])) {
						$dbDetails = $d1['options']['DBDetails'];
						$oldHistoryData = getHistory($multiCallResponse['extractParentHID']);
						$param1 = $oldHistoryData['param1'];
						$param1 = unserialize(base64_decode($param1));

						if (empty($param1['dbHost'])) {
							$param1['dbHost'] = $dbDetails['DB_HOST'];
							$param1['dbUser'] = $dbDetails['DB_USER'];
							$param1['dbPassword'] = $dbDetails['DB_PASSWORD'];
							$param1['dbName'] = $dbDetails['DB_NAME'];
							updateHistory(array('param1' => base64_encode(serialize($param1))), $multiCallResponse['parentHistoryID']);
						}
					}
					self::triggerBridgeExtractMulticall($multiCallResponse, $siteID);
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
				} else if($d2 == "success" && $d3 == 'clone_completed'){
					$finalStateReached = true;
					DB::update("?:history_additional_data", array('status' => 'success'), $where);
				} else if($d2 == "options" && (!empty($d3))){
					$type = DB::getField("?:history","type", $where);
					$d3['isStaging'] = ($type == "staging") ? true : false;
		  			if( $type == 'staging'){
						self::triggerAddSite($historyID, $d3);
					}else{
						self::triggerAddSite('', $d3);

					}
					return;
				}	
			}
		}
		if($responseDataReadable === true && $finalStateReached === false){
			DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
		}
	}
	
	public static function triggerAddSite($parentHistoryID, $siteOptions){
		//Add site Function.
		$thisUrl = trim($siteOptions['URL']);
		$thisUrl = $thisUrl.(substr($thisUrl, -1) == '/' ? '' : '/');
		$parentActionID = getActionIDByHistoryID($parentHistoryID);
		$alternativeUrl = self::buildAlternativeUrl($thisUrl);
		if(empty($alternativeUrl)){
			$alternativeUrl = $thisUrl;
		}
		$where = array(
			'query' =>   "URL=':URL' OR URL=':alternativeUrl' ",
				'params' => array(
					':URL' => $thisUrl,
					':alternativeUrl'=>$alternativeUrl,
				)
			);

		$siteAlreadyExists = DB::getRow("?:sites", "siteID", $where);
		if (!empty($siteOptions['isStaging'])) {
			$isStaging = 1;
		}
		if (!empty($siteOptions['userName']) && !empty($siteAlreadyExists['siteID'])) {
			DB::update("?:sites", array('adminUsername' => $siteOptions['userName']), $where);
		}
		$params = array('URL' => $siteOptions['URL'], 'username' => $siteOptions['userName'], 'activationKey' => $siteOptions['activationKey'], 'parentHistoryID' => $parentHistoryID, 'doNotShowUser' => self::isHideAddSiteProcessForStaging($siteOptions['isStaging']), 'actionID' => $parentActionID, 'isStaging' => $isStaging, 'iwp_admin_version' => '2.10.1');
		

		if($siteAlreadyExists && !empty($siteAlreadyExists['siteID'])){
			$_POST = array('action' => 'readdSite', 
				'args' => array(
				'params' => $params, 
				'siteIDs' => array( 0 => $siteAlreadyExists['siteID']), 
				)
			);
		} else{
			$_POST = array('action' => 'addSite', 
				'args' => array(
				'params' => $params, 
					'siteIDs' => array(),
				)
			);
		}
		panelRequestManager::handler($_POST);
		if(!empty($siteOptions['isStaging'])){
			addNotification($type='N', $title='STAGING COMPLETE', $message='You have successfully created the staging site.<br><a href="'.$siteOptions['URL'].'/iwp-clone-log.txt" target="_blank">View log</a>', $state='U', $callbackOnClose='', $callbackReference='');
			self::stagingInsertIntoFireQueue();
		} else {
			self::installCloneInsertIntoFireQueue();
		}
	}
	public static function buildAlternativeUrl($thisUrl){
		$getUrlSchema = parse_url($thisUrl);
		$urlSchema = $getUrlSchema['scheme'];
		if($urlSchema == 'https'){
			return str_replace( 'https://', 'http://', $thisUrl );
		}elseif($urlSchema == 'http'){
			return str_replace( 'http://', 'https://', $thisUrl );
		}else{
			return $thisUrl;
		}

	}
	public static function stagingInsertIntoFireQueue(){
		$requiredData['stagingCompleted'] = array(
			'getSites' => '1',
			'stagingGetStagingBaseIDs' => '1'
		);
		panelRequestManager::insertFireQueue($requiredData);
	}
	public static function installCloneInsertIntoFireQueue(){
		$requiredData['installCloneCompleted'] = array(
			'getGroupsSites' => '1',
			'getSites' => '1',
			'getSitesList' => '1',
		);
		panelRequestManager::insertFireQueue($requiredData);
	}
	public static function isHideAddSiteProcessForStaging($isStaging = null){
		$hideSiteVar = false;
		if(!empty($isStaging)){
			$hideSiteVar = true;
		}
		return $hideSiteVar;
	}
	
	public static function triggerBridgeExtractMulticall($data, $siteID){
		$allParams = array('action' => 'bridgeExtractMulticall', 'args' => array('params' => array('responseData' => $data, 'extractParentHID' => $data['parentHistoryID']), 'siteIDs' => array($siteID)));
		panelRequestManager::handler($allParams);
		
	}
	
	public static function bridgeExtractMulticallProcessor($siteIDs, $params, $extras){
		$type = "installClone";
		$action = "bridgeExtractMulticall";
		$requestAction = "bridgeExtractMulticall";
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
		
		$getCount = DB::getField("?:history", "count(historyID)", "type='installClone' AND action = 'bridgeExtractMulticall' AND parentHistoryID = '".$params['extractParentHID']."'" );
		if($getCount >= 500){
			updateHistory(array('status' => 'error', 'error' => 'max_trigger_calls_reached'), $params['extractParentHID'], array('status' => 'error', 'error' => 'max_trigger_calls_reached', 'errorMsg' => 'Multi-call limit reached.'));
			return;
		}
		
		if(DB::getExists("?:history", "historyID", "type='installClone' AND action = 'bridgeExtractMulticall' AND parentHistoryID = '".$params['extractParentHID']."' AND status not IN('completed', 'error', 'netError')")){
			return;
			
		}
		
		$oldHistoryData = getHistory($params['extractParentHID'], true);
		
		$requestParams = array('mechanism' => 'multiCall','extractParentHID' => $params['extractParentHID'], 'responseData' => $params['responseData'], 'param1' => $oldHistoryData['param1']);
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => "bridgeExtractTrigger", 'detailedAction' => $action);
		
		$doNotShowUser = true;
			
		$siteData['connectURL'] = 'siteURL';
		$siteData['URL'] = $oldHistoryData['URL'];
		$siteData['siteID'] = $oldHistoryData['siteID'];
				  		
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
	
	public static function bridgeExtractMulticallResponseProcessor($historyID, $responseData){
		
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
					self::triggerBridgeExtractMulticall($multiCallResponse, 0);
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
					if ($type != 'staging') {
						$param1 = unserialize(base64_decode(DB::getField("?:history","param1", $updateWhere)));
						$URL = trim($param1['newSiteURL'],'/');
						addNotification($type='N', $title='CLONE COMPLETED', $message='You have successfully created the clone site.<br><a href="'.$URL.'/iwp-clone-log.txt" target="_blank">View log</a>', $state='U', $callbackOnClose='', $callbackReference='');
					}
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
						self::triggerAddSite($historyData['parentHistoryID'], $d3);
					}else{
						self::triggerAddSite('', $d3);

					}
					return;
				}	
			}
		}
		
		if($responseDataReadable === true && $finalStateReached === false){
			DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
		}
	}
	
	public static function installCloneBackupNowProcessor($siteIDs, $params){
		$type = "installClone";
		$action = "installCloneBackupNow";
		$config = $params['config'];
		$timeout = (20 * 60);//20 mins
		$requestAction = "scheduled_backup";
		if($config['mechanism'] == 'advancedBackup'){
			$requestAction = "new_scheduled_backup";
		}
		$exclude = explode(',', $config['exclude']);
		$include = explode(',', $config['include']);			
		array_walk($exclude, 'trimValue');
		array_walk($include, 'trimValue');
		
		if(!empty($params['isStaging'])){
			$type = "staging";
			$exclude = array(0=>'php.ini', 'iwp-clone-log.txt');
		}elseif(!empty($params['isStagingToLive'])){
			$type = "stagingToLive";
			$exclude = array(0=>'php.ini', 'iwp-clone-log.txt','robots.txt','.htaccess.litespeed');
		}
		
		$siteData = getSiteData($siteIDs, 'true');
		$requestParams = array('task_name' => 'Backup Now', 'mechanism' => $config['mechanism'], 'args' => array('type' => $type, 'action' => $action, 'what' => 'full', 'optimize_tables' => '', 'exclude' => $exclude, 'exclude_file_size' => (int)$config['excludeFileSize'], 'exclude_extensions' => $config['excludeExtensions'], 'include' => $include, 'del_host_file' => '','fail_safe_db' => $config['fail_safe_check_DB'], 'fail_safe_files' => $config['fail_safe_check_files'], 'disable_comp' => '', 'limit' => '5', 'backup_name' => 'Backup for Clone'));

		if(!empty($params['isStaging'])){
			$requestParams = array('task_name' => 'Backup Now', 'mechanism' => $config['mechanism'], 'args' => array('type' => $type, 'action' => $action, 'what' => 'full', 'optimize_tables' => '', 'exclude' => $exclude, 'exclude_file_size' => (int)$config['excludeFileSize'], 'exclude_extensions' => $config['excludeExtensions'], 'include' => $include, 'del_host_file' => '','fail_safe_db' => $config['fail_safe_check_DB'], 'fail_safe_files' => $config['fail_safe_check_files'], 'disable_comp' => '', 'limit' => '5', 'backup_name' => 'Backup for staging'));
		}

		if(!empty($params['isStagingToLive'])){
			$requestParams = array('task_name' => 'Backup Now', 'mechanism' => $config['mechanism'], 'args' => array('type' => $type, 'action' => $action, 'what' => 'full', 'optimize_tables' => '', 'exclude' => $exclude, 'exclude_file_size' => (int)$config['excludeFileSize'], 'exclude_extensions' => $config['excludeExtensions'], 'include' => $include, 'del_host_file' => '','fail_safe_db' => $config['fail_safe_check_DB'], 'fail_safe_files' => $config['fail_safe_check_files'], 'disable_comp' => '', 'limit' => '5', 'backup_name' => 'Backup for live'));
		}
		
		if($config['mechanism'] == "multiCall")
		{
			//this function set the multicall options value from config.php if available 
			setMultiCallOptions($requestParams);
		}
			 		 			  
		$historyAdditionalData = array();
		$historyAdditionalData[] = array('uniqueName' => 'Backup clone', 'detailedAction' => $type);
			  
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
		$PRP['sendAfterAllLoad'] = true;
		
		prepareRequestAndAddHistory($PRP);
	}
	
	public static function installCloneBackupNowResponseProcessor($historyID, $responseData){
		
		responseDirectErrorHandler($historyID, $responseData);
		if(empty($responseData['success'])){
			return false;
		}

		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
		);
		
		if(!empty($responseData['success']['error'])){
			DB::update("?:history_additional_data", array('status' => 'error', 'errorMsg' => $responseData['success']['error']), $where);	
			return array('status' => 'error', 'errorMsg' => ' backup error ');
		}
		
		$historyData = DB::getRow("?:history", "*", $where);
		$siteID = $historyData['siteID'];
		
		if(!empty($responseData['success'])){
			if(!empty($responseData['success']['success']['nextFunc']) || !empty($responseData['success']['success'])){
				$historyResponseStatus[$historyID] = "multiCallWaiting";
				Reg::set("historyResponseStatus", $historyResponseStatus);
				if (!empty($responseData['success']['wp_content_url'])) {
					$wp_content_url = $responseData['success']['wp_content_url'];
					$log = $responseData['success']['backup_id'];
					DB::update("?:history_additional_data", array('successMsg' => "<a class='view_on_site' href='".rtrim($wp_content_url,'/')."/infinitewp/backups/log.".$log.".txt' target='_blank' > View log</a>"), $where);
				}
				updateHistory(array('status' => "multiCallWaiting"), $historyID);
				manageClientsBackup::triggerRecheck($responseData, $siteID);
			} else{
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
				$siteBackupsTemp = $responseData['success']['task_results'];	
				$siteStats = DB::getRow("?:site_stats", "stats", "siteID=".$siteID);
				$siteStats = unserialize(base64_decode($siteStats['stats']));
				$siteStats["iwp_backups"]["Backup Now"] = $siteBackupsTemp;
				
				$siteStats = base64_encode(serialize($siteStats));
				
				DB::update("?:site_stats", array('stats' => $siteStats), "siteID=".$siteID);
			}	
		}
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
	
	public static function getCloneBridgePath(){
		return APP_ROOT.'/includes/bridge/';
	}
	
	public static function removeStagingSiteAfterUpdate($historyID, $siteID){
		$where = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $historyID,
				)
		);
		$thisHistoryData = DB::getRow("?:history", "parentHistoryID", $where);
		$parentHistoryID = $thisHistoryData['parentHistoryID'];
		if(!empty($parentHistoryID)){
			$parentHistoryIDWhere = array(
			'query' =>   "historyID=':historyID'",
				'params' => array(
					':historyID' => $parentHistoryID,
				)
			);
			$thisHistoryData = DB::getRow("?:history", "type, action, siteID", $parentHistoryIDWhere);
			if($thisHistoryData['type'] == 'staging'){
				$siteStagingData = stagingGetStagingFtpDetails(array('siteID' => $thisHistoryData['siteID']));
				foreach ($siteStagingData as $key => $data) {
					$siteAlreadyExists = $data['isAlreadyExistingSite'];
				}
				if(empty($siteAlreadyExists) && $siteAlreadyExists != true){
					manageClientsSites::removeSiteProcessor(array( 0 => $siteID), array('iwpPluginDeactivate' => 1, 'doNotShowUser' => true));
					return true;
				}
			}
		}
		return false;
	}

	public static function triggerInstallCloneCommonNewSite($historyID, $historyData, $responseData, $siteID){

		$parentActionID = DB::getField('?:history', 'actionID', 'historyID = '.$historyData['parentHistoryID']);
		$parentHistoryReqData = DB::getRow("?:history_raw_details", "panelRequest", "historyID='".$historyData['parentHistoryID']."'");
		$parentHistoryReqData = unserialize($parentHistoryReqData['panelRequest']);
		$isStaging = $parentHistoryReqData['args']['params']['isStaging'];
		$isStagingToLive = $parentHistoryReqData['args']['params']['isStagingToLive'];
		$isBackupBeforeUpdate= $parentHistoryReqData['args']['params']['isBackupBeforeUpdate'];
		$stagingType = $parentHistoryReqData['args']['params']['stagingType'];
		$stagingTypeDets = $parentHistoryReqData['args']['params'][$stagingType];
		if(!empty($isStaging)){
			$thisBackupDetails = $responseData['success']['task_results'][$historyData['parentHistoryID']];
			$thisBackupDetails['server']['file_url'] = getFullWPURL($siteID, $thisBackupDetails['server']['file_url']);
			if($stagingType == 'stagingDomainServer'){
				$stagingTypeDets['dbHost'] = $thisBackupDetails['server']['dbHost'];
				$stagingTypeDets['dbName'] = $thisBackupDetails['server']['dbName'];
				$stagingTypeDets['dbUser'] = $thisBackupDetails['server']['dbUser'];
				$stagingTypeDets['dbPassword'] = $thisBackupDetails['server']['dbPassword'];
			}
			$allParams = array('action' => 'installCloneCommonNewSite', 'args' => array('params' => array('backupURL' => $thisBackupDetails['server']['file_url'], 'isStaging' => 1, 'stagingType' => $stagingType, $stagingType => $stagingTypeDets, 'siteID' => $siteID, 'parentHistoryID' => $historyData['parentHistoryID'], 'parentActionID' =>$parentActionID)));
		}
		elseif(!empty($isBackupBeforeUpdate)){
			$allParams = $parentHistoryReqData['args']['params']['toBeUpdated'];
		}
		elseif (!empty($isStagingToLive)) {
			$thisBackupDetails = $responseData['success']['task_results'][$historyData['parentHistoryID']];
			$thisBackupDetails['server']['file_url'] = getFullWPURL($siteID, $thisBackupDetails['server']['file_url']);
			$dbDetails['dbHost'] = $thisBackupDetails['server']['dbHost'];
			$dbDetails['dbName'] = $thisBackupDetails['server']['dbName'];
			$dbDetails['dbUser'] = $thisBackupDetails['server']['dbUser'];
			$dbDetails['dbPassword'] = $thisBackupDetails['server']['dbPassword'];
			$defaultFtpSettings = panelRequestManager::getSiteFtpDetails(array('siteID' => $parentHistoryReqData['args']['params']['baseSiteID']));
			if (!empty($defaultFtpSettings) && !empty($defaultFtpSettings['hostName'])) {
				
				$allParams = array('action' => 'installCloneCommonNewSite', 'args' => array('params' => array('backupURL' => $thisBackupDetails['server']['file_url'], 'isStagingToLive' => 1, 'dbDetails' => $dbDetails, 'siteID' => $siteID, 'baseSiteID' => $parentHistoryReqData['args']['params']['baseSiteID'], 'parentHistoryID' => $historyData['parentHistoryID'], 'parentActionID' =>$parentActionID)));
			}else{
				$allParams = array('action' => 'installCloneNewExistingSite', 'args' => array('siteIDs' => array($parentHistoryReqData['args']['params']['baseSiteID']),'params' => array('backupURL' => $thisBackupDetails['server']['file_url'], 'isStagingToLive' => 1, 'dbDetails' => $dbDetails, 'siteID' => $parentHistoryReqData['args']['params']['baseSiteID'], 'baseSiteID' => $parentHistoryReqData['args']['params']['baseSiteID'], 'parentHistoryID' => $historyData['parentHistoryID'], 'parentActionID' =>$parentActionID)));
			}
		}
		else{
			return false;
		}
		return $allParams;
	}

		public static function installCloneNewExistingSiteProcessor($siteIDs, $params){
			$type = "installClone";
			$action = "newExistingSite";
			$requestAction = "file_editor_upload";
			if (!empty($params['isStagingToLive'])) {
				$type = "stagingToLive";
				$actionID = $params['parentActionID'];
				if (empty($actionID)) {
					$actionID = Reg::get('currentRequest.actionID');
				}
			}
			$historyAdditionalData = array();
			$events=1;
			$file = APP_ROOT."/includes/bridge/bridge.txt";
			$fileName = 'bridge.php';
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
				$siteData = getSiteData(intval($siteID), 'true');
				$historyAdditionalData[] = array('uniqueName' => 'existingSite', 'detailedAction' => $type, 'successMsg' => '<a href="'.$siteData['URL'].'/iwp-clone-log.txt" target="_blank">View log</a>');
				$requestParams = array('filePath'=>array('clone_controller', 'bridge.php'),'folderPath'=>'root','fileContent'=>$fileContentEncode,'ext'=>$ext, 'fileName'=> $fileName);
				$PRP = array();
				$siteData['connectURL'] = 'siteURL';
				$PRP['requestAction'] 	= $requestAction;
				$PRP['siteData'] 		= $siteData;
				$PRP['type'] 			= $type;
				if (!empty($actionID)) {
					$PRP['actionID'] 	= $actionID;
					$PRP['parentHistoryID'] = $params['parentHistoryID'];
				}
				$PRP['action'] 			= $action;
				$PRP['requestParams'] 	= $requestParams;
				$PRP['directExecute'] 	= false;
				$PRP['events'] 			= $events;
				$PRP['sendAfterAllLoad'] = true;
				$PRP['historyAdditionalData'] 	= $historyAdditionalData;
				$historyID = prepareRequestAndAddHistory($PRP);
			}
			DB::insert("?:temp_storage", array('type' => 'installCloneCommonNewSite', 'paramID' => $historyID, 'time' => time(), 'data' =>  serialize($params)));
		}

		public static function installCloneNewExistingSiteResponseProcessor($historyID, $responseData){
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
			
				$historyResponseStatus[$historyID] = "multiCallWaiting";
				Reg::set("historyResponseStatus", $historyResponseStatus);
				
				updateHistory(array('status' => "multiCallWaiting"), $historyID);
				self::installCloneTriggerUploadBridge($historyID, array('siteID' => $siteID, 'parentHistoryID' => $historyID));
				
			}
		}

		public static function installCloneTriggerUploadBridge($historyID, $params){
			$fileName = self::findNextFileToUpload($historyID);
			if ($fileName == false) {
				$allParams = array('action' => 'installCloneExistingSiteCloneRun', 'args' => array('params' => array('parentHistoryID' => $params['parentHistoryID']), 'siteIDs' => array($params['siteID'])));
			}else{
				$allParams = array('action' => 'installCloneTriggerUploadBridge', 'args' => array('params' => array('fileName' => $fileName, 'parentHistoryID' => $params['parentHistoryID']), 'siteIDs' => array($params['siteID'])));
			}
			panelRequestManager::handler($allParams);
		}

		public static function installCloneTriggerUploadBridgeProcessor($siteIDs, $params){
			$type = "installClone";
			$action = "uploadBridge";
			
			$fileName = $params['fileName'];
			$requestAction = "file_editor_upload";
			$parentHistoryID = $params['parentHistoryID'];
			$historyAdditionalData = array();
			$historyAdditionalData[] = array('uniqueName' => 'newSite', 'detailedAction' => $type);
			$events=1;
			$file = APP_ROOT."/includes/bridge/".$fileName;
			// $fileOrig = substr($file,0,-4);
			$ext = substr($file,strrpos($file, '.')+1);
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
				$siteData = getSiteData(intval($siteID), 'true');
				$requestParams = array('filePath'=>array('clone_controller', $fileName),'folderPath'=>'root','fileContent'=>$fileContentEncode,'ext'=>$ext, 'fileName'=> $fileName);
				$PRP = array();
				$siteData['connectURL'] = 'siteURL';
				$PRP['requestAction'] 	= $requestAction;
				$PRP['siteData'] 		= $siteData;
				$PRP['type'] 			= $type;
				$PRP['action'] 			= $action;
				$PRP['requestParams'] 	= $requestParams;
				$PRP['directExecute'] 	= false;
				$PRP['events'] 			= $events;
				$PRP['doNotShowUser'] 	= true;
				$PRP['sendAfterAllLoad'] = true;
				$PRP['parentHistoryID'] = $parentHistoryID;
				$PRP['historyAdditionalData'] 	= $historyAdditionalData;
				prepareRequestAndAddHistory($PRP);
			}
		}

		public static function installCloneTriggerUploadBridgeResponseProcessor($historyID, $responseData){
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
			
				DB::update("?:history_additional_data", array('status' => 'success'), $where);
				DB::update("?:history", array('status' => 'completed'), $where);
				self::installCloneTriggerUploadBridge($historyID, array('siteID' => $siteID, 'parentHistoryID' => $historyData['parentHistoryID']));
				
			}
		}

		public static function findNextFileToUpload($historyID){
			$where = array(
	      		'query' =>  "historyID=':historyID'",
	      		'params' => array(
	               ':historyID'=>$historyID
					)
			);
			$fileName = false;
			$previousRequest = DB::getField("?:history_raw_details", "request", $where);
			$previousRequest = unserialize(base64_decode($previousRequest));
			$previousFileName = $previousRequest['params']['fileName'];
			if ($previousFileName == 'bridge.php') {
				$fileName = 'class-pclzip.php';
			}elseif ($previousFileName == 'class-pclzip.php') {
				$fileName = 'db.php';
			}elseif ($previousFileName == 'db.php') {
				$fileName = 'fileSystem.php';
			}
			return $fileName;
		}

		public static function installCloneExistingSiteCloneRunProcessor($siteIDs, $params){
			$type = "installClone";
			$action = "cloneRun";
			$where = array(
	      		'query' =>  "paramID=':paramID'",
	      		'params' => array(
	               ':paramID'=>$params['parentHistoryID']
					)
			);
			$parentHistoryID = $params['parentHistoryID'];
			$panelRequest = DB::getField("?:temp_storage", 'data', $where);
			$params = unserialize($panelRequest);
			$siteData = getSiteData($siteIDs[0], 'true');
			$destinationURL = trim($siteData['URL'], '/').'/clone_controller/bridge.php';
			$parentActionID = Reg::get('currentRequest.actionID');
			$newUserPassword = !empty($params['newUserPassword']) ? md5($params['newUserPassword']) : '';
			$excludeFromTarget = array();
			$oneShotdownlaod = false;
			if (defined('EXCLUDE_FROM_CLONE_TARGET_FOLDER') && is_array(EXCLUDE_FROM_CLONE_TARGET_FOLDER)) {
				$excludeFromTarget = EXCLUDE_FROM_CLONE_TARGET_FOLDER;
			}

			if (defined('IWP_CLONE_ONE_SHOT_DOWNLOAD') && IWP_CLONE_ONE_SHOT_DOWNLOAD) {
				$oneShotdownlaod = IWP_CLONE_ONE_SHOT_DOWNLOAD;
			}
			$requestParams = array('dbHost' => $params['dbHost'], 'dbUser' => $params['dbUser'], 'dbPassword' => $params['dbPassword'], 'dbName' => $params['dbName'], 'oldSite' => $params['oldURL'], 'oldUser' => $params['oldUser'], 'newSiteURL' => $siteData['URL'], 'newUser' => $params['newUserName'], 'newPassword' => $newUserPassword, 'admin_email' => $params['adminEmail'], 'memorySize' => '256', 'toIWP' => 1,'emptyTargetDir' => $params['emptyCloneDir'], 'backupURL' => $params['backupURL'], 'manualBackupFile' => $params['manualBackupFile'], 'db_table_prefix' => $params['dbPrefix'] , 
			'ftpHost' => $params['hostName'], 
			'ftpPort' => $params['hostPort'],
			'ftpUser' => $params['hostUserName'],
			'ftpPass' => $params['hostPassword'],		
			'ftpBase' => '/'.trim($params['remoteFolder'], '/'),
			'ftpSSL' => $params['hostSSL'],
			'ftpPassive' => $params['hostPassive'],
			'excludeFolder' => $excludeFromTarget,
			'isTestConnection' => (!empty($params['isTestConnection'])) ? 1 : 0,
			'extractParentHID' => $historyID,
			'isStaging' => $params['isStaging'],
			'isStagingToLive' => $params['isStagingToLive'],
			'isExistingSite' => $params['isExistingSite'],
			'oneShotdownlaod' => $oneShotdownlaod,
			'isSameServerStaging' => $params['isSameServerStaging'],
			/*, 'owner' => 'infinitewp.com'*/);
			//
			if(isset($params['use_sftp'])) {
				$requestParams['ftpUseSftp'] = $params['use_sftp'];
			}
			if($requestParams['backupURL'] == 'localPackage'){//FreshWP to Existing site
				$requestParams['backupURL'] = installCloneGetWPPackage(true);
			}
			if(!empty($params['manualBackupFile'])){
				//BackURL From Manual file, We removeing the ftp path, we send only the file name. Basicly the client side we have the http access only, 
				//so we need to place the backup file by root folder of install new site
				$requestParams['manualBackupFile'] = basename($requestParams['manualBackupFile']);
			}
			if(!empty($params['isDeleteStagingSite'])){
				$requestParams['isDeleteStagingSite'] = true;
			}

			if (empty($requestParams['dbHost']) && (!empty($siteIDs[0]))) {
				$siteID = $siteIDs[0];
				if (!empty($parentHistoryID)) {
					$where = array(
						'query' =>   "historyID=':historyID'",
							'params' => array(
								':historyID' => $parentHistoryID,
							)
						);
					$actionID = DB::getField('?:history', 'actionID', $where);
				}else{
					$actionID = Reg::get('currentRequest.actionID');
				}

				$databaseDetails = manageClientsFetch::loadGetDBDetails($actionID, $siteID);
				if (!empty($databaseDetails['dbHost'])) {
					$requestParams['dbHost'] = $databaseDetails['dbHost'];
			    	$requestParams['dbName'] = $databaseDetails['dbName'];
			    	$requestParams['dbUser'] = $databaseDetails['dbUser'];
			    	$requestParams['dbPassword'] = $databaseDetails['dbPassword'];
			    	if (empty($requestParams['db_table_prefix'])) {
			    		$requestParams['db_table_prefix'] = $databaseDetails['db_table_prefix'];
			    	}
				}
			}

			$userID = (isset($GLOBALS['userID']) && !empty($GLOBALS['userID']))?$GLOBALS['userID']:$_SESSION['userID'];
			$historyAdditionalData = array();
			$historyAdditionalData[] = array('uniqueName' => 'cloneRun', 'detailedAction' => $type);

			$historyData = array('siteID' => $siteIDs[0], 'actionID' => $parentActionID, 'userID' => $userID, 'type' => $type, 'action' => $action, 'events' => 1, 'status' => 'pending',  'timeout' => 120, 'isPluginResponse' => '0', 'showUser' => 'N', 'parentHistoryID' => $params['parentHistoryID'], 'param1' => base64_encode(serialize($requestParams)), 'param2' => $params['newSiteURL'], 'URL' => $destinationURL, 'parentHistoryID'=> $parentHistoryID);
			$historyID = addHistory($historyData, $historyAdditionalData);
			$updateHistoryData = array('param1' => base64_encode(serialize($requestParams)), 'param2' => $params['newSiteURL'], 'URL' => $destinationURL);
			updateHistory($updateHistoryData, $parentHistoryID);
			DB::insert("?:history_raw_details", array('historyID' => $historyID, 'request' => base64_encode(serialize($requestParams)), 'panelRequest' => serialize($params)));

		}

		public static function installCloneExistingSiteCloneRunResponseProcessor($historyID, $responseData){
			$start = '#Status(';
			$end = ')#';
			
			$strBetArray = getStrBetAll($responseData,$start,$end);
			$statusData = unserializeBase64DecodeArray($strBetArray);
			
			$where = array(
				'query' =>   "historyID=':historyID'",
					'params' => array(
						':historyID' => $historyID,
					)
				);
			$parentHistoryID = DB::getField("?:history", 'parentHistoryID', $where);
			$where = array(
				'query' =>   "historyID=':historyID'",
					'params' => array(
						':historyID' => $parentHistoryID,
					)
				);
			$finalStateReached = false;
			$responseDataReadable = false;
			$setBreak = false;
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
						DB::update("?:history", array('status' => 'error'), $where);
					} else if($d2 == "success" && $d3 == "multicall"){
						$finalStateReached = true;
						$historyResponseStatus[$parentHistoryID] = "multiCallWaiting";
						Reg::set("historyResponseStatus", $historyResponseStatus);
						
						$multiCallResponse = array();
						$multiCallResponse = $d1['options'];
						$multiCallResponse['parentHistoryID'] = $parentHistoryID;
						// updateHistory(array('status' => "multiCallWaiting"), $where);
						$where = array(
							'query' =>   "historyID=':historyID'",
								'params' => array(
									':historyID' => $historyID,
								)
							);
						DB::update("?:history_additional_data", array('status' => 'success'), $where);
						self::triggerBridgeExtractMulticallExisting($multiCallResponse, $siteID);
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
					} else if($d2 == "success" && $d3 == 'clone_completed'){
						$finalStateReached = true;
						DB::update("?:history_additional_data", array('status' => 'success'), $where);
						DB::update("?:history", array('status' => 'completed'), $where);
						$where = array(
							'query' =>   "historyID=':historyID'",
								'params' => array(
									':historyID' => $historyID,
								)
							);
						DB::update("?:history_additional_data", array('status' => 'success'), $where);
						
					} else if($d2 == "options" && (!empty($d3))){
						$type = DB::getField("?:history","type", $where);
						$d3['isStaging'] = ($type == "staging") ? true : false;
			  			if( $type == 'staging'){
							self::triggerAddSite($historyID, $d3);
						}else{
							self::triggerAddSite('', $d3);

						}
						return;
					}	
				}
			}
			if($responseDataReadable === true && $finalStateReached === false){
				DB::update("?:history", array('status' => 'error'), $where);
				DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
			}
		}

		public static function triggerBridgeExtractMulticallExisting($data, $siteID){
			$allParams = array('action' => 'cloneRunMulticall', 'args' => array('params' => array('responseData' => $data, 'extractParentHID' => $data['parentHistoryID']), 'siteIDs' => array($siteID)));
			panelRequestManager::handler($allParams);
			
		}
		
		public static function cloneRunMulticallProcessor($siteIDs, $params, $extras){
			$type = "installClone";
			$action = "cloneRunMulticall";
			$requestAction = "bridgeExtractMulticall";
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
			
			$getCount = DB::getField("?:history", "count(historyID)", "type='installClone' AND action = 'cloneRunMulticall' AND parentHistoryID = '".$params['extractParentHID']."'" );
			if($getCount >= 500){
				updateHistory(array('status' => 'error', 'error' => 'max_trigger_calls_reached'), $params['extractParentHID'], array('status' => 'error', 'error' => 'max_trigger_calls_reached', 'errorMsg' => 'Multi-call limit reached.'));
				return;
			}
			
			if(DB::getExists("?:history", "historyID", "type='installClone' AND action = 'cloneRunMulticall' AND parentHistoryID = '".$params['extractParentHID']."' AND status not IN('completed', 'error', 'netError')")){
				return;
				
			}
			
			$oldHistoryData = getHistory($params['extractParentHID'], true);
			
			$requestParams = array('mechanism' => 'multiCall','extractParentHID' => $params['extractParentHID'], 'responseData' => $params['responseData'], 'param1' => $oldHistoryData['param1']);
			$historyAdditionalData = array();
			$historyAdditionalData[] = array('uniqueName' => "cloneRunMulticall", 'detailedAction' => $action);
			
			$doNotShowUser = true;
			$siteData = getSiteData($oldHistoryData['siteID'], 'true');
			$siteData['connectURL'] = 'siteURL';
			$siteData['URL'] = trim($siteData['URL'], '/').'/clone_controller/bridge.php';
			$siteData['siteID'] = $oldHistoryData['siteID'];
					  		
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
		
		public static function cloneRunMulticallResponseProcessor($historyID, $responseData){
			
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
						DB::update("?:history", array('status' => 'error'), $where);
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
						$multiCallResponse['parentHistoryID'] = (!empty($d1['options']['extractParentHID'])) ? $d1['options']['extractParentHID'] : $fullHistoryData['parentHistoryID'];
						self::triggerBridgeExtractMulticallExisting($multiCallResponse, 0);
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
						if ($type != 'staging') {
							$param1 = unserialize(base64_decode(DB::getField("?:history","param1", $updateWhere)));
							$URL = trim($param1['newSiteURL'],'/');
							addNotification($type='N', $title='CLONE COMPLETED', $message='You have successfully created the clone site.<br><a href="'.$URL.'/iwp-clone-log.txt" target="_blank">View log</a>', $state='U', $callbackOnClose='', $callbackReference='');
						}
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
							self::triggerAddSite($historyData['parentHistoryID'], $d3);
						}else{
							self::triggerAddSite('', $d3);

						}
						return;
					}	
				}
			}
			
			if($responseDataReadable === true && $finalStateReached === false){
				DB::update("?:history_additional_data", array('status' => 'error', 'error' => 'error', 'errorMsg' => 'An unknown error occured in Install/Clone process.'), $where);
			}
		}
}
manageClients::addClass('manageClientsInstallCloneCommon');
