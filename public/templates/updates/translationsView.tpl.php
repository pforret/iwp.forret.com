<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
if (!empty($d['siteData'])) {
	$sites = $d['siteData'];
?>
<script>
$("#updatePagination").hide();
$('.translationsSearch').show();
$('.info_bar').hide();
</script>
<?php
$parentFlag = 1;
foreach ($sites as $itemView => $data) { 
	$displayName = "Translation updates are available";
	$totalCount = count($data);
	$pFlag=1;
	$isErrorMessageActive = 'active';				//a variable to prevent adding active class to the div when its a error message
	$rowChecboxForError = '<div class="row_checkbox main_checkbox"></div>';
	$updateGroupForError = '<a class="update_group needConfirm" selector="parent_'.$parentFlag.'" parent="parent_'.$parentFlag.'"><span class="status_parent_'.$parentFlag.' statusSpan">Update All</span></a>';
	$updateErrorClass = '';
	if(!empty($data['error'])){
		$isErrorMessageActive = '';
		$rowChecboxForError = '';
		$updateGroupForError = '';
		$updateErrorClass = 'update_error';
	} 
	$vulnurableParClass = '';
 ?>

<div class="ind_row_cont   <?php echo $vulnurableParClass; ?> <?php echo $updateErrorClass; ?> <?php echo $isErrorMessageActive; ?> row_parent_<?php echo $parentFlag; ?>" parent="parent_<?php echo $parentFlag; ?>" <?php echo $parentFlag; ?> selector="<?php echo $view; ?>" did = "<?php echo $itemView; ?>" itemID = "<?php echo $data['ID']; ?>" >
		<div class="row_summary" view="<?php echo $view; ?>" itemID = "<?php echo $data['ID']; ?>" did = "<?php echo $itemView; ?>">
			<div class="row_arrow" ></div> <?php echo $rowChecboxForError; ?>
			<div class="row_name searchable"> <?php echo $displayName; ?> </div>
			<div class="row_action float-left"><?php echo $updateGroupForError; ?></div>
			<div class="clear-both"></div>
		</div>
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
						<?php 	$oldVersion = '';
							if(!empty($value['old_version'])) {
								$oldVersion = 'v'.$value['old_version'];
							} elseif(!empty($value['version'])) {
								$oldVersion = 'v'+$value['version'];
							}
							$oversionContent = '<a class="cutClass">'.$oldVersion.'</a>';
							if($view == "plugins"){
								$versionContent='<a href="'.WP_PLUGIN_CHANGELOG_URL.''.$value['slug'].'/developers/" target="_blank" >v'.$value['new_version'].'</a>';
							} else{
								$versionContent='<a class="cutClass">v'.$value['new_version'].'</a>';
							}
							$uType= 'translations';
						
						$itemClasses = 'active';
						$checkBoxClass = '';
						$hiddenButton = "Hide";
						$vulnurableActiveClass= '';
						$vulUrlDiv = '';
						$vulnWarning = ''; ?>
						<div class="item_ind plugin_theme_wp_group_hide <?php echo $itemClasses; ?> float-left parent_<?php echo $parentFlag; ?> selectOption" iname="<?php echo $value['name']; ?>" parent="parent_<?php echo $parentFlag; ?>" style="width:100%" selector="parent_<?php echo $parentFlag; ?>" did="<?php echo $itemView; ?>" sid="<?php echo $property; ?>"  utype="<?php echo $uType; ?>" onclick="" >
								<div class="row_checkbox <?php echo $vulnurableActiveClass; ?>"  <?php echo $checkBoxClass; ?> > </div>
							<div class="item <?php echo $vulnurableActiveClass; ?>" style="width:732px"><?php echo $siteName; ?></div>
								<div class="actions">
									<a class="float-left update_single" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" >Update</a>
									<a class="float-left hideItem" parent="parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" ><?php echo $hiddenButton; ?></a>
								</div>
							</div>
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
}else{
	?>
<script>
	$('.update_overall').hide();
</script>
<div class="empty_data_set hiddenCheck" style="display:block">
	<div class="line2">Hurray! All Translations are up-to-date.</div>
</div>
	<?php
}