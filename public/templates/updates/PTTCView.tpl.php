<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
$view = $d['view'];
if (!empty($d['siteSummaryData'])) {
	$WPVUlnsView = $d['WPVulns'][$view];
	$sites = $d['siteSummaryData'];
	if($view=="wp"){
		$emptyStatusType="WordPress installations";
	}else{
		$emptyStatusType=strtoupper($view);
	} ?>

<script>
$("#updatePagination").show();
$('.update_overall').show();
$('.info_bar').show();
</script>
<div class="rows_cont" style="position:relative">

<?php  
$parentFlag = 1;
foreach ($sites as $itemView => $data) { 
	if($view === 'wp'){
		$displayName = $itemView;
	}
	else{
		$displayName = $data['name'];
	}
	$totalCount = count($data);
	$pFlag=1;

	$isErrorMessageActive = 'active';				//a variable to prevent adding active class to the div when its a error message
	$rowChecboxForError = '<div class="row_checkbox main_checkbox"></div>';
	$updateGroupForError = '<a class="update_all_group needConfirm" selector="parent_'.$parentFlag.'" parent="parent_'.$parentFlag.'"><span class="status_parent_'.$parentFlag.' statusSpan">Update All</span></a>';
	$updateErrorClass = '';
	if(!empty($data['error'])){
		$isErrorMessageActive = '';
		$rowChecboxForError = '';
		$updateGroupForError = '';
		$updateErrorClass = 'update_error';
	} 
	$vulnurableParClass = '';
	if (!empty($WPVUlnsView) && !empty($WPVUlnsView[$itemView])) {
		foreach ($WPVUlnsView[$itemView] as $key => $value) {
			if (!$value['hiddenItem'] && $value['vulnurable']) {
				$vulnurableParClass = 'vulnurable_active';
				break;
			}
		}
	}

	?>

	<div class="ind_row_cont <?php echo $vulnurableParClass; ?> <?php echo $updateErrorClass; ?> <?php echo $isErrorMessageActive; ?> row_parent_<?php echo $parentFlag; ?>" parent="parent_<?php echo $parentFlag; ?>" selector="<?php echo $view; ?>" did = "<?php echo $itemView; ?>" itemID = "<?php echo $data['ID']; ?>" >
		<div class="row_summary" view="<?php echo $view; ?>" itemID = "<?php echo $data['ID']; ?>" did = "<?php echo $itemView; ?>"  parent_flag ='<?php echo $parentFlag; ?>'>
			<div class="row_arrow" ></div> <?php echo $rowChecboxForError; ?>
			<div class="row_name searchable"> <?php echo $displayName; ?> </div>
			<div class="row_action float-left"><?php echo $updateGroupForError; ?></div>
			<div class="clear-both"></div>
		</div>
		<div class="appendRowDetaile"></div>

</div>


<?php $parentFlag++; } $pagination = Reg::tplget('pagination'); 
?>
</div>
<script>
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
      if (pageView == 'plugins') {
	      tempArray['requiredData']['getPluginsViewUpdatesContent']={};
	      tempArray['requiredData']['getPluginsViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getPluginsViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
      	  doCall(ajaxCallPath,tempArray,'loadPluginsViewUpdatesContent');

      } else if (pageView == 'themes') {
      	  tempArray['requiredData']['getThemesViewUpdatesContent']={};
	      tempArray['requiredData']['getThemesViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getThemesViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      doCall(ajaxCallPath,tempArray,'loadThemesViewUpdatesContent');
      } else if (pageView == 'translations') {
      	  tempArray['requiredData']['getTranslationsViewUpdatesContent']={};
	      tempArray['requiredData']['getTranslationsViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getTranslationsViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      doCall(ajaxCallPath,tempArray,'loadTranslationsViewUpdatesContent');
      } else if (pageView == 'WP') {
      	  tempArray['requiredData']['getWPViewUpdatesContent']={};
	      tempArray['requiredData']['getWPViewUpdatesContent']['page']=num;
	      tempArray['requiredData']['getWPViewUpdatesContent']['searchKey']=$('.searchSiteUpdate').val();
	      doCall(ajaxCallPath,tempArray,'loadWPViewUpdatesContent');
      }

    }
  });
<?php } ?>
<?php if(empty($pagination['totalPage'])){ ?>
$("#updatePagination").hide();
<?php } ?>
</script>


<?php } elseif (empty($d['siteSummaryData']) && empty($d['siteDetailedData'])) { 
if(!isset($d['siteDetailedData'])){

	?>
<script>
$("#updatePagination").hide();
$('.update_overall').hide();
$('.info_bar').hide();
</script>
	
<?php  }
	if(!empty($d['data']['searchKey']) && $d['data']['searchKey']!=1){
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Make sure the names are spelled correctly.</div></div> <?php
	}elseif ($view == 'plugins') {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! All Plugins are up-to-date.</div></div> <?php
	}elseif ($view == 'themes') {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! All Themes are up-to-date.</div></div> <?php
	} elseif ($view == 'WP') {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! All WordPress Installations are up-to-date.</div></div> 
		<?php
	} elseif ($view == 'translations') {
		?> <div class="empty_data_set hiddenCheck" style="display:block"> <div class="line2">Hurray! All translations are up-to-date.</div></div> <?php
	} 

}

if (!empty($d['siteDetailedData'])) {
	$sites = $d['siteDetailedData'];
	$view = $d['view'];
	$childFlag = 1;
	$parentFlag = $d['parentFlag'];
	foreach ($sites as $itemView => $data) { 
			if($view === 'wp'){
				$displayName = $itemView;
			}
			elseif($view === 'translations'){
				$displayName = "Translation updates are available";
			}
			else{
				$firstKey = key($data);
				$displayName = $data[$firstKey]['name'];
			}
			$totalCount = count($data);
			$pFlag=1;

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

			foreach ($data as $itemKey => $value) {
				if (!empty($value['vulnurable'])) {
					$vulnurableParClass = 'vulnurable_active';
				}
			}
		?>
		<div class="row_detailed" style="display:none">
			<div class="rh <?php echo $vulnurableParClass; ?>">
				<div class="row_arrow"></div> <?php echo $rowChecboxForError; ?>
				<div class="row_name "> <?php echo $displayName; ?> </div>
				<div class="row_action float-left"><?php echo $updateGroupForError; ?></div>
				<div class="clear-both"></div>
			</div>
			<div class="rd"> <?php 
				foreach ($data as $property => $value) { 
					$where = array(
				      		'query' =>  "siteID=':siteID'",
				      		'params' => array(
				               ':siteID'=>$property
								)
						);
					$siteName = DB::getField('?:sites', 'name', $where);
					$extraClass = 'row_parent_'.$parentFlag;
				?>
				<div class="row_updatee <?php echo $extraClass; ?> ">	
					<div class="row_updatee_ind">
						<div class="items_cont_long float-left" style="width:100%">
					<?php 
						if ($view == 'wp') {
							$oversionContent = '<a class="cutClass">v'.$value['current_version'].'</a>';
							$uType ='core';
							$versionContent='<a href="'.WP_CHANGELOG_URL.'Version-'.$itemView.'/" target="_blank">v'.$itemView.'</a>';
						} else{
							$oldVersion = '';
							if(!empty($value['old_version'])) {
								$oldVersion = 'v'.$value['old_version'];
							} elseif(!empty($value['version'])) {
								$oldVersion = 'v'.$value['version'];
							}
							$oversionContent = '<a class="cutClass">'.$oldVersion.'</a>';
							if($view == "plugins"){
								$versionContent='<a href="'.WP_PLUGIN_CHANGELOG_URL.''.$value['slug'].'/developers/" target="_blank" >v'.$value['new_version'].'</a>';
							} else{
								$versionContent='<a class="cutClass">v'.$value['new_version'].'</a>';
							}
							$uType= strtolower($view);
						}
						$itemClasses = 'active';
						$checkBoxClass = '';
						$hiddenButton = "Hide";
						$vulnurableActiveClass= '';
						$vulUrlDiv = '';
						$vulnWarning = '';
						if (!empty($value['vulnurable'])) {
							$vulnurableActiveClass = 'vulnurable_active';
							$vulUrlDiv = '  <a class="float-right" href="'.$value['vulUrl'].'" target="_blank">View vulnerablity</a>';
							$vulnWarning = '<span class="vulns_warning"></span>'; 
						}
					?>
							<div class="item_ind plugin_theme_wp_group_hide <?php echo $itemClasses; ?> float-left parent_<?php echo $parentFlag; ?> selectOption" iname="<?php echo $value['name']; ?>" parent="parent_<?php echo $parentFlag; ?>" style="width:100%" selector="parent_<?php echo $parentFlag; ?>" did="<?php echo $itemView; ?>" sid="<?php echo $property; ?>"  utype="<?php echo $uType; ?>" onclick="" >
								<div class="row_checkbox <?php echo $vulnurableActiveClass; ?>"  <?php echo $checkBoxClass; ?> > </div>
							<?php 
							if($view == 'translations'){ ?>
								<div class="item <?php echo $vulnurableActiveClass; ?>" style="width:732px"><?php echo $siteName; ?></div>
								<div class="actions">
									<a class="float-left update_single" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" >Update</a>
									<a class="float-left hideItem" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" ><?php echo $hiddenButton; ?></a>
								</div>
							</div>
							<?php }else { ?> 
								<div class="item <?php echo $vulnurableActiveClass; ?>" style="width:732px"><?php echo $vulUrlDiv; ?><?php echo $siteName; ?> - <span class="version"><?php echo $oversionContent; ?></span> to <span class="version"><?php echo $versionContent; ?></span><?php echo $vulnWarning; ?></div>
								<div class="actions">
									<a class="float-left update_single" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" >Update</a>
									<a class="float-left hideItem" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" ><?php echo $hiddenButton; ?></a>
								</div>
							</div>
							<?php } ?>
						</div>
						<div class="clear-both"></div>
					</div>
					</div>
					
		<?php $childFlag++; }
			$parentFlag++; ?>
		<div class='clear-both'></div>
		</div>

	<?php }
	?> <?php
}