<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

if (!empty($d['siteSummaryData'])) {
	$sites = $d['siteSummaryData'];
	$view = $d['view'];
?> 
<script>
$("#updatePagination").show();
$('.update_overall').show();
$('.info_bar').show();
$('.update_in_page').show();
</script>
<div class="empty_data_set hiddenCheck" style="display:none">
	<div class="line2">Hurray! Everything is up-to-date.</div>
</div>
<div class="rows_cont" style="position:relative">
	<div class="no_match hiddenCont" style="display:none">Bummer, there are no matches.<br />Try typing fewer characters.</div>

<?php  
$parentFlag = 1;
foreach ($sites as $siteID => $data) { 
	
	$isErrorMessageActive = 'active';				//a variable to prevent adding active class to the div when its a error message
	$rowChecboxForError = '<div class="row_checkbox main_checkbox"></div>';
	$updateGroupForError = '<a class="update_all_group needConfirm" selector="parent_'.$parentFlag.'" parent="parent_'.$parentFlag.'"><span class="status_parent_'.$parentFlag.' statusSpan">Update All</span></a>';
	$updateErrorClass = '';
	$vulnurableParClass = '';
	if(!empty($data['error'])){
		$isErrorMessageActive = '';
		$rowChecboxForError = '';
		$updateGroupForError = '';
		$updateErrorClass = 'update_error';
	}

	if (!empty($data['vulnerability'])) {
		$vulnurableParClass = 'vulnurable_active';
	}
?>

	<div class="ind_row_cont <?php echo $vulnurableParClass; ?> <?php echo $updateErrorClass; ?> js_sites <?php echo $isErrorMessageActive; ?> visible parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" siteid="<?php echo $siteID; ?>" view="<?php echo $view; ?>" > 
		<div class="row_summary" siteid="<?php echo $siteID; ?>" view="<?php echo $view; ?>" parent_flag ='<?php echo $parentFlag; ?>'>
			<div class="row_arrow" ></div> <?php echo $rowChecboxForError; ?>
			<div class="row_name searchable"> <?php echo $data['name']; ?> </div>
				<?php if(empty($data['error'])) { ?> 
					<div class="row_update_count updateCount_wp_parent_<?php echo $parentFlag; ?> ">
						<span><?php echo !empty($data['isCoreUpdateAvailable'])?'1':'0'; ?></span>
					</div>
					<div class="row_update_count updateCount_plugins_parent_<?php echo $parentFlag; ?> ">
						<span><?php echo !empty($data['updatePluginCounts'])?$data['updatePluginCounts']:'0'; ?></span>
					</div>
					<div class="row_update_count updateCount_themes_parent_<?php echo $parentFlag; ?> ">
						<span><?php echo !empty($data['updateThemeCounts'])?$data['updateThemeCounts']:'0'; ?></span>
					</div>
					<div class="row_update_count updateCount_themes_parent_<?php echo $parentFlag; ?> ">
						<span><?php echo !empty($data['isTranslationUpdateAvailable'])?$data['isTranslationUpdateAvailable']:'0'; ?></span>
					</div>
				<?php } ?>
			<div class="row_action float-left"><?php echo $updateGroupForError; ?></div>
			<div class="clear-both"></div>
		

	</div>
		<div class="appendRowDetaile"></div>
</div>


<?php 
 $parentFlag ++;
 } 

$pagination = Reg::tplget('pagination');
if(empty($pagination['totalPage'])){ ?>
<div class="empty_data_set"> <div class="line2">New subsequent login activity will be logged here.</div></div>
<?php } ?>

<script>
var groupID = $('.update_by_group :selected').val();
if (typeof groupID == 'undefined' || groupID == null || groupID =='0') {
	groupID = null;
}
var pageView = "<?php echo $view; ?>";

<?php if($pagination['page'] == 1){ ?>
$("#updatePagination").show().jPaginator({
  nbVisible:5,
  nbPages:<?php echo $pagination['totalPage']; ?>,
  selectedPage:<?php echo $pagination['page']; ?>,
  overBtnLeft:'#updatePagination_o_left',
  overBtnRight:'#updatePagination_o_right',
  maxBtnLeft:'#updatePagination_m_left',
  maxBtnRight:'#updatePagination_m_right',
  withSlider: false,
  widthPx: 25,
  marginPx: 0,
  onPageClicked: function(a,num) {
      tempArray={};
      tempArray['requiredData']={};
      if (pageView == 'sites') {
	      tempArray['requiredData']['getSitesViewUpdatesContent']={};
	      tempArray['requiredData']['getSitesViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getSitesViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      tempArray['requiredData']['getSitesViewUpdatesContent']['groupID']=groupID;
	      doCall(ajaxCallPath,tempArray,'loadSitesViewPageContent');
      } else if(pageView == 'hiddenUpdates'){
      	  tempArray['requiredData']['getHiddenViewUpdatesContent']={};
	      tempArray['requiredData']['getHiddenViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getHiddenViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      tempArray['requiredData']['getHiddenViewUpdatesContent']['groupID']=groupID;
	      doCall(ajaxCallPath,tempArray,'loadHiddenViewUpdatesContent');
      } else if(pageView == 'WPVulns'){
      	  tempArray['requiredData']['WPVulnsGetWPVulnsViewUpdatesContent']={};
	      tempArray['requiredData']['WPVulnsGetWPVulnsViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['WPVulnsGetWPVulnsViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      tempArray['requiredData']['WPVulnsGetWPVulnsViewUpdatesContent']['groupID']=groupID;
	      doCall(ajaxCallPath,tempArray,'WPVulnsLoadWPVulnsViewUpdatesContent');
      }
    }
  });
<?php } ?>
<?php if(empty($pagination['totalPage'])){ ?>
$("#updatePagination").hide();
<?php } ?>
</script>

<?php }elseif(empty($d['siteSummaryData']) && !isset($d['siteDetailedData'])){ 
if(!empty($d['data']['searchKey']) && $d['data']['searchKey']!=1){
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Make sure the names are spelled correctly.</div></div> <?php
	} elseif(!empty($d['data']['groupID']) && $d['data']['groupID'] != 'null') {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">No updates available in this group.</div></div> <?php
	}else {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! Everything is up-to-date.</div></div> <?php
	}
 ?>

<script>
$("#updatePagination").hide();
$('.update_overall').hide();
$('.info_bar').hide();
</script>

<?php }

if (!empty($d['siteDetailedData'])) {
	$sites = $d['siteDetailedData'];
	$view = $d['view'];
	$childFlag = 1;
	$parentFlag = $d['parentFlag'];
	foreach ($sites as $siteID => $data) { 
		$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
							)
					);
		$siteName = DB::getField('?:sites', 'name', $where);
		$isErrorMessageActive = 'active';				//a variable to prevent adding active class to the div when its a error message
		$rowChecboxForError = '<div class="row_checkbox main_checkbox"></div>';
		$updateGroupForError = '<a class="update_group needConfirm" selector="parent_'.$parentFlag.'" parent="parent_'.$parentFlag.'"><span class="status_parent_'.$parentFlag.' statusSpan">Update All</span></a>';
		$updateErrorClass = '';
		$vulnurableParClass = '';
		if(!empty($data['error'])){
			$isErrorMessageActive = '';
			$rowChecboxForError = '';
			$updateGroupForError = '';
			$updateErrorClass = 'update_error';
		}

		foreach ($data as $itemKey => $item) {
			if (empty($item)) {
				foreach ($item as $key => $value) {
					if (!empty($value['vulnerability'])) {
						$vulnurableParClass = 'vulnurable_active';
					}
				}
			}
		}

?>
	<div class="row_detailed" style="display:none">
		<div class="rh <?php echo $vulnurableParClass; ?>">
			<div class="row_arrow"></div> <?php echo $rowChecboxForError; ?>
			<div class="row_name "> <?php echo $siteName; ?> </div>
			<div class="row_action float-left"><?php echo $updateGroupForError; ?></div>
			<div class="clear-both"></div>
		</div>
		<div class="rd"> <?php 
			foreach ($data as $property => $stats) { 
				$extraClass = 'row_child_'.$childFlag.$parentFlag;
			?>
			<div class="row_updatee <?php echo $extraClass; ?> ">	
				<div class="row_updatee_ind">
					<?php 
						$countForError = '<div class="count float-left"><span selector="child_'.$childFlag.$parentFlag.'">'.count($stats).'</span></div>';
						if($property=="core"){
							$typeName="WP";
						}
						elseif($property == 'error'){
							$typeName = 'error';
							$countForError = '';
						}
						else{
							$typeName = strtoupper($property);	
							$typeVar = ucfirst($property);
						}
					?>
					<div class="label_updatee">
						<div class="label droid700 float-left"><?php echo $typeName; ?></div><?php echo $countForError; ?>
						<div class="clear-both"></div>
					</div>
					<div class="items_cont float-left"> <?php 
					if (count($stats)>1 && $property != 'error') { ?>
						<div class="select_action select_child_<?php echo $childFlag.$parentFlag; ?>">
							<div class="select_cont float-left">
								<span>Select: </span>
								<a class="all" selector="child_<?php echo $childFlag.$parentFlag; ?>" parent="parent_<?php echo $parentFlag; ?>">All</a>
								<a class="invert" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>">Invert</a>
								<a class="none" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>">None</a>
							</div>
							<a class="action float-right update_group needConfirm" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>">
								<span class="status_child_<?php echo $childFlag.$parentFlag; ?> statusSpan">Update All </span>
								<span class="typeVar typeVar_child_<?php echo $childFlag.$parentFlag; ?>"><?php echo $typeVar; ?></span>
							</a>
							<div class="clear-both"></div>
						</div>
					<?php } 
						if ($property == 'error') {
							$hyphen = '';
							if ($property != 'core') {
								$oversionContent = '';
								$hyphen = ' -';
								$itemName='error';
								$uType=$property;
							}
							$itemClasses='active';
							$checkBoxClass='';
							$hiddenButton="Hide";
							$items = ''; ?> 
							<div class="item_ind float-left parent_<?php echo $parentFlag; ?> child_<?php echo $childFlag.$parentFlag; ?> hasParent" iname="<?php echo $itemName; ?>" style="width:100%" selector="child_<?php echo $childFlag.$parentFlag; ?>" parent="parent_<?php echo $parentFlag; ?>" did="<?php echo $items; ?>" sid="<?php echo $siteID; ?>"  utype="<?php echo $uType; ?>" onclick=""></div>
					</div> <?php } else {
						foreach ($stats as $items => $itemsval) {
							$hyphen = '';
							$oldVersion = '';
							$vulnurableActiveClass = '';
							$vulUrlDiv = '';
							$vulnWarning = '';
							if(!empty($itemsval['old_version'])){
								$oldVersion = 'v'.$itemsval['old_version'];
							}elseif(!empty($itemsval['version'])) {
								$oldVersion = 'v'.$itemsval['version'];
							}
							if ($property == 'core') {
								$oversionContent = '<a class="cutClass">v'.$itemsval['current_version'].'</a>';
								$iversionContent='<a href="'.WP_CHANGELOG_URL.'Version-'.$items.'/" target="_blank">v'.$items.'</a>';
								$uType= strtolower($property);
								$itemName='';
								$hyphen = '';
							}elseif($property == 'translations'){
								$oversionContent = 'Some of your translations are no longer up ';
								$iversionContent	= 'date.';
								$uType=strtolower($property);
								$itemName='';
								$hyphen = '';
							}else{
								$oversionContent = '<a class="cutClass">'.$oldVersion.'</a>';
								$hyphen = ' -';
								$itemName=$itemsval['name'];
								if($property=="plugins"){
									$iversionContent='<a href="'.WP_PLUGIN_CHANGELOG_URL.''.$itemsval['slug'].'/developers/" target="_blank">v'.$itemsval['new_version'].'</a>';
								}
								else{
									$iversionContent='<a class="cutClass">v'.$itemsval['new_version'].'</a>';
								}
								$uType = $property;
							}

							$itemClasses = 'active';
							$checkBoxClass = '';
							$hiddenButton = "Hide";
							if ($view == 'hiddenUpdates') {
								$hiddenButton = "Un Hide";
							}
							if (!empty($itemsval['vulnerability'])) {
								$vulnurableActiveClass = 'vulnurable_active';
								$vulUrlDiv = '  <a class="float-right" href="'.$itemsval['vulUrl'].'" target="_blank">View vulnerablity</a>';
								$vulnWarning = '<span class="vulns_warning"></span>'; 
							}
							if($property == 'translations'){
								$oversionContent = "Translation updates are available"; ?>

								<div style="width:100%" class="item_ind  <?php echo $itemClasses; ?> float-left parent_<?php echo $parentFlag; ?> child_<?php echo $childFlag.$parentFlag; ?> selectOption hasParent " iname="<?php echo $itemName; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" parent_<?php echo $parentFlag; ?> did="<?php echo $items; ?>" sid="<?php echo $siteID; ?>" utype="<?php echo $uType; ?>" onclick="">
									<div class="row_checkbox <?php echo $vulnurableActiveClass; ?>" <?php echo $checkBoxClass; ?> > </div>
									<div class="item <?php echo $vulnurableActiveClass; ?>"><?php echo $itemName.$hyphen; ?><span class="version"><?php echo $oversionContent; ?></span>
									</div>
									<div class="actions">
										<a class="float-left update_single" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" >Update</a>
										 <a class="float-left hideItem" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" ><?php echo $hiddenButton; ?></a>
									</div>
								</div>
							<?php } else{ ?>
								<div style="width:100%" class="item_ind  <?php echo $itemClasses; ?> float-left parent_<?php echo $parentFlag; ?> child_<?php echo $childFlag.$parentFlag; ?> selectOption hasParent " iname="<?php echo $itemName; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" parent_<?php echo $parentFlag; ?> did="<?php echo $items; ?>" sid="<?php echo $siteID; ?>" utype="<?php echo $uType; ?>" onclick="">
									<div class="row_checkbox <?php echo $vulnurableActiveClass; ?>" <?php echo $checkBoxClass; ?> > </div>
									<div class="item <?php echo $vulnurableActiveClass; ?>"><?php echo $vulUrlDiv; ?><?php echo $itemName.$hyphen; ?><span class="version"><?php echo $oversionContent; ?></span> to <span class="version"><?php echo $iversionContent; ?></span><?php echo $vulnWarning; ?>
									</div>
									<div class="actions">
											<a class="float-left update_single" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" >Update</a>
											 <a class="float-left hideItem" parent="parent_<?php echo $parentFlag; ?>" selector="child_<?php echo $childFlag.$parentFlag; ?>" ><?php echo $hiddenButton; ?></a>
									</div>	
								</div>	

							<?php }
						 } 
					} ?>
	<?php   ?>
			</div>
				<div class="clear-both"></div>
			</div>
		</div>
	
	<?php $childFlag++; } ?>
	</div>
	<?php $parentFlag ++;
	} 
}
elseif(isset($d['siteDetailedData']) && empty($d['siteDetailedData'])){ 
	
	?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! Everything is up-to-date or site data is loading in the background (Re-visit updates page to refresh the list).</div></div> <?php
}