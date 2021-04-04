<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
 
$data = $d['result'];
$seperate = 0;
$plugin = '';
$theme = '';
$site = '';
$and = '';
$backup = '';
$backupSite = '';
$malwareSite  = '';
$malwareCount = '';
$cloneAndStaging = '';
$stagingSite = '';
$install = '';
if (!empty($data['updates']['plugin']) && $data['updates']['plugin'] >1 ) {
	$plugin = $data['updates']['plugin'].' plugins';
}elseif (!empty($data['updates']['plugin'])) {
	$plugin = $data['updates']['plugin'].' plugin';
}

if (!empty($data['updates']['theme']) && $data['updates']['theme'] >1 ) {
	$theme = $data['updates']['theme'].' themes';
}elseif (!empty($data['updates']['theme'])) {
	$theme = $data['updates']['theme'].' theme';
}

if (!empty($data['updates']['theme']) && !empty($data['updates']['plugin'])){
	$and = 'and';
}

if (!empty($data['updates']['sites']) && $data['updates']['sites'] >1 ) {
	$site = $data['updates']['sites'].' sites.';
}elseif (!empty($data['updates']['sites'])) {
	$site = $data['updates']['sites'].' site.';
}

if (!empty($data['backup']['sites']) && $data['backup']['sites'] >1 ) {
	$backupSite = $data['backup']['sites'].' sites';
}elseif (!empty($data['backup']['sites'])) {
	$backupSite = $data['backup']['sites'].' site';
}

if (!empty($data['backup']['backupCount']) && $data['backup']['backupCount'] >1 ) {
	$backup = $data['backup']['backupCount'].' times';
}elseif (!empty($data['backup']['backupCount'])) {
	$backup = $data['backup']['backupCount'].' time';
}

if (!empty($data['malwareScanningSucuri']['scanCount']) && $data['malwareScanningSucuri']['scanCount'] >1 ) {
	$malwareCount = $data['malwareScanningSucuri']['scanCount'].' times';
}elseif (!empty($data['malwareScanningSucuri']['scanCount'])) {
	$malwareCount = $data['malwareScanningSucuri']['scanCount'].' time';
}

if (!empty($data['malwareScanningSucuri']['sites']) && $data['malwareScanningSucuri']['sites'] >1 ) {
	$malwareSite = $data['malwareScanningSucuri']['sites'].' sites';
}elseif (!empty($data['malwareScanningSucuri']['sites'])) {
	$malwareSite = $data['malwareScanningSucuri']['sites'].' site';
}

if (!empty($data['cloneAndStaging']['installClone']) && $data['cloneAndStaging']['installClone']>1) {
	$install = 'installations';
}elseif (!empty($data['cloneAndStaging']['installClone'])) {
	$install = 'installations';
}
$installCloneCount = $data['cloneAndStaging']['installClone'];
if (!empty($data['cloneAndStaging']['stagingSite']) && $data['cloneAndStaging']['stagingSite']>1) {
	$stagingSite = $data['cloneAndStaging']['stagingSite'].' sites';
}elseif (!empty($data['cloneAndStaging']['stagingSite'])) {
	$stagingSite = $data['cloneAndStaging']['stagingSite'].' site';
}


if (!empty($install) && !empty($stagingSite)) {
	$cloneAndStaging = 'INSTALL & STAGE';
}elseif (!empty($install)) {
	$cloneAndStaging = 'INSTALL';
}else{
	$cloneAndStaging = 'STAGE';
}

//subject starts here
?> Summary of work done this week using InfiniteWP. <?php

//subject ends here
echo '+-+-+-+-+-+-+-+-+-+-+-+-+-mailTemplates+-+-+-+-+-+-+-+-+-+-+-';

//message starts here

?>

Hey <?php echo $d['firstName']; ?>,<br><br>

It’s been a week since you started trying out InfiniteWP. I hope we have lived upto your expectations and even exceeded them. Here’s a summary of what you achieved this week using InfiniteWP. 🍸<br><br>
<table style=" border-collapse: collapse;">
			<tr> 
			<?php if ($data['updates']) { 
				$seperate++;
				?>
				<td style="border: 1px solid ;padding: 8px; <?php if(count($data) == 1) echo "text-align: center"; else echo "text-align: left"; ?>" <?php if(count($data) == 1) echo 'colspan="2"'; ?> > <b>UPDATES</b><br>
					<?php echo $plugin ?> <?php echo $and ?> <?php echo $theme ?> updating in <?php echo $site ?>
				</td>
			<?php } ?>
			<?php if ($data['backup']) { 
				$seperate++;
				?>
				<td style="border: 1px solid ;text-align: left;padding: 8px;"> <b>BACKUPS</b><br>
					<?php echo $backupSite ?> backed up a total of <?php echo $backup ?>
				</td>
			<?php } ?>
			<?php if ($seperate >=2){$seperate=0; echo "</tr><tr>"; } ?> 
			<?php if ($data['malwareScanningSucuri']) { 
				$seperate++;
				?>
				<td style="border: 1px solid ;padding: 8px; <?php if(count($data) == 3) echo "text-align: center"; else echo "text-align: left"; ?>" <?php if(count($data) == 3) echo 'colspan="2"'; ?>> <b>MALWARE SCAN</b><br>
					<?php echo $malwareSite ?> scanned a total <?php echo $malwareCount ?> 
				</td>
			<?php } ?>
			<?php if ($seperate >=2){$seperate=0; echo "</tr><tr>"; } ?> 
			<?php if ($data['cloneAndStaging']) { 
				$seperate++;
				?>
				<td style="border: 1px solid ;padding: 8px; <?php if(count($data) == 3) echo "text-align: center"; else echo "text-align: left"; ?>" <?php if(count($data) == 3) echo 'colspan="2"'; ?>><b><?php echo $cloneAndStaging ?></b><ul style="padding: 0px;margin: 0px;">
					<?php if(!empty($install)) { echo "<li>$installCloneCount new $install were done</li>"; } if(!empty($stagingSite)) { echo "<li>Staging environment was setup for $stagingSite</li>"; } ?>
				</ul>
				</td>

			<?php } ?>
			</tr>
			<tr><th colspan="2" style="border: 1px solid;text-align: center; padding: 8px;"><a href="<?php echo APP_URL; ?>">[Send this to the client]</a></th></tr>


</table>
<br><br>
That's some great work, right there! Do let me know if you find InfiniteWP valuable. And if you do, consider subscribing to one of our premium plans.<br><br>
<a href="<?php echo IWP_NEW_SITE_URL; ?>pricing/?utm_source=trial&utm_medium=email&utm_campaign=pricing">Check out the Premium Plans now -></a><br><br>

Cheers,<br>
David.<br><br>

P.S.: As always, if you have any questions, just reply to this email and we'll get back to you asap.
