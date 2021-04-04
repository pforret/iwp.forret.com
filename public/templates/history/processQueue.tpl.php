<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
?>
<div class="site_bar_btn rep_sprite float-right" style="margin-right:10px;">
  <div id="process_queue" class="historyToolbar"><div class="<?php if($d['showInProgress']){?> in_progress<?php } ?> historyToolbar processQueueMoveOut"><span class="processQueueMove"></span> </div>Process Queue</div>
  <div class="queue_cont" id="historyQueue">
<?php
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
if(!empty($d['actionsHistoryData'])){
	$sitesData = Reg::tplGet('sitesData');
foreach($d['actionsHistoryData'] as $actionID => $actionHistory){
  $showByDetailedActionGroup = ( ($actionHistory['type'] == 'PTC')|| ( ($actionHistory['action'] == 'manage' || $actionHistory['action'] == 'install') && ($actionHistory['type'] == 'plugins' || $actionHistory['type'] == 'themes') ) );
  $showByDetailedActionGroup2 = true;

  if(empty($actionHistory)){
    continue;
  }
  
  $actionIDHTML = str_replace('.', '', $actionID);
  //stagingTweakInProcessQueue($actionHistory);
  if($actionHistory['status'] == 'pending' || $actionHistory['status'] == 'multiCallWaiting') $actionOverallStatus = '';  
  elseif($actionHistory['statusSummary']['total'] == $actionHistory['statusSummary']['success']) $actionOverallStatus = 'success';
  else $actionOverallStatus = 'failure';
  $percentageDone = (((int)$actionHistory['historyStatusSummary']['success']+(int)$actionHistory['historyStatusSummary']['error'])/(float)$actionHistory['historyStatusSummary']['total'])*100;

  $percentageDone = ($percentageDone<2)?2:$percentageDone;
  if($actionHistory['historyStatusSummary']['total'] == 1) $percentageDone = 100;//99.9;
  $inProgress=0;
  if(in_array($actionHistory['status'], array('pending', 'running', 'initiated', 'processingResponse','multiCallWaiting','retry'))){$inProgress=1;}

  $stoppingClass = '';
  $where = array(
              'query' =>  "actionID = ':actionID'",
              'params' => array(
                   ':actionID'=>$actionID
            )
        );
  $statusArrTemp = DB::getArray("?:history", "status", $where);
  $statusArr = array();
  foreach($statusArrTemp as $actualStatus){
    array_push($statusArr, $actualStatus['status']);
  }
  // $stopping = count(array_intersect($statusArr, array('pending','multiCallWaiting','scheduled') ) );
  $stopping = count(array_diff($statusArr, array('completed','error','netError') ) );
  if( $stopping ){
    $stoppingClass = 'stop_pending';
    if($actionHistory['status'] == 'multiCallWaiting'){
      $stoppingClass .= ' stop_multicall';
    }
  }

?>
<?php TPL::captureStart('processQueueRowSummary'); ?>
<?php echo TPL::captureGet('processQueueRowSummary');
$titleTweak = processQueueTweak($actionHistory, 'staging', 'title');
if($titleTweak){
  $TPLPrepareHistoryBriefTitle = $titleTweak;
} else {
  $TPLPrepareHistoryBriefTitle = TPLPrepareHistoryBriefTitle($actionHistory);
}
?>
<div class="queue_ind_item historyItem <?php echo $actionIDHTML; ?> <?php if($stoppingClass != 'stop_pending'){ echo ' '.$actionOverallStatus; }?>" did="<?php echo $actionIDHTML; ?>"  actionID="<?php echo $actionID; ?>" onclick=""><?php if($inProgress){ ?><div class="in_progress" style="width: <?php echo $percentageDone; ?>%"></div> <?php } ?> <?php if($stoppingClass != ''){ ?>  <div class="rep_sprite btn_stop_rep_sprite" ><span class = "rep_sprite_backup btn_stop_progress <?php echo $stoppingClass; ?>"  mechanism = "pending" actionID = "<?php echo $actionID; ?>"></span> </div><?php } ?> <?php if($percentageDone<100){ ?><div style="position:relative;"><?php } ?><div class="queue_ind_item_title"><?php  echo $TPLPrepareHistoryBriefTitle; ?></div><div class="timestamp float-right"><?php echo @date(Reg::get('dateFormatYearLess'), $actionHistory['time']); ?></div><?php if($percentageDone<100){ ?></div><?php } ?>
<div class="clear-both"></div>
</div>
<?php TPL::captureStop('processQueueRowSummary'); ?>
<div class="hisDetail_<?php echo $actionIDHTML; ?>"></div>
<?php } //END foreach($d['actionsHistoryData'] as $actionHistory) ?>
<?php } //if(!empty($d['actionsHistoryData']))
else{ ?>

<?php TPL::captureStart('processQueueRowSummary'); ?>
	<div class="empty_data_set websites"><div class="line1">Operations that you initiate will be queued and processed here.</div></div>
<?php TPL::captureStop('processQueueRowSummary'); ?>
<?php	
}
 ?>
    <div class="queue_list">
      <div class="th rep_sprite">
        <div class="title droid700">PROCESS QUEUE</div><div class="float-left" id="historyQueueUpdateLoading"></div>
        <div class="history"><a class="navLinks" page="history">View Activity Log</a></div>
      </div>
      <div class="queue_ind_item_cont nano">
        <div class="content">
          <?php echo TPL::captureGet('processQueueRowSummary'); ?>
        </div>
      </div>
    </div>
  </div>
</div>