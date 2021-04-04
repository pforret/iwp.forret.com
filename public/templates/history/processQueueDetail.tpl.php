<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
if(!empty($d['actionsHistoryData'])){
	$sitesData = Reg::tplGet('sitesData');
  $statusMessages = array(
"writingRequest" => "Adding to queue",
"pending" => "Pending",
"initiated" => "Initiated",
"running" => "Running",
"scheduled" => "Waiting in queue",
"processingResponse" => "Processing response",
"multiCallWaiting" => "Running",
"retry" => "waiting for retry"
  );
foreach($d['actionsHistoryData'] as $actionID => $actionHistory){
   $showByDetailedActionGroup = ( ($actionHistory['type'] == 'PTC')|| ( ($actionHistory['action'] == 'manage' || $actionHistory['action'] == 'install') && ($actionHistory['type'] == 'plugins' || $actionHistory['type'] == 'themes') ) );
  $showByDetailedActionGroup2 = true;
  $actionIDHTML = str_replace('.', '', $actionID);
  $titleTweak = processQueueTweak($actionHistory, 'staging', 'title');
  $inProgress=0;
  if(in_array($actionHistory['status'], array('pending', 'running', 'initiated', 'processingResponse','multiCallWaiting','retry'))){$inProgress=1;}
  if($titleTweak){
    $TPLPrepareHistoryBriefTitle = $titleTweak;
  } else {
    $TPLPrepareHistoryBriefTitle = TPLPrepareHistoryBriefTitle($actionHistory);
  }
 ?>

    <div class="queue_detailed <?php echo $actionIDHTML; ?> nano" id="<?php echo $actionIDHTML; ?>" style="display:none;"  actionID="<?php echo $actionID; ?>">
      <div class="content">
        <div class="item_title"><span class="droid700" style="padding: 12px; float: left; line-height: 20px; padding-bottom: 0;"><?php echo $TPLPrepareHistoryBriefTitle; ?></span>
          <div class="time_suc_fail"><span class="timestamp"><?php echo @date(Reg::get('dateFormatLong'), $actionHistory['time']); ?></span>
          <?php if($actionHistory['statusSummary']['success']) { ?><span class="success"><?php echo $actionHistory['statusSummary']['success']; ?></span><?php } ?>
          <?php if($errorCount = ($actionHistory['statusSummary']['error'] + $actionHistory['statusSummary']['netError'])) { ?><span class="failure"><?php echo $errorCount; ?></span><?php } ?>
          <a class="btn_send_report float-right droid400 sendReport" actionid="<?php echo $actionID; ?>">Report Issue</a>
          <?php if($errorCount = ($actionHistory['statusSummary']['error'] + $actionHistory['statusSummary']['netError'])) { ?><br>
          <a class="btn_send_report float-right droid400 needConfirm retryAllFailure" actionid="<?php echo $actionID; ?>">Retry Failed</a>
          <?php } ?>
          </div>
          <div class="clear-both"></div>
        </div>
<?php 
//Grouping by siteID, detailedAction, status
$fullGroupedActions = array();

$siteWithErrors = array();
foreach($actionHistory['detailedStatus'] as $singleAction){
  if(isset($singleAction['isV3'])){
    if($singleAction['isV3'] == 1){
      $version = 'v3';
    }elseif($singleAction['isV3'] == 0){
      $version = 'v2';
    }else{
      $version = '';
    }
  }else{
      $version = '';
  }
	//to display plugin slug instead of plugin main file say hello-dolly/hello_dolly.php => hello-dolly
	if(($actionHistory['type'] == 'PTC' || $actionHistory['type'] == 'staging') && $singleAction['detailedAction'] == 'plugin'){
		$singleAction['uniqueName'] = reset(explode('/', $singleAction['uniqueName']));
		$singleAction['uniqueName'] = str_replace('.php', '', $singleAction['uniqueName']);
	}
	
	if(in_array($actionHistory['type'], array('themes', 'plugins')) && $actionHistory['action'] == 'install' && strpos($singleAction['uniqueName'], '%20') !== false){//this to replace %20 in the file name
		$singleAction['uniqueName'] = str_replace('%20', ' ', $singleAction['uniqueName']);
	}
	if($singleAction['status'] == 'success'){
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'success' ] [] = array('name' => $singleAction['uniqueName'],'detailedAction' => $singleAction['detailedAction'],'type' => $actionHistory['type'], 'action' => $actionHistory['action'] , 'successMsg' => $singleAction['successMsg']);
	} elseif($singleAction['status'] == 'error' || $singleAction['status'] == 'netError'){		
		//if($singleAction['error'] == 'main_plugin_connection_error'){ $singleAction['errorMsg'] = 'Plugin connection error.'; }
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'error' ] [] = array('name' => $singleAction['uniqueName'], 'errorMsg' => $singleAction['errorMsg'], 'error' => $singleAction['error'], 'type' => $actionHistory['type'], 'action' => $actionHistory['action'], 'detailedAction' => $singleAction['detailedAction'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status'], 'historyID' => $singleAction['historyID']);
		$siteWithErrors[$singleAction['siteID']] = $singleAction['historyID'];
	}	else{
		$fullGroupedActions[ $singleAction['siteID'] ][ $singleAction['detailedAction'] ][ 'others' ] [] = array('name' => $singleAction['uniqueName'], 'detailedAction' => $singleAction['detailedAction'], 'errorMsg' => $singleAction['mainStatus'], 'microtimeInitiated' => $singleAction['microtimeInitiated'], 'status' => $singleAction['status'], 'historyID' => $singleAction['historyID'], 'type' => $actionHistory['type'], 'action' => $actionHistory['action'],'successMsg' => $singleAction['successMsg']);
	}
	$sitesDataTemp[$singleAction['siteID']]['name'] = isset($sitesData[$singleAction['siteID']]['name']) ?  $sitesData[$singleAction['siteID']]['name'] : $singleAction['URL'];
  $sitesDataTemp[$singleAction['siteID']]['version'] = $version;
}?>

<?php foreach($fullGroupedActions as $siteID => $siteGroupedActions){ ?>  
        <div class="queue_detailed_ind_site_cont">
          <div class="site_name droid700 version_number"><?php echo $sitesDataTemp[$siteID]['name'].'<span>'.$sitesDataTemp[$siteID]['version'].'</span>'; ?><?php if(!empty($siteWithErrors[$siteID])){ ?><a style="float:right;" class="moreInfo" historyID="<?php echo $siteWithErrors[$siteID]; ?>">View site response</a><a style="float:right;font-weight: 400;" class="curlVerbose" historyID="<?php echo $siteWithErrors[$siteID]; ?>">Curl verbose</a><?php } ?></div>
     <?php foreach($siteGroupedActions as $detailedAction => $statusGroupedActions){ ?>
     	<?php
        if(($actionHistory['type'] == 'PTC' || $actionHistory['type'] == 'staging') && $detailedAction == 'plugin'){
				
			}
		?>
     
          <div class="item_cont">
            <?php if($showByDetailedActionGroup){ ?><div class="item_label float-left"><span><?php echo ucfirst($detailedAction); ?></span></div><?php } ?>
              
            <div class="item_details float-left">

              <?php if(!empty($statusGroupedActions['success'])){ ?>
                <div class="item_details_success"> 
                  <?php foreach($statusGroupedActions['success'] as $oneAction){
                    if ($oneAction['type'] == 'staging' || $oneAction['type'] == 'stagingToLive') {
                        processQueueTweak($oneAction, 'staging', 'content');
                    }
                    if($showByDetailedActionGroup){
                      echo '<span>'.ucfirst($oneAction['name']).'</span>'; 
                      ?>  <div class="reason"> <?php
                        if (!empty($oneAction['successMsg'])) {
                           echo $oneAction['successMsg']; 
                        }?> </div> <?php
                    }else{ 
                      if ($oneAction['isStage']  == 'staging') {
                        echo "<span>".TPLActionTitle($oneAction)." (Staging site will not be displayed in the site list)</span>"; 

                    }else{ 
                        echo "<span>".TPLActionTitle($oneAction)."</span>"; 
                        ?>  <div class="reason"> <?php
                        if (!empty($oneAction['successMsg'])) {
                           echo $oneAction['successMsg']; 
                        }?> </div> <?php

                      }
                    }
                  }
                  ?>
                  <div class="clear-both"></div>
                </div>
              <?php }    ?>

              <?php if(!empty($statusGroupedActions['others'])){ ?>
              <div class="<?php if($inProgress == 1) {?>running_task<?php } else { ?> item_details_fail <?php } ?> ">
              <?php foreach($statusGroupedActions['others'] as $oneAction){

                     if ($oneAction['type'] == 'staging' || $oneAction['type'] == 'stagingToLive'){
                          processQueueTweak($oneAction, 'staging', 'content');
                    } ?>
                <?php if($showByDetailedActionGroup2){ ?><div class="name"><?php echo TPLActionTitle($oneAction); ?>
                <?php $singleTaskStoppingClass = 'single stop_pending'; if($oneAction['errorMsg'] == 'multiCallWaiting'){$singleTaskStoppingClass .= ' stop_multicall'; } ?>
<div class="rep_sprite btn_stop_rep_sprite" ><span class = "rep_sprite_backup btn_stop_progress <?php echo $singleTaskStoppingClass; ?>"  mechanism = "pending" historyID = "<?php echo $oneAction['historyID']; ?>"></span> </div>

              </div><?php } ?>
                <div class="reason<?php if(!$showByDetailedActionGroup2){ ?> only<?php } ?>"><?php echo $statusMessages[$oneAction['errorMsg']]; ?></div> <div style="position: absolute;margin-top: -25px;margin-left: 61px;"> <?php if (!empty($oneAction['successMsg'])) { echo $oneAction['successMsg']; } ?>
                </div><div class="clear-both"></div><?php } ?>
              </div>
              <?php } ?>             
              <?php if(!empty($statusGroupedActions['error'])){ ?> 
              <div class="item_details_fail">
              <?php foreach($statusGroupedActions['error'] as $oneAction){
                  if ($oneAction['type'] == 'staging'){
                    processQueueTweak($oneAction, 'staging', 'content');
                  }  
                  ?>
                <a style="float:right;" class="retry_failed_task needConfirm" historyid="<?php echo $oneAction['historyID']; ?>">Retry</a>
                <?php if($showByDetailedActionGroup2){ ?><div class="name"><?php echo TPLActionTitle($oneAction); ?></div><?php } ?>
                <div class="reason<?php if(!$showByDetailedActionGroup2){ ?> only<?php } ?>"><?php echo TPLAddErrorHelp($oneAction); ?></div>
                <div class="clear-both"></div>
                <?php } ?>
              </div>
              <?php } ?>
            </div>
            <div class="clear-both"></div>
          </div>
      <?php } //END foreach($siteGroupedActions as $detailedAction => $statusGroupedActions) ?> 
        </div>
<?php }}
}