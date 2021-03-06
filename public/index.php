<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/

include("includes/app.php");
onBrowserLoad();
initMenus();
if(function_exists('multiUserStatus')){
    multiUserStatus();
}else{
    if(userStatus() != 'admin'){
            userLogout();
    }
}

$mainJson = jsonEncoder(panelRequestManager::getSitesUpdates());
$toolTipData = jsonEncoder(panelRequestManager::getUserHelp());
$favourites =  jsonEncoder(panelRequestManager::getFavourites());
$sitesData = jsonEncoder(panelRequestManager::getSites());
$sitesListData = jsonEncoder(panelRequestManager::getSitesList());
$groupData = jsonEncoder(panelRequestManager::getGroupsSites());
$groupOrderData = jsonEncoder(panelRequestManager::getGroupsSitesOrderByName());
$updateAvailable = jsonEncoder(checkUpdate(false, false));
$updateAvailableV3 = jsonEncoder(getCachedV3UpdateDetails());
$switchToV3 = getOption('V3Installed');
if($switchToV3 == '1'){
    $cronFrequencyV3 = jsonEncoder(getRealSystemCronRunningFrequency(false, true));
}
$updateAvailableNotify = jsonEncoder(panelRequestManager::isUpdateHideNotify());
$totalSettings =  jsonEncoder(array("data"=>panelRequestManager::requiredData(array("getSettingsAll"=>1))));
$fixedNotifications = jsonEncoder(getNotifications(true));
$cronFrequency = jsonEncoder(getRealSystemCronRunningFrequency());
$clientUpdatesAvailable=jsonEncoder(panelRequestManager::getClientUpdateAvailableSiteIDs());
$purchasedAddons = jsonEncoder(Reg::get('purchasedAddons'));
$allAddonsBought = jsonEncoder(Reg::get('addonDetails'));
$multiUserAllowAccess = jsonEncoder(panelRequestManager::requiredData(array("multiUserAllowAccess"=>1)));

//for notification center html
$notifCenterData = jsonEncoder(panelRequestManager::requiredData(array("updateNotificationContent"=>1)));
$favouritesGroupData = json_encode(panelRequestManager::getFavouritesGroups());

$isAddonPlanLimitExceeded = panelRequestManager::checkIsAddonPlanLimitExceeded();
$isAddonPlanExpired = panelRequestManager::checkIsAddonPlanExpired();
$addonSuitePlanActivity = panelRequestManager::getAddonSuitePlanActivity();
$addonPlanSiteLimit = panelRequestManager::getAddonPlanSiteLimit();
$activePlanPurchasedDate = panelRequestManager::getActivePlanPurchasedDate();
$activeTierPlan = panelRequestManager::getActiveTierPlan();
$currentTimestamp = panelRequestManager::getCurrentTimestamp();
$priceForAllPlans = jsonEncoder(panelRequestManager::getAllPricingPlans());
$IDForAllPlans = jsonEncoder(panelRequestManager::getAllIDsToUpgrade());
$allPlansSiteLimits = jsonEncoder(panelRequestManager::getallPlansSiteLimits());
$addonSuiteLimitExceededIllegally = jsonEncoder(Reg::get('addonSuiteLimitExceededIllegally'));
$appRegisteredUser = getOption("appRegisteredUser");
$completedInitialSetup = panelRequestManager::isInitialSetupCompleted();
$completedAddonsInstallation = panelRequestManager::iscompletedAddonsInstallation();
$addonPlanLimitExceed = jsonEncoder(panelRequestManager::isAddonPlanLimitExceededAttemptDetails());
$addonPlanDeails = jsonEncoder(panelRequestManager::addonSuiteLimitExceededDetails());
$infinitewpAccountCredentials = jsonEncoder(panelRequestManager::getInfinitewpAccountCredentials());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "//www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="//www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="robots" content="noindex">
<title>InfiniteWP</title>
<link href='//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,700italic,800italic,400,300,700,800' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="lib/JqueryfileTree/jqueryFileTree.css" type="text/css" media="all" />
<?php
$CSSFiles = array(
    array('css' => array('href' => 'css/select2.min.css')),
    array('css' => array('href' => 'css/reset.min.css')),
    array('css' => array('href' => 'css/core.css')),
    array('css' => array('href' => 'css/datepicker.min.css')),
    array('css' => array('href' => 'css/jPaginator.min.css')),
    array('css' => array('href' => 'css/jquery-ui.min.css')),
    array('css' => array('href' => 'css/nanoscroller.min.css')),
    array('css' => array('href' => 'css/custom_checkbox.min.css')),
    array('css' => array('href' => 'css/jquery.qtip.min.css')),
    array('css' => array('href' => 'css/font-awesome-4.6.3/css/font-awesome.min.css')),
);

$panelHeadCache = new createPanelCache($CSSFiles);
echo $panelHeadCache->initiateCacheProcess(); ?>

<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon"/>
<!-- <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
 --><!--[if lt IE 9]>
	<link rel="stylesheet" type="text/css" href="css/ie8nlr.css?<?php echo APP_VERSION; ?>" />
<![endif]-->

<?php
$JSFiles = array(
    array('js' => array('href' => 'js/jquery.min.js')),
    array('js' => array('href' => 'js/jquery-ui.min.js')),
    array('js' => array('href' => 'js/clipboard.min.js')),
    array('js' => array('href' => 'js/select2.min.js')),
    array('js' => array('href' => 'js/fileuploader.min.js')),
    array('js' => array('href' => 'js/apps.js')),
    array('js' => array('href' => 'js/load.js')),
    array('js' => array('href' => 'js/jPaginator.min.js')),
    array('js' => array('href' => 'js/jquery.qtip.min.js')),
    array('js' => array('href' => 'js/jquery.mousewheel.min.js')),
    array('js' => array('href' => 'js/jquery.encode.min.js')),
    array('js' => array('href' => 'js/lazysizes.min.js')),
    array('js' => array('href' => 'lib/JqueryfileTree/jquery.easing.js')),
    array('js' => array('href' => 'lib/JqueryfileTree/jqueryFileTree.js'))
);
echo $panelHeadCache->loadJSFallBackMethod($JSFiles); ?>

<script>
var globalMessageFlagForMini=0;
var permissions = <?php echo $multiUserAllowAccess; ?>;
var systemURL = "<?php echo APP_URL;?>";
var serviceURL = "<?php echo getOption('serviceURL');?>";
$(document).ready(function(){
    iwpCronInvitied = "<?php echo getOption('iwpCronInvited');?>";
    var notifyUser = "<?php echo getOption('iwpCrontInvitedNotificationReq');?>";
    if(iwpCronInvitied && notifyUser){
        showIWPCronInviteDialog();
        resetBottomToolbar();
    }
});

var appVersion = "<?php echo APP_VERSION; ?>";
var appInstallHash = "<?php echo APP_INSTALL_HASH; ?>";
var IP = "<?php echo $_SERVER['REMOTE_ADDR']; ?>";
var APP_PHP_CRON_CMD = "<?php echo APP_PHP_CRON_CMD; ?>";
var APP_ROOT = decodeURIComponent("<?php echo rawurlencode(APP_ROOT); ?>");
var tempCronFrequency = "<?php echo $cronFrequency; ?>";
if (tempCronFrequency == 0) {
  var CRON_FREQUENCY = '<div class="rep_sprite_backup block_fail_icon" style="right:10px;margin-top:5px;">Server cron is not running.</div>' ;
} else {
  var CRON_FREQUENCY = '<div class="rep_sprite_backup block_success_icon" style="right:10px;margin-top:5px;">This cron is running every '+tempCronFrequency+' min</div>' ;
}

// v3 cron 
var switchToV3 = "<?php echo $switchToV3; ?>";
if(switchToV3 == '1'){
    var tempCronFrequencyV3 = "<?php echo $cronFrequencyV3; ?>";
    if (tempCronFrequencyV3 == 0) {
      var CRON_FREQUENCYV3 = '<div class="rep_sprite_backup block_fail_icon" style="right:10px;margin-bottom:10px;">Server cron v3 is not running.</div>' ;
    } else {
      var CRON_FREQUENCYV3 = '<div class="rep_sprite_backup block_success_icon" style="right:10px;margin-bottom:15px;">v3 cron is running every '+tempCronFrequencyV3+' min</div>' ;
    }
 }
// 

var GAHelpTracking = "<?php echo GA_HELP_TRACKING ?>";
var supportURL = "<?php echo SUPPORT_URL ?>";
var WPChangeLogURL = "<?php echo WP_CHANGELOG_URL?>";
var WPPluginChangeLogURL = "<?php echo WP_PLUGIN_CHANGELOG_URL?>";
var mainJson = <?php echo $mainJson?>;
var sitesjson = mainJson.siteView;
var hiddenUpdateCount = 0;
var securityUpdateCount = 0;
var siteErrorCount = 0;
var pluginsUpdateCount = 0;
var WPUpdateCount = 0;
var themesUpdateCount= 0;
var wooDBUpdateCount= 0;
var transUpdateCount = 0;
var totalPluginUpdateCount = 0;
var totalThemeUpdateCount = 0;
var totalCoreUpdateCount = 0;
var totalTranslationUpdateCount = 0;
var vulnsThemeUpdateCount = 0;
var vulnsCoreUpdateCount = 0;
var vulnsThemeUpdateCount = 0;
var activeProcessQueueActionID = false;
var vulnerableSiteIDs = [];
var WPVulnsHiddenCount = [];
var toolTipData = <?php echo $toolTipData;?>;
var favourites = <?php echo $favourites; ?>;
var site = <?php echo  $sitesData;?>;
var sitesList = <?php echo  $sitesListData;?>;
var group = <?php echo  $groupData;?>;
var groupOrderByName = <?php echo  $groupOrderData;?>;
var favouritesGroupData = <?php echo $favouritesGroupData ?>;
var totalSites = getPropertyCount(site);
var totalGroups = getPropertyCount(group);
var totalUpdates =  getPropertyCount(mainJson);
var pluginsStatus,themesStatus;
var updateAvailable   = <?php echo $updateAvailable;?>;
var updateAvailableV3   = <?php echo $updateAvailableV3;?>;
var updateAvailableNotify=<?php echo $updateAvailableNotify;?>;
var fixedNotifications = <?php echo $fixedNotifications;?>;
var settingsData = <?php echo $totalSettings; ?>;
var addonPlanDeails = <?php echo $addonPlanDeails; ?>;
var addonPlanLimitExceed = <?php echo $addonPlanLimitExceed; ?>;
var fromEmailDetails = "<?php echo getDefaultFromEmail(); ?>";
var clientUpdatesAvailable=<?php echo $clientUpdatesAvailable;?>;
settingsData['data']['getSettingsAll']['settings']['timeZone'] = '';
var forcedAjaxInterval = <?php echo FORCED_AJAX_CALL_MIN_INTERVAL; ?>;	
var completedInitialSetup = <?php echo $completedInitialSetup; ?>;
var completedAddonsInstallation = <?php echo $completedAddonsInstallation; ?>;
var infinitewpAccountCredentials = <?php echo $infinitewpAccountCredentials; ?>;
var isShowBetaWelcome ="<?php echo isShowBetaWelcome();?>"
var isShowReSchedulePopup ="<?php echo isShowReSchedulePopup();?>"
//notification center JS object
var notifCenterAllData = {};
var isInnoDBConversionNeeded = "<?php echo showConversionNeededTableNames(); ?>"
notifCenterAllData['data'] = {};
notifCenterAllData['data'] = <?php echo $notifCenterData;?>;
var isDisabled2FA = <?php echo (defined('DISABLE_2FA'))?1:0; ?>;
var isHTTPSDefined = <?php echo $GLOBALS['isHTTPSDefined']; ?>;
var currentUserAccessLevel = "<?php echo userStatus(); ?>";
var isDirectFS = "<?php echo getOption('isDirectFS');  ?>"
var isAddonPlanLimitExceeded = parseInt("<?php echo($isAddonPlanLimitExceeded); ?>");
var addonSuitePlanActivity = "<?php echo($addonSuitePlanActivity); ?>";
var activeTierPlan = "<?php echo($activeTierPlan); ?>";
var addonPlanSiteLimit = parseInt("<?php echo($addonPlanSiteLimit); ?>");
var addonSuiteLimitExceededIllegally = parseInt("<?php echo($addonSuiteLimitExceededIllegally); ?>");
var isAddonSuiteLimitExceededAttempt = 0;
var isAddonPlanExpired = parseInt("<?php echo($isAddonPlanExpired); ?>");
var isConfigWritable=<?php echo isConfigWritable(); ?>;
var isFSMethodDefined=<?php echo defined('FS_METHOD')?1 :0; ?>;
var priceForAllPlans = <?php echo $priceForAllPlans?>;
var IDForAllPlans = <?php echo $IDForAllPlans?>;
var allPlansSiteLimits = <?php echo $allPlansSiteLimits?>;
var activePlanPurchasedDate = parseInt("<?php echo($activePlanPurchasedDate); ?>");
var currentTimestamp = parseInt("<?php echo($currentTimestamp); ?>");
var appRegisteredUser = "<?php echo($appRegisteredUser); ?>";
var lastPanelUpdated = "<?php echo getLastPanelUpdated(); ?>";
if (appRegisteredUser == 'trialIWPPanel') {
  iwpTrailPanel = true;
}
var switchToV3 = "<?php echo($switchToV3); ?>";
var IWPSiteURL = "<?php echo(IWP_SITE_URL); ?>";
var googleSettings='';
var cpBrandingSettings='';
var uptimeMonitoringSettings='';
var WPTCSettings='';
var googleAnalyticsAccess='';
var googleWebMastersAccess='';
var googlePageSpeedAccess='';
var googleSafeBrowsingAccess='';
var scheduledBackupSettings = '';
var purchasedAddonsGlobal = <?php echo $allAddonsBought; ?>;
var markBottomLines = <?php echo jsonEncoder(json_decode(getOption('markBottomLines'), true)); ?>;// jsonEncoder(json_decode()) this will help empty content to hv null data
var threeSitesTweetStatus = '<?php if(!getOption("tweet_status_three_sites")){ echo "no"; }else{ echo getOption("tweet_status_three_sites"); } ?>';  //storing global variable for displaying tweet
var updateAllTweetStatus = '<?php if(!getOption("tweet_status_update_all")){ echo "no"; }else{ echo getOption("tweet_status_update_all"); } ?>';

<?php echo getAddonHeadJS(); ?>
<?php if(!empty($_REQUEST['page'])) {?>
	reloadStatsControl=0;
<?php }  ?>
</script>
<?php
$bottomJSFiles = array(
    array('js' => array('href' => 'js/init.js')),
    array('js' => array('href' => 'js/jquery.nanoscroller.min.js')),
    array('js' => array('href' => 'js/datepicker.min.js')),
    array('js' => array('href' => 'js/eye.min.js')),
    array('js' => array('href' => 'js/utils.min.js')),
    array('js' => array('href' => 'js/layout.min.js')),
);
echo $panelHeadCache->loadJSFallBackMethod($bottomJSFiles); ?>

<?php if(userStatus() != 'admin'){ ?>
<script>
$(function () {
	$(".settingsButtons").click();
});
</script>
<?php } ?>
<!-- addon ext src starts here -->
<?php 
if($addonSuiteLimitExceededIllegally==0) echo getAddonsHTMLHead(); 
?>
<?php if(!empty($_REQUEST['page']))
{ ?>
	<script>
    $(function () { 
        reloadStatsControl=0;
        <?php if($_REQUEST['page']=="addons") ?>
            $("#iwpAddonsBtn").click();		
        processPage("<?php echo $_REQUEST['page'];?>");
    
    });
    </script>
<?php } ?>
<style>
@media only screen and (max-width : 1360px) {
ul#header_nav li.resp_hdr_logout { display:inline; }
#header_bar a.logout { display:none;}
}
</style>
</head>
<body>
<div class="notification_cont"></div>
<div id="fb-root"></div>
<div id="updateOverLay" style='display:none;-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=70)"; background-color:#000; opacity:0.7;position:fixed;top: 0;left: 0;width: 100%;height: 100%; z-index:1020'></div>
<div id="loadingDiv" style="display:none">Loading...</div>
<div id="modalDiv"></div>

<!--<div class="overlay_test"></div>-->
<?php if($appRegisteredUser == 'trialIWPPanel'){ ?>

<div class="iwp-trial-baner"><span class="infiniteWP-Trail-font">InfiniteWP Trial</span> <span class="iwp-trail-move-panel float-right"><a class="iwp-trail-move-panel-text" href="<?php echo SUPPORT_URL ?>solution/articles/235590-how-to-move-a-hosted-panel-to-your-server/?utm_source=trial&utm_medium=panel&utm_campaign=move" target="_blank">Move your panel</a></span><span class="iwp-trail-choose-plan float-right"><a class="iwp-trail-choose-plan-text" href="<?php echo IWP_NEW_SITE_URL;?>pricing/?utm_source=trial&utm_medium=panel&utm_campaign=pricing" target="_blank" style="color: #ffffff;">Choose a plan</a></span><span class="float-right iwp-trail-baner-note">This instance will be deleted once the trial ends in 14 days </span></div>
  
<?php } ?>
<div id="dynamic_resize">
<div id="header_bar" <?php if($appRegisteredUser == 'trialIWPPanel') echo 'style ="margin-top: 48px;"'; ?> > <div class="header_container"><a href="<?php echo APP_URL; ?>" style="text-decoration:none;">
    <div id="logo"></div></a>
    <div id="admin_panel_label">Admin Panel v<?php echo APP_VERSION; ?></div>
    
    <a class="float-left fb_icon_hdr" href="//www.facebook.com/infinitewp" target="_blank"></a><a class="float-left twitter_icon_hdr" href="//twitter.com/infinitewp" target="_blank"></a>
    <ul id="header_nav">
      <!--<li><a href="">Suggest an Idea</a></li>-->
      <li class="notif_wrap" style="margin-right:16px; position:relative;"> <a class="notif_btn" style="display:none" ><i class="fa fa-bell-o"></i></a> <span class="notif_count" style="display:none" > </span>
        <div class="notification_centre_cont" style= "display:none">
          <div style="">
            <ul class="notif_data_list">
            </ul>
            <div class="weekly_deal_cont cf">
            </div>
          </div>
        </div>
      </li>
      <?php if(userStatus() == 'admin'){ ?>
        <li class="restrictCronToggle"><a class="updates_centre first-level" id="updateCentreBtnV3" style="display:none">v3<span id="updateCenterCountV3" style="display:none" class="count">1</span></a>
          
          <div id="updates_centre_cont_V3" style="display:none">
                     
            <div class="th rep_sprite" style="border-top: 1px solid #C1C4C6; height: 38px; border-bottom: 0;">
              <div class="btn_action float-right"><a class="rep_sprite updateActionBtnV3">Check Now</a></div>
              
            </div>
          </div>
        </li>
         <li class="restrictCronToggle"><a href="<?php echo trim(APP_URL, '/').'/v3'; ?>" class="first-level" id="switchToV3" style="display:none">Switch to v3</a>
        </li>
        <li class="restrictCronToggle"><a class="updates_centre first-level" id="updateCentreBtn">IWP Update Centre<span id="updateCenterCount" style="display:none" class="count">1</span></a>
      	
        <div id="updates_centre_cont" style="display:none">
                   
          <div class="th rep_sprite" style="border-top: 1px solid #C1C4C6; height: 38px; border-bottom: 0;">
            <div class="btn_action float-right"><a class="rep_sprite updateActionBtn">Check Now</a></div>
            
          </div>
        </div>
      </li>
      
       <li><a class="first-level" id="iwpAddonsBtn">Addons <span class="count" style="<?php if(($addonUpdate = getAddonAlertCount()) < 1){ ?>display:none;<?php } ?>"><?php echo $addonUpdate; ?></span></a></li><?php } ?>
       <li><a class="first-level" href="https://trello.com/b/V6VVdo7i/infinitewp-ideas" target="_blank">Got an idea?</a></li>
      <li class="help"><a class="first-level">Help <span style="font-size:7px;">???</span></a>
      	<ul class="sub_menu_ul">
        	  <?php /* <li><a href="https://support.infinitewp.com/?utm_source=application&utm_medium=userapp&utm_campaign=support" target="_blank">Discussion Forum</a></li> */ ?>
            <li><a href="javascript:loadReport('',1)">Report Issue</a></li>
<!--             <li><a class="takeTour">Take the tour</a></li> -->
        </ul>
      </li>
      <li class="user_mail"><a id="user_email_acc" style="color:#e9e9e9;"><?php echo $GLOBALS['email']; ?> </a><li>
      <li class="settings" title="Settings" id="mainSettings">
        <a id="settings_btn">Settings</a>
      </li>
	  <li class="resp_hdr_logout"><a class="first-level" href="login.php?logout=now">Logout</a></li>
    </ul>
    <div class="clear-both"></div></div><a href="login.php?logout=now" class="logout">Logout</a>
  </div>
<div id="site_cont">
  
  <div id="main_cont">
    
    <ul class="site_nav">
    	<?php printMenus(); ?>
    </ul>
    <div class="btn_reload_drop">
      <ul class="group_reload"><li class="l1"><a class="caret-down"></a>
     <ul class="l2"><?php echo printGroupsForReloaData(); ?></ul></li></ul>
    </div>
    <div class="btn_reload  float-right"><a class=" user_select_no" id="reloadStats" style=" /*position: relative;  */  display: inline-block;"><i class="fa fa-repeat" style= "line-height: 12px;position: absolute;left: 8px;font-size: 15px;top:7px;"></i>Reload Data</a></div>
    
    
	<div class="checkbox user_select_no" style="float:right; width:70px; cursor:pointer; position:relative" id="clearPluginCache">Clear cache</div>
    <div id="lastReloadTime"></div>
    <ul class="site_nav single_nav float-left"><li class="l1 navLinks" page="history"><a>Activity Log</a></li></ul>
    <div class="clear-both"></div>
    <div id="panelNotifyHtml"></div>
    <hr class="dotted" />
    <div class="page_section_title">UPDATES</div>
    <div id="pageContent">
      <div class="empty_data_set welcome">
        <div class="line1">Hey there. Welcome to InfiniteWP.</div>
        <div class="line2">Lets now manage WordPress, the IWP way!</div>
        <div class="line3">
          <div class="welcome_arrow"></div>
          Add a WordPress site to IWP.<br />
          <span style="font-size:12px">(Before adding the website please install and activate InfiniteWP Client Plugin in your WordPress site)</span> </div>
        <a href="//www.youtube.com/watch?v=q94w5Vlpwog" target="_blank">See How</a>. </div>
    </div>
  </div>
</div>
</div>
<div id="bottom_toolbar" class="siteSearch">
  <div id="activityPopup"> </div>
</div>
<script type="text/javascript" language="javascript">
	jQuery(document).ready(function() {
		if(!isNaN(addonSuiteLimitExceededIllegally) && addonSuiteLimitExceededIllegally==1) {
			globalMessageFlagForMini=1;
			addonSuiteMiniLimitExceeded('addonSuiteLimitExceededIllegally');
		}
	});
	//to display notification center content
	processUpdateNotificationContent(notifCenterAllData);	
  $(".btn_reload").qtip({content: { text: 'All websites' }, position: { my: 'top center', at: 'bottom center' }, show: { delay: 1000,effect: function() { $(this).fadeTo(500, 1);} }, hide: {inactive: 1000 }, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 9, height:4 } } });
  $(".btn_reload_drop").qtip({content: { text: 'Groups' }, position: { my: 'top center', at: 'bottom center' }, show: { delay: 1000 }, hide: { event: 'click mouseleave',inactive: 1000}, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 9, height:4 } } });
  $(".emptyGroups").qtip({content: { text: 'No sites in this group' }, position: { my: 'right center', at: 'left center' }, show: { delay: 1000 }, hide: { event: 'click mouseleave',inactive: 1000}, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 5, height:5} } });
</script>
</body>
</html>