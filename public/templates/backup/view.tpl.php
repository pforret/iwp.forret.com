<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
?>
<?php  $sitesData = Reg::tplGet('sitesData'); 

	$restrictions = array();
	if(function_exists('multiUserAllowAccess')) {
  		$restrictions = multiUserAllowAccess();
                $restrictions = $restrictions['restrict'];
        }
		
  if(!empty($d['sitesBackups'])){   ?>
  
  <div class="rows_cont" id="backupList">
  
  <?php foreach($d['sitesBackups'] as $siteID => $siteTaskType){

    TPL::captureClear('oldBackups');
	  TPL::captureClear('parentBackup');

  ?>
    <div class="ind_row_cont">
      <div class="row_summary">
      	<?php TPL::captureStart('sitesBackupsRowSummary'); ?>
        <div class="row_arrow"></div>
        <div class="row_name searchable"><?php echo $sitesData[$siteID]['name'] ?></div>
        <div class="clear-both"></div>
        <?php TPL::captureStop('sitesBackupsRowSummary'); ?>
        <?php echo TPL::captureGet('sitesBackupsRowSummary'); ?>
      </div>
      <div class="row_detailed" style="display:none;">
        <div class="rh">
          <?php echo TPL::captureGet('sitesBackupsRowSummary'); ?>
        </div>
        <div class="rd">
          <div class="row_updatee">
          
          	<?php foreach($siteTaskType as $key => $siteBackups){
				if($key == 'parentBackup'){
					TPL::captureStart('parentBackup');
					echo TPL::captureGet('parentBackup');
				}elseif ($key != 'backupNow') {
          TPL::captureStart('oldBackups');
          echo TPL::captureGet('oldBackups');
        }
				foreach($siteBackups as $siteBackup){ 
                $isNewBackup = '';
                  $isCloudBackup = '';
                  $parentSiteID = '';
                  $blogID = '';
                if (!empty($siteBackup['newBackupMethod'])) {
                      $isNewBackup = 'isNewBackup';
                  }
                  if ($siteBackup['repo'] != 'Server') {
                    $isCloudBackup = 'isCloudBackup';
                  }
                  if (!empty($siteBackup['parentSiteID'])) {
                      $parentSiteID = $siteBackup['parentSiteID'];
                      $blogID = getBlogID($siteID);

                  }
          ?>
            <div class="row_updatee_ind topBackup">
              <div class="label_updatee float-left">
                <div class="label droid700 float-right"><?php echo $siteBackup['backupNameDisplay']; ?></div>
              </div>
              <div class="items_cont float-left">
                <div class="item_ind float-left">
                  <div class="rep_sprite_backup stats <?php if($siteBackup['repository'] == 'Server' || empty($siteBackup['repository'])){ echo "repository"; } else { echo "cloud"; }?> delConfHide" style="position:relative"><?php if(!empty($siteBackup['repository'])){ echo $siteBackup['repository']; } else { echo "Server"; }?></div>
                  <div class="rep_sprite_backup stats <?php if($siteBackup['what'] == 'full'){ ?>files_db<?php } elseif($siteBackup['what'] == 'db'){ ?>db<?php } elseif($siteBackup['what'] == 'files' || empty($siteBackup['what'])){ ?>files<?php } ?> delConfHide" style="position:relative"><?php if($siteBackup['what'] == 'full'){ ?>Files + DB<?php } elseif($siteBackup['what'] == 'db'){ ?>DB<?php } elseif($siteBackup['what'] == 'files'){ ?>Files<?php } ?></div>
                  <div class="rep_sprite_backup stats size delConfHide" style="position:relative"><?php echo $siteBackup['size']; ?></div>
                  <div class="rep_sprite_backup stats time delConfHide" style="position:relative" ><?php echo @date(Reg::get('dateFormatLong'), $siteBackup['time']); ?></div>
                  
                  <?php if(empty($restrictions) || !in_array("restoreDeleteDownloadBackup", $restrictions)){ ?> 
							<div class="row_backup_action rep_sprite" style="float:right;">
                            <a class="trash rep_sprite_backup removeBackup" style="position:relative" sid="<?php echo $siteBackup['siteID']; ?>" taskName="<?php echo $siteBackup['backupName']; ?>" referencekey="<?php echo $siteBackup['referenceKey']; ?>"></a>
                            <div class="del_conf" style="display:none;">
                              <div class="label">Sure?</div>
                              <div class="yes deleteBackup <?php echo $isNewBackup; ?>">Yes</div>
                              <div class="no deleteBackup">No</div>
                            </div>
                          </div>
							<?php if(!empty($siteBackup['downloadURL']) && (!is_array($siteBackup['downloadURL']))){ ?> <div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a class="download rep_sprite_backup" href="<?php echo $siteBackup['downloadURL']; ?>"></a></div> <?php } ?>
							<?php if(!empty($siteBackup['downloadURL']) && is_array($siteBackup['downloadURL'])){ ?>
								<div class="row_backup_action rep_sprite delConfHide" style="float:right;"><a data-downloads='<?php echo json_encode($siteBackup);?>' data-downcount='<?php echo count($siteBackup['downloadURL'])?>' class="download multiple_downloads rep_sprite_backup"></a></div>
							<?php
							}
							?>
                  <div class="row_action float-left delConfHide"><a class="restoreBackup needConfirm  <?php echo $isNewBackup; ?> <?php echo $isCloudBackup; ?>"  sid="<?php echo $siteBackup['siteID']; ?>" taskName="<?php echo $siteBackup['backupName']; ?>" referencekey="<?php echo $siteBackup['referenceKey']; ?>" baseBackupFileName="<?php echo $siteBackup['backupFileBasename']; ?>" parentSiteID = "<?php echo $parentSiteID; ?>" blogID = "<?php echo $blogID; ?>" what = "<?php echo $siteBackup['what']; ?>" >Restore</a></div>
				  <?php } ?>
						                  
                
                </div>
              </div>
              <div class="clear-both"></div>
            </div> 
            <?php }//end foreach($siteBackups as $siteBackup)
			
				if($key == 'parentBackup'){
					TPL::captureStop('parentBackup');
				}elseif ($key != 'backupNow') {
          TPL::captureStop('oldBackups');
        }
  			}//end foreach($siteTaskType as $key => $siteBackups)
			if($oldBackupsHTML = trim(TPL::captureGet('oldBackups'))){
				?> <div style="border-top: 1px solid #F1F1D7;margin-top: -1px;font-weight: 700;margin-left: -33px;padding: 40px 0px 16px 49px;border-bottom: 1px solid #F1F1D7;">Other backups <span style="font-weight: 500">(Backups from deleted schedules & backups from reconnected sites are shown below) </span></div> <?php
				echo $oldBackupsHTML;
			}
      if($oldBackupsHTML = trim(TPL::captureGet('parentBackup'))){
        ?> <div style="border-top: 1px solid #F1F1D7;margin-top: -1px;font-weight: 700;margin-left: -33px;padding: 40px 0px 16px 49px;border-bottom: 1px solid #F1F1D7;">Parent site schedule backups</div> <?php
        echo $oldBackupsHTML;
      }
			?>
          </div>
        </div>
      </div>
    </div>
    <?php } 
//END foreach($d['sitesBackups'] as $siteID => $siteBackups) ?>
  </div>
<?php } else { if(empty($restrictions) || !in_array("createBackup", $restrictions)){ ?>
<div class="empty_data_set"><div class="line2">Looks like there are <span class="droid700">no backups here</span>. Create a <a class="multiBackup">Backup Now</a>.</div></div>
<script>$(".searchItems","#backupPageMainCont").hide();</script>
<?php } else { ?> 
			<div class="empty_data_set"><div class="line2">Looks like there are <span class="droid700">no backups here</span>.</div></div>
<?php }
 } ?>

