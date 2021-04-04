<?php

function emailCampaignUpdateCheck($result){

	if (empty($result['emailCampaign'])) {
		return;
	}
	$emailCampaign = $result['emailCampaign'];
	foreach ($emailCampaign as $key => $value) {
		$where = array(
		      		'query' =>  "id = ':id'",
		      		'params' => array(
		               ':id'=>$value['id']
       				)
    			);
		$isExist = DB::getField("?:email_campaign", "id", $where);
		$value = calculateEmailCampaignScheduleTime($value);
		if (empty($isExist)) {
			DB::insert("?:email_campaign", $value);
		}else{
			DB::update("?:email_campaign", $value, $where);
		}

	}

}

function calculateEmailCampaignScheduleTime($campaign){
	$installedTime = getOption('installedTime');
	$timeInterval = $campaign['timeInterval'];
	$extendTime  = 0;
	if (empty($installedTime) || empty($timeInterval)) {
		return $campaign;
	}
	$trialPanelExtended = getOption('trialPanelExtended');
	if (!empty($trialPanelExtended)) {
		if ($campaign['action'] == 'trialEndTomorrow' || $campaign['action'] == 'afterTrialExpired' || $campaign['action'] == 'lastCall') {
			$extendTime = 604800;
		}
	}
	$scheduleTime = $timeInterval+$installedTime+$extendTime;
	$campaign['scheduleTime'] = $scheduleTime;
	return $campaign;
}

function checkTrialCampaignRun(){
	$installedTime = getOption('installedTime');
	$traiUser = getOption('appRegisteredUser');
	if (empty($installedTime) || $traiUser != 'trialIWPPanel') {
		return false;	
	}
	return true;
}

function trialEmailCampaignCronRun(){
	if (!checkTrialCampaignRun()) {
		return false;	
	}

	trialPanelExtendedCheck();
	$where = array(
		      		'query' =>  "scheduleTime !=0 AND scheduleTime <= ':scheduleTime' AND isSent = '0' AND doneByUser = '0' AND status = '1'",
		      		'params' => array(
		               ':scheduleTime'=>time()
       				)
    			);
	$campaigns = DB::getArray("?:email_campaign", '*', $where);
	if (empty($campaigns)) {
		return false;
	}
	foreach ($campaigns as $key => $campaign) {
		
		switch ($campaign['action']) {
			case 'siteNotAdd':
				checkAndSendSiteNotAddTrialCampaignMail($campaign);
				break;
			case 'siteNotAdd2dRemainder':
				checkAndSendSiteNotAdd2dRemainderTrialCampaignMail($campaign);
				break;
			case 'noBackupSchedule':
				checkAndSendNoScheduleBackupTrialCampaignMail($campaign);
				break;
			case 'noCloudBackup':
				checkAndSendNoCloudBackupTrialCampaignMail($campaign);
				break;
			case 'updateAvailable':
				checkAndSendUpdateAvailableTrialCampaignMail($campaign);
				break;
			case 'notUsedEnterprise':
				checkAndSendNotUsedEnterpriseTrialCampaignMail($campaign);
				break;
			case 'subscribeMail':
				checkAndSendSubscribeMail($campaign);
				break;
			case 'trialEndTomorrow':
				checkAndSendTrialEndTomorrow($campaign);
				break;
			case 'trialPanelExtended':
				checkAndSendTtrialPanelExtended($campaign);
				break;
			case 'afterTrialExpired':
				checkAndSendAfterTrialExpired($campaign);
				break;
			case 'lastCall':
				checkAndSendLastCallExpired($campaign);
				break;
			case 'weeklyReport':
				checkAndSendWeeklyReport($campaign);
				break;
			case 'weeklyReport2':
				checkAndSendWeekly2Report($campaign);
				break;
		}
	}
}

function scheduleAllTrialEmailCampaign(){
	if (!checkTrialCampaignRun()) {
		return false;	
	}

	$campaigns = DB::getArray("?:email_campaign", '*', 1);
	if (empty($campaigns)) {
		return false;
	}

	foreach ($campaigns as $key => $value) {
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=>$value['action']
       				)
    			);
		$isExist = DB::getField("?:email_campaign", "id", $where);
		$value = calculateEmailCampaignScheduleTime($value);
		if (empty($isExist)) {
			DB::insert("?:email_campaign", $value);
		}else{
			DB::update("?:email_campaign", $value, $where);
		}

	}
}

function trialEmailCampaignMail($historyID){
	$traiUser = getOption('appRegisteredUser');
	if ($traiUser != 'trialIWPPanel') {
		return false;	
	}
	$historyData = getHistory($historyID, true);
	$historyAdditional = $historyData['additionalData'];
	$historyAdditional = $historyAdditional[0];
	if ($historyAdditional['detailedAction'] == 'add' && $historyAdditional['status'] == 'success' ) {
		checkAndSendFirstAddSiteTrialCampaignMail();
	}elseif($historyData['status'] == 'completed' && $historyData['type'] == 'PTC'){
		$data['update'] = count($historyData['additionalData']);
		$data['time'] = $historyData['microtimeEnded']-$historyData['microtimeAdded'];
		checkAndSendFirstUpdateTrialMail($data);
	}

}

function checkAndSendFirstAddSiteTrialCampaignMail(){
	$isSiteAdded = DB::getArray("?:sites", '*', 1);
	$firstAddSiteTime = getOption('firstAddSiteTime');
	if (empty($isSiteAdded) || !empty($firstAddSiteTime)) {
		return false;
	}
	
	$campaign = DB::getRow("?:email_campaign", '*', "action = 'siteAdd' AND isSent = '0' AND doneByUser = '0' AND status = '1'");
	if (empty($campaign)) {
		return false;
	}
	$isSent = sendTrialCampaignMail($campaign);


	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'siteAdd'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'siteNotAdd'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'siteNotAdd2dRemainder'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'siteNotAdd3rdRemainder'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'trialPanelExtended'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		updateOption('firstAddSiteTime', time());
		$update = array('scheduleTime' => time()+86400);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'updateAvailable'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
		scheduleAllTrialEmailCampaign();
	}


}

function checkAndSendFirstUpdateTrialMail($data){
	$backups  = panelRequestManager::getSitesBackups();
	if (empty($backups)) {
		$campaign = DB::getRow("?:email_campaign", '*', "action = 'updateWithoutBackup' AND isSent = '0' AND doneByUser = '0' AND status = '1'");
		if (empty($campaign)) {
			return false;
		}
		$campaign = array_merge($campaign, $data);
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'updateWithoutBackup'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);

			$update = array('doneByUser' => 1);
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'updateWithBackup'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}else{

		$campaign = DB::getRow("?:email_campaign", '*', "action = 'updateWithBackup' AND isSent = '0' AND doneByUser = '0' AND status = '1'");
		if (empty($campaign)) {
			return false;
		}
		$campaign = array_merge($campaign, $data);
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'updateWithBackup'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);

			$update = array('doneByUser' => 1);
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'updateWithoutBackup'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}
}

function checkAndSendSiteNotAddTrialCampaignMail($campaign){
	$isSiteAdded = DB::getArray("?:sites", '*', 1);

	if (empty($isSiteAdded)) {
		
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'siteNotAdd'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}else{
			$update = array('doneByUser' => 1);
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'siteNotAdd'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendNoScheduleBackupTrialCampaignMail($campaign){
	if (!function_exists('scheduleBackupGetBackups')) {
		return false;
	}
	$scheduleBackup = scheduleBackupGetBackups();
	if (empty($scheduleBackup)) {

		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'noBackupSchedule'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}else{
			$update = array('doneByUser' => 1);
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'noBackupSchedule'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendNoCloudBackupTrialCampaignMail($campaign){
	$cloudBackup = DB::getArray("?:backup_repository", "*", 1);
	if (empty($cloudBackup)) {
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'noCloudBackup'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}
}

function checkAndSendUpdateAvailableTrialCampaignMail($campaign){
	$updates = panelRequestManager::getSitesUpdates();
	$where = array(
			      		'query' =>  "type = ':type' AND status = 'completed'",
			      		'params' => array(
			               ':type'=> 'PTC'
	       				)
	    			);
	$updatesDone = DB::getField("?:history", "historyID", $where);

	if (!empty($updates) && !empty($updatesDone)) {
		
		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'updateAvailable'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);

		return false;
	}

	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'updateAvailable'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}


}

function checkAndSendNotUsedEnterpriseTrialCampaignMail($campaign){
	if(!function_exists('multiUserGetAllUsers')) return false;

	$users = multiUserGetAllUsers(array());

	if (!empty($users) && count($users) < 2) {
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'notUsedEnterprise'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}
}

function checkAndSendSubscribeMail($campaign){
	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'subscribeMail'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendTrialEndTomorrow($campaign){
	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'trialEndTomorrow'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendAfterTrialExpired($campaign){
	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'afterTrialExpired'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendLastCallExpired($campaign){
	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'lastCall'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendSiteNotAdd2dRemainderTrialCampaignMail($campaign){
	$isSiteAdded = DB::getArray("?:sites", '*', 1);

	if (empty($isSiteAdded)) {
		
		$isSent = sendTrialCampaignMail($campaign);

		if ($isSent) {
			$update = array('isSent' => 1, 'sentTime' => time());
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'siteNotAdd2dRemainder'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
		}
	}else{
			$update = array('doneByUser' => 1);
			$where = array(
			      		'query' =>  "action = ':action'",
			      		'params' => array(
			               ':action'=> 'siteNotAdd2dRemainder'
	       				)
	    			);
			DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendSiteNotAdd3rdRemainderTrialCampaignMail($campaign){
	$isSiteAdded = DB::getArray("?:sites", '*', 1);
	if (!empty($isSiteAdded)) {
		$update = array('doneByUser' => 1);
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'trialPanelExtended'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
		return false;
	}

	updateOption('trialPanelExtended', 1);
	scheduleAllTrialEmailCampaign();
}

function checkAndSendTrialPanelExtendedTrialCampaignMail($campaign){
	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'trialPanelExtended'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function sendTrialCampaignMail($campaign){
	$where = array(
			      		'query' =>  "userID = ':userID'",
			      		'params' => array(
			               ':userID'=>$GLOBALS['userID']
           				)
        			);
	$user = DB::getRow("?:users", "email, name", $where);
	$toEmail = $user['email'];
	$toName = $user['name'];
	$contentTPL = '/templates/trialEmailTemplate/'.$campaign['action'].'.tpl.php';

	$campaign['firstName'] = $toName;
	$TPLOptions = array('isMailFormattingReqd' => false);
	$content = TPL::get($contentTPL, $campaign, $TPLOptions);
	$content = explode("+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-", $content);
	$mailSubject = $content[0];
	$mailBody = $content[1];

	$response = sendTrialMail($toEmail, $toName, $mailSubject, $mailBody);
	if ($response['status'] == 'success') {
		return true;
	}

	addNotification($type='E', $title='Mail Error', $message=$response['errorMsg'], $state='U');	  
	return false;
}

function trialPanelExtendedCheck(){
	$where = array(
		      		'query' =>  "scheduleTime <= ':scheduleTime' AND action = 'trialPanelExtended' AND isSent = '0' AND doneByUser = '0' AND status = '1'",
		      		'params' => array(
		               ':scheduleTime'=>time()
       				)
    			);
	$campaign = DB::getRow("?:email_campaign", '*', $where);
	if (empty($campaign)) {
		return false;
	}
	checkAndSendSiteNotAdd3rdRemainderTrialCampaignMail($campaign);
}

function checkAndSendTtrialPanelExtended($campaign){
	$trialPanelExtended = getOption('trialPanelExtended');
	if (!empty($trialPanelExtended)) {
		checkAndSendTrialPanelExtendedTrialCampaignMail($campaign);
	}
}



function sendTrialMail($toEmail, $toName, $mailSubject, $mailBody,$mail_fromname='',$mail_from=''){
	require_once(APP_ROOT.'/lib/phpmailer.php');
	require_once(APP_ROOT.'/lib/class.smtp.php'); //smtp mail
	if($mail_fromname=='') {
		$mail_fromname = Reg::get('config.MAIL_FROMNAME');
	}
	
	if($mail_from=='') {
		$mail_from = Reg::get('config.MAIL_FROM');
	}	
	
	$mail = new PHPmailer();
	$mail->From = $mail_from;
	$mail->FromName = $mail_fromname;
	$mail->Host = Reg::get('config.MAIL_HOST');
	$mail->Port = 465;
	$mail->Mailer   = 'smtp';
	$mail->Username = Reg::get('config.MAIL_USERNAME');	
	$mail->Password = Reg::get('config.MAIL_PASSWORD');
	// $mail->IsSMTP();
	$mail->SMTPAuth  =  true;
	$mail->SMTPSecure = true;
	$mail->Subject = $mailSubject;
	$mail->Body = $mailBody;
	$mail->CharSet = 'UTF-8';
	
	$mail->AddAddress($toEmail/*, $toName*/);
	$mail->AddReplyTo($mail_from, $mail_fromname);
	$mail->IsHTML();
	
	if($mail->Send()){
		return array('status' => 'success');
	}
	else{
		 return array('status' => 'error', 'error' => '', 'errorMsg' => $mail->ErrorInfo);
	}	
}

function checkAndSendWeeklyReport($campaign){
	$firstAddSiteTime = getOption('firstAddSiteTime');
	if (empty($firstAddSiteTime)) {
		return false;
	}
	$params['dates']['fromDate'] = $firstAddSiteTime;
	$params['dates']['toDate'] = time();
	$result = getTrialWeeklyReport($params);
	if (empty($result['result'])) {
		return false;
	}

	$campaign['result'] = $result['result'];

	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'weeklyReport'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function checkAndSendWeekly2Report($campaign){
	$firstAddSiteTime = getOption('firstAddSiteTime');
	if (empty($firstAddSiteTime)) {
		return false;
	}
	$params['dates']['fromDate'] = $firstAddSiteTime;
	$params['dates']['toDate'] = time();
	$result = getTrialWeeklyReport($params);
	if (empty($result['result'])) {
		return false;
	}
	$campaign['action'] = 'weeklyReport';
	$campaign['result'] = $result['result'];

	$isSent = sendTrialCampaignMail($campaign);

	if ($isSent) {
		$update = array('isSent' => 1, 'sentTime' => time());
		$where = array(
		      		'query' =>  "action = ':action'",
		      		'params' => array(
		               ':action'=> 'weeklyReport2'
       				)
    			);
		DB::update("?:email_campaign", $update, $where);
	}
}

function getTrialWeeklyReport($params = array()){
	
	$result_temp = array();
	$params['action'] = array('core', 'theme', 'plugin');
	$params['type'] = array('backup', 'PTC','malwareScanningSucuri');
	$params['siteID'] = DB::getFields("?:sites", 'siteID','1');
	if(!empty($params['dates'])){

		$fromDate 	= $params['dates']['fromDate'];
		$toDate		= $params['dates']['toDate'];
		//$result['dateRange'] 	= date("M d, Y", $fromDate) .'-'. date("M d, Y", $toDate);
		if(!empty($fromDate) && !empty($toDate) && $fromDate != -1 && $toDate != -1){
			$action = '';
			
			$typeIn = $params['type'];
			if(is_array($typeIn)){
				//if(in_array('backup', $params['type'])){//old code - backup count not working
	//				$action = "OR (type = 'scheduleBackup' AND action = 'runTask')";
	//			}
				
				if(in_array('backup', $typeIn)){//new code
					$action = "OR 
					(
					(type = 'backup' AND (action = 'now' OR  action = 'multiCallNow')) 
					OR 
					(type = 'scheduleBackup' AND (action = 'runTask' OR  action = 'multiCallRunTask'))
					
				)";
					if(($key = array_search('backup', $typeIn)) !== false) {
						unset($typeIn[$key]);
					}
					
				}
			}
			$toDate += 86399;
			$dateQuery = "( ( type IN ('".implode("','", array_unique(DB::esc($typeIn)))."') AND action != 'get') ".$action." ) AND microtimeAdded >= ".DB::esc($fromDate)." AND  microtimeAdded <= ".DB::esc($toDate)." ";
		}
	}
	
	$siteName = array();
	$data = array();
	foreach($params['siteID'] as $siteIDs => $siteID){
		$data[$siteID] = array();
		$actionsHistoryData = array();
		$where1 = array(
		      		'query' =>  "siteID = ':siteID' AND ".$dateQuery."GROUP BY actionID ORDER BY historyID",
		      		'params' => array(
		      		   ':siteID' => $siteID
       				)
    			);

		$actionIDs = DB::getFields("?:history", "actionID", $where1);
		$where = array(
					'query' => "siteID = ':siteID'",
					'params' => array(
							':siteID' => $siteID
					)
				);
		$siteName[] = DB::getField("?:sites", "name", $where);
		
		if(!empty($actionIDs)){ 
			$actionsHistoryData = array();
			foreach($actionIDs as $actionID){
				$actionsHistoryData[ $actionID ] = panelRequestManager::getActionStatus($actionID);
				unset($actionIDs);
			}
		if(!empty($actionsHistoryData) && is_array($actionsHistoryData))
		foreach($actionsHistoryData as $actionID => $actionData){
			//if(in_array($actionData['type'], $params['type'])){
				if($actionData['type'] == "PTC" && !empty($params['action'])){
					foreach($params['action'] as $actionKey => $action){
						foreach($actionData['detailedStatus'] as $key => $detailedAction){
								if($detailedAction['siteID'] == $siteID && $detailedAction['detailedAction'] == $action){
								array_push($params['type'], $action);
								if($detailedAction['status'] == 'success')
								$data[$siteID][$detailedAction['detailedAction']][] = $actionData['status'];
							}
						}
					}
					
				}else{
					foreach($actionData['detailedStatus'] as $key => $detailedAction){
						if($detailedAction['siteID'] == $siteID && $detailedAction['status'] == 'success')
						$data[$siteID][$actionData['type']][] = $detailedAction['status'];
					}
				}
			//}		
		}
		}else{
			if(!in_array('googleAnalytics', $params['type']) && !in_array('malwareScanningSucuri', $params['type']))
				continue;
		}
		
	}
	
	$result = array();
	
	if(!empty($params['type']) && is_array($params['type']))
	foreach($params['type'] as $key => $type){
		foreach($data as $sID => $actionType){
			if(empty($actionType)) $result[$sID] = array();
			if($type == 'backup' && !empty($actionType['scheduleBackup'])){ $actionType[$type] = @array_merge($actionType['scheduleBackup'], (array)$actionType[$type]); }
				if(is_array($actionType[$type])){
					$result[$sID][$type] = @array_count_values($actionType[$type]);	
				}
		}
	}
	$formatArray = array();
	foreach ($result as $siteID => $value) {
		if (!empty($value)) {
			if (!empty($value['plugin']['success'])) {
				$formatArray['updates']['sites']++;
				$formatArray['updates']['plugin'] += $value['plugin']['success'];
			}
			if (!empty($value['theme']['success'])) {
				$formatArray['updates']['sites']++;
				$formatArray['updates']['theme'] += $value['theme']['success'];
			}
			if (!empty($value['backup']['success'])) {
				$formatArray['backup']['sites']++;
				$formatArray['backup']['backupCount'] += $value['backup']['success'];
			}

			if (!empty($value['malwareScanningSucuri']['success'])) {
				$formatArray['malwareScanningSucuri']['sites']++;
				$formatArray['malwareScanningSucuri']['scanCount'] += $value['malwareScanningSucuri']['success'];
			}
		}
	}

	$dateQuery = " type = 'installClone' AND action = 'newSite' AND status ='completed' AND microtimeAdded >= ".DB::esc($fromDate)." AND  microtimeAdded <= ".DB::esc($toDate)." ";

	$installClone = DB::getFields("?:history", 'historyID', $dateQuery);
	if (!empty($installClone)) {
		$formatArray['cloneAndStaging']['installClone'] = count($installClone);
	}
	$stagingSite = DB::getFields("?:sites", 'siteID', "type = 'staging'");
	if (!empty($stagingSite)) {
		$formatArray['cloneAndStaging']['stagingSite'] = count($stagingSite);
	}
	$result = array(
		'result' => $formatArray
	);
	return $result;
	
}