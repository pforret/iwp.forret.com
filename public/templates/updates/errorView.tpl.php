<?php 
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
if (!empty($d['siteData'])) {
	$sites = $d['siteData'];

$parentFlag = 0;
foreach ($sites as $siteID => $data) { 
	$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
						)
				);
		$siteName = DB::getField('?:sites', 'name', $where);
	$parentFlag.' statusSpan">Update All</span></a>';
	$updateErrorClass = '';
	$vulnurableParClass = '';
	$isErrorMessageActive = '';
	$rowChecboxForError = '';
	$updateGroupForError = '';
	$updateErrorClass = 'update_error';

?>

	<div class="ind_row_cont <?php echo $updateErrorClass; ?> js_sites  visible parent_<?php echo $parentFlag; ?>" selector="parent_<?php echo $parentFlag; ?>" siteid="<?php echo $siteID; ?>"  > 
		<div class="row_summary" siteid="<?php echo $siteID; ?>">
			<div class="row_arrow" ></div>
			<div class="row_name searchable"> <?php echo $siteName; ?> </div>
			<div class="row_action float-left"></div>
			<div class="clear-both"></div>
		</div>
	<div class="row_detailed" style="display:none">
		<div class="rh">
			<div class="row_arrow"></div> 
			<div class="row_name "> <?php echo $siteName; ?> </div>
			<div class="row_action float-left"></div>
			<div class="clear-both"></div>
		</div>
		<div class="rd"> <?php 
			foreach ($data as $property => $stats) { 
				$extraClass = 'row_child_'.$childFlag.$parentFlag;
			?>
			<div class="row_updatee <?php echo $extraClass; ?> ">	
				<div class="row_updatee_ind">
					<?php 
						
						if($property == 'error'){
							$typeName = 'error';
							$countForError = '';
						}
					?>
					<div class="label_updatee">
						<div class="label droid700 float-left"><?php echo $typeName; ?></div><?php echo $countForError; ?>
						<div class="clear-both"></div>
					</div>
					<div class="items_cont float-left"> <?php 
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
							<div class="item_ind float-left parent_<?php echo $parentFlag; ?> child_<?php echo $childFlag.$parentFlag; ?> hasParent" iname="<?php echo $itemName; ?>" style="width:100%" selector="child_<?php echo $childFlag.$parentFlag; ?>" parent="parent_<?php echo $parentFlag; ?>" did="<?php echo $items; ?>" sid="<?php echo $siteID; ?>"  utype="<?php echo $uType; ?>" onclick=""><div class="item"><?php echo $stats; ?> </div></div>
					</div> <?php } ?>
	<?php  $childFlag++; ?>
			</div>
				<div class="clear-both"></div>
			</div>
		</div>
	
	<?php } ?>
	</div>
	<?php $parentFlag ++;
 } 
  }
?>

</div>
