/************************************************************
* InfiniteWP Admin panel									*
* Copyright (c) 2012 Revmakx								*
* www.revmakx.com											*
*															*
************************************************************/
$(function () {
	$.fn.qtip.zindex = 900;
	if ( $.browser.msie ) {
		if($.browser.version=='6.0' || $.browser.version=='7.0')
		{
			window.location.href = "no_support.html";
			return false;
		}
	}
	// Resize and remove elements when the canvas size is small
	loadFixedNotifications();
	// loadSettingButtonForCron();
	if (clientUpdatesAvailable != false) {
		clientPluginUpdatesNotification();
	};
	if(runOffBrowser == 0){
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['runOffBrowserLoad']=1;
		tempArray['requiredData']['bypassHistoryAjaxCall']=1;
		runOffBrowser=1;
		doCall(ajaxCallPath,tempArray);
	}
	
	if(totalSites>0)
	{
		processPage("updates");
		$(".navLinkUpdate").addClass('active');
		if((typeof isComment != 'undefined' )&&(isComment == 1))
		{
			$(".navLinkUpdate.commentClass").removeClass('active'); //To disable the Comments tab to be active on first load
		}
	}
	siteSelector();
	siteSelector(1);
	$("#bottom_toolbar").prepend('<div id="bottomToolBarSelector">'+bottomToolbarVar+'</div>');
	$(".managePanel").prepend('<div class="siteSelectorContainer">'+siteSelectorVar+'</div>');
	updateSitesStatusColor();
	if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
		updateSiteVulnsStatus();
	}
	resetGroup();
	if(toolTipData.manageGroups=="true")
	$(".toggle_manage_groups").qtip({id:"toggleGroupQtip",content: { text: 'Manage Groups' }, position: { my: 'bottom center', at: 'top center',  adjust:{ y: -6} }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
	if(toolTipData.addSite!="true")
	$("#addWebsite").qtip({events: { hide: function(event, api) { tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['addSite']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData'); } }, id: 'addWebsiteQtip', content: { text: ' ', title: { text: 'Add your WordPress sites here', button: true } }, position: { my: 'bottom center', at: 'top center', adjust:{ y: -7} }, show: { event: false, ready: true }, hide: false, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
	if(toolTipData.adminPopup!="true")
	$(".showFooterSelector").qtip({id:"adminPopupQtip",events: { hide: function(event, api) { tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['adminPopup']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData'); } },  content: { text: ' ', title: { text: 'You can still access your other sites from here', button: true } }, position: { my: 'bottom left', at: 'top left', adjust:{ y: -7, x: 20} }, show: { event: false}, hide: true, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 10, height:5} } });
	
	
	
	if(toolTipData.reloadStats!="true")
	{
		
		$("#reloadStats").qtip({ events: { hide: function(event, api) { tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['reloadStats']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData'); } }, id: 'reloadStatsQtip', content: { text: ' ', title: {text: 'Fetch real time data from all websites', button: true } }, position: { my: 'right center', at: 'left center', adjust:{ x: -6} }, show: { event: false, ready: true }, hide: false, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 5, height:8} } });
	}
	
	$(".valid_error").hide();
	updateCountRefresh();
	historyRefresh();
        
    var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['getRecentPluginsStatus']=1;
	tempArray['requiredData']['getRecentThemesStatus']=1;
	tempArray['requiredData']['getFTPValues']=1;
	tempArray['requiredData']['getTimeZones']=1;
	tempArray['requiredData']['getConfigFTP']=1;
	tempArray['requiredData']['autoCheckAndDeleteLog']=1;
	tempArray['requiredData']['upperConnectionLevelReset']=1;
	tempArray['requiredData']['autoCheckAndDeleteLoginLog']=1;
	tempArray['requiredData']['manageEasyCron::isActive'] = 1;
	tempArray['requiredData']['manageEasyCron::getTokenFromDB'] = 1;
	tempArray['requiredData']['getSystemCronRunningFrequency']= {};	
	tempArray['requiredData']['getSystemCronRunningFrequency']['bothCheck']= 1;	
	tempArray['requiredData']['notifyIfBothPanelCronRun']= 1;	
	tempArray['requiredData']['Manage_IWP_Cron::isActive'] = 1;
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	doCall(ajaxCallPath,tempArray,"get_settings_loader");
    
    if( (typeof isGoogle != 'undefined' && isGoogle == 1) || (typeof isGoogleWM != 'undefined' && isGoogleWM == 1) || (typeof isGooglePS != 'undefined' && isGooglePS == 1) ){   
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['googleServicesGetAPIKeys']=1;
		tempArray['requiredData']['bypassHistoryAjaxCall'] =1;
		doCall(ajaxCallPath,tempArray,"loadGoogleServicesAPIKeys");
 	}

	$(".optionSelect").live('click',function() {
		if(!$(this).hasClass("typeSelector"))
		optionSelect(this);
	});
	$(".optionSelectOne").live('click',function() {
		optionSelect(this,1);
	});

	$("body").delegate(".disabled", "click", function(){
		return false;
	});
	
	$("body").delegate(".optionSelect", "click", function(){
		if($(this).hasClass('active') && !$(this).hasClass('applyChangesCheck') )
		return false;
	});
	
	$(window).resize(function() {
		dynamicResize();
	});
	$(document).bind('click', function(event) {
		
		//Hide the menus if visible
		closeDialogs(0,event);

	});

	historyRefreshInterval=  setInterval(function () {
		if(!$(".queue_detailed").is(":visible"))
		{
			$("#historyQueueUpdateLoading").addClass('loading');
			historyRefresh();
		}
	}, 5000);

	/* -------------	   -------------	   -------------	   -------------	   -------------	   -------------	   -------------	   -------------	   -------------	   -------------	   -------------	  */
	// Updates page
	$(".all").live('click',function() {
		selectorBind(this,'all');
	});
	$(".invert").live('click',function() {
		selectorBind(this,'invert');

	});
	$(".none").live('click',function() {
		selectorBind(this,'none');
	});
	$(".selectOption").live('click',function(e) {
		if(!$(this).hasClass('updating') && !$(this).hasClass('hidden') && ($(e.target).attr('href')=='' || $(e.target).attr('href')==undefined) ) 
		generalSelect(this,'',1);
	});
	$(".row_summary").live('click',function() {
		
		if($('.navLinks.active').attr("page")=='iwpusers')
		{
			return;
		}
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['bypassHistoryAjaxCall']=1;
		if($(this).attr('view') == 'sites')
		{
			tempArray['requiredData']['getSitesRowDetailedUpdates']={};
			tempArray['requiredData']['getSitesRowDetailedUpdates']['siteID']=$(this).attr('siteid');
			tempArray['requiredData']['getSitesRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,"loadSitesRowDetailedUpdates");
			return;
		} else if($(this).attr('view') == 'plugins'){
			tempArray['requiredData']['getPluginsRowDetailedUpdates']={};
			tempArray['requiredData']['getPluginsRowDetailedUpdates']['itemID']=$(this).attr('itemID');
			tempArray['requiredData']['getPluginsRowDetailedUpdates']['key']=$(this).attr('did');
			tempArray['requiredData']['getPluginsRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,'loadPluginsRowDetailedUpdates');
		} else if($(this).attr('view') == 'themes'){
			tempArray['requiredData']['getThemesRowDetailedUpdates']={};
			tempArray['requiredData']['getThemesRowDetailedUpdates']['itemID']=$(this).attr('itemID');
			tempArray['requiredData']['getThemesRowDetailedUpdates']['key']=$(this).attr('did');
			tempArray['requiredData']['getThemesRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,'loadThemesRowDetailedUpdates');
		} else if($(this).attr('view') == 'core'){
			tempArray['requiredData']['getCoreRowDetailedUpdates']={};
			tempArray['requiredData']['getCoreRowDetailedUpdates']['itemID']=$(this).attr('itemID');
			tempArray['requiredData']['getCoreRowDetailedUpdates']['key']=$(this).attr('did');
			tempArray['requiredData']['getCoreRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,'loadCoreRowDetailedUpdates');
		} else if($(this).attr('view') == 'translations'){
			tempArray['requiredData']['getTranslationsRowDetailedUpdates']={};
			tempArray['requiredData']['getTranslationsRowDetailedUpdates']['itemID']=$(this).attr('itemID');
			tempArray['requiredData']['getTranslationsRowDetailedUpdates']['key']=$(this).attr('did');
			doCall(ajaxCallPath,tempArray,'loadTranslationsRowDetailedUpdates');
		} else if($(this).attr('view') == 'hiddenUpdates'){
			tempArray['requiredData']['getHiddenUpdatesRowDetailedUpdates']={};
			tempArray['requiredData']['getHiddenUpdatesRowDetailedUpdates']['siteID']=$(this).attr('siteid');
			tempArray['requiredData']['getHiddenUpdatesRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,'loadHiddenUpdatesRowDetailedUpdates');
		} else if($(this).attr('view') == 'WPVulns'){
			tempArray['requiredData']['WPVulnsGetWPVulnsRowDetailedUpdates']={};
			tempArray['requiredData']['WPVulnsGetWPVulnsRowDetailedUpdates']['siteID']=$(this).attr('siteid');
			tempArray['requiredData']['WPVulnsGetWPVulnsRowDetailedUpdates']['parentFlag']=$(this).attr('parent_flag');
			doCall(ajaxCallPath,tempArray,'WPVulnsLoadWPVulnsRowDetailedUpdates');
		} else{

			expandThis(this,'summary');
		}

		if($('.navLinks.active').attr("page")=='comments')
		{
			//hideApproveButton();     //TO hide the approve button
		}
		if($('.navLinks.active').attr("page")=='posts')
		{
			//hideTagsButton(); //To hide the tags if the tags on particular post is empty
		}
	});
	$(".rh").live('click',function() {
		if($('.navLinks.active').attr("page")=='iwpusers')
		{
			return;
		}
		expandThis(this,'detailed');
	});
	$(".main_checkbox").live('click',function() {
		object=$(this).closest(".ind_row_cont");
		generalSelect(object,'ind_row_cont',1);
		return false;
	});

	$('.update_view_dropdown').live('change',function(){
		var value = $(this).val();
		$('.searchSiteUpdate').removeClass('siteSearch');
		$('.searchSiteUpdate').removeClass('pluginsSearch');
		$('.searchSiteUpdate').removeClass('coreSearch');
		$('.searchSiteUpdate').removeClass('themesSearch');
		$('.searchSiteUpdate').removeClass('translationsSearch');
		$('.searchSiteUpdate').removeClass('WPVulnsSearch');
		$('.searchSiteUpdate').removeClass('hiddenSearch');
		eval("init"+value+"View()");
	
	});
		
	$(".search_site").live('keyup',function() {
		
		searchSites(this);
	});
	$(".searchSiteUpdate").live('keyup',function() {
		
		//searchSites(this,2);
	});

	$(".searchItems").live('keyup',function() {
		
		searchSites(this,4);
	});

	// Control the content via Group
	$(".update_group").live('click',function() {

		if($(this).hasClass("needConfirm"))
		{
			loadConfirmationPopup($(this));
			return false;
		}
		updateSites(this,1);
		return false;
	});
	$(".update_single").live('click',function() {
		updateSites(this);
		return false;
	});

	$('.update_in_page').live('click', function() {
		if($(this).hasClass("needConfirm"))
		{
			loadConfirmationPopup($(this));
			return false;
		}
		updateInPage(this,1);
		return false;
	});

	$('.update_all_group').live('click', function() {
		if($(this).hasClass("needConfirm"))
		{
			loadConfirmationPopup($(this));
			return false;
		}
		var selector = $(this).attr('selector');
		updateInPage(this, selector);
		return false;
	});

	$('.update_overall').live('click', function() {
		
		if($(this).hasClass("needConfirm"))
		{
			var view = $(this).attr('view');
			if (view == 'hiddenUpdates') {
				tempConfirmObject = $(this);
				tempArray['requiredData'] = {};
				tempArray['requiredData']['getOnlyHiddenUpdateCounts'] = 1;
				doCall(ajaxCallPath,tempArray,'processGetOnlyHiddenUpdateCounts','json');
			} else if (view == 'WPVulns'){
				tempArray['requiredData'] = {};
				tempConfirmObject = $(this);
				tempArray['requiredData']['getOnlyVulnsUpdateCounts'] = 1;
				doCall(ajaxCallPath,tempArray,'processGetOnlyVulnsUpdateCounts','json');
			}else{
				loadUpdateConfirmationPopup($(this), view);
			}
			return false;
		}
		tempArray['requiredData'] = {};
		tempArray['requiredData']['updateOverall'] = $(this).attr('view');
		doCall(ajaxCallPath,tempArray,'processUpdateOverall','json');
		return false;
	});

	// Siteselector and bottom footer	
	$(".nano").live('mousewheel',function(e, delta) {
		if ( !$.browser.msie && $.browser.version!='8.0') {

			var object=this;
			if($('.slider',object).css("top")=="0px" && delta>0)
			cancelEvent(e);
			var bottomval=$(this).height()-$('.slider',object).height();
			var bottomval=bottomval+"px";

			if($('.slider',object).css("top")==bottomval && delta<0)
			cancelEvent(e);
		}
	});	

	
	$(".group_cont").live('click',function() {
		if ($(this).hasClass('favGroup')){
			return false;
		}
		if($(this).attr('id')=='g0'){
			makeSelection(this,'all');
		}else{
			makeSelection(this,1);
		}
		if(currentPage=="posts" && forceBackup!=1){
			showPostsOptions();                //To show the load posts option on click
		}
		if(currentPage=="items" && forceBackup!=1){
			showItemOptions();
			triggerNanoScrollerFavoritesGroup();
		}
		if(currentPage=="malwareSecurity" && forceBackup!=1){
			showMalwareOptions();
		}
		if(currentPage=="upTimeMonitor" && forceBackup!=1){
			showUptimeOptions();
		}
		if(currentPage=="clientReporting" && forceBackup!=1){
			showReportingOptions();
		}
		if(currentPage=="wpOptimize" && forceBackup!=1){
			showOptimizeOptions();
		}
		if(currentPage=="comments" && forceBackup!=1){        //To show the load Comments option on click
			showCommentOptions();
		}else if (currentPage=="backups" || forceBackup==1){
			showBackupOptions();
		}
		if(currentPage=="userManagement") // User management
		{
			if(addNewUserOn==0)
			showUserOptions();
			else
			showAddUserOptions();
		}
		if(currentPage=="codeSnippet")
		showCodeSnippetOptions();
			
		if(currentPage=="wordFence") {
			showWordFenceOptions();
		}
                if(currentPage=="ithemesSecurity") {
			showIthemesSecurityOptions();
		}
		if(currentPage=="brokenLinks"){
			loadBrokenLinksContent();
		}

		if(currentPage=="googleWebMasters"){
			loadGoogleWebMastersContent();
		}

		if(currentPage=="fileEditor"){
			loadFileEditorContent();
		}

		if(currentPage=="gPageSpeed"){
			loadGooglePageSpeedPageContent();
		}

		if(currentPage=="yoastWpSeo"){
			loadYoastContent();
		}

	});
	$(".ind_groups","#bottom_sites_cont").live('click',function() {
		if(!$(this).hasClass('error'))
		makeSelection(this,1,1);
	});
	$(".ind_sites").live('click',function() {
		makeSelection(this,0,1);
	});
	$(".siteSelectorSelect").live('click',function() {
        selection($(this).html().toLowerCase(),'website_cont',this,".siteSelectorContainer");
	if(currentPage != 'iwpusers'){	
		if(currentPage=="items" && forceBackup!=1){
			showItemOptions();
		}else if (currentPage=="comments" || forceBackup==1){
			showCommentOptions();
		}else if(currentPage=="posts" && forceBackup!=1){
			showPostsOptions();
		}else if(currentPage=="upTimeMonitor" && forceBackup!=1){
			showUptimeOptions();
		}else if(currentPage=="clientReporting" && forceBackup!=1){
			showReportingOptions();
		}else if(currentPage=="wpOptimize" && forceBackup!=1){
			showOptimizeOptions();
		}else if(currentPage=="malwareSecurity" && forceBackup!=1){
			showMalwareOptions();
		}else if (currentPage=="backups" || forceBackup==1){
			showBackupOptions();
		}

		if(currentPage=="userManagement"){ // User management
			if(addNewUserOn==0){
				showUserOptions();
			}else{
				showAddUserOptions();
			}
		}
		if(currentPage=="codeSnippet"){
			showCodeSnippetOptions();
		}
		if(currentPage=="wordFence") {
			showWordFenceOptions();
		}
        if(currentPage=="ithemesSecurity") {
			showIthemesSecurityOptions();
		}
		if(currentPage=="brokenLinks"){
			loadBrokenLinksContent();
		}

		if(currentPage=="googleWebMasters"){
			loadGoogleWebMastersContent();
		}

		if(currentPage=="fileEditor"){
			loadFileEditorContent();
		}

		if(currentPage=="gPageSpeed"){
			loadGooglePageSpeedPageContent();
		}

		if(currentPage=="yoastWpSeo"){
			loadYoastContent();
		}
	}
	});



	$(".toggle_manage_groups").live('click',function() {
		groupEdit(this);
	});
	$(".icon_close").live('click',function() {
		resetGroup();
	});
	$("#save_group_changes").live('click',function() {
		var doSave = true;
		$("#bottomToolBarSelector .groupEditText:visible").each(function () {
			if($(this).val()=="")
			{
				$(this).addClass('error');
				doSave=false;
			}
		});
		if(doSave==true)
		{
			var tempDataArray={};
			var tempArray={};
			tempDataArray['requiredData']={};
			tempArray['new']=groupCreateArray;
			tempArray['updateSites']=groupChangeArray;
			tempArray['updateNames']=groupNameArray;
			tempArray['delete']=groupDeleteArray;
			tempDataArray['requiredData']['manageGroups']=tempArray;
			tempDataArray['requiredData']['getGroupsSites']=1;
			tempDataArray['requiredData']['getSites']=1;
			tempDataArray['requiredData']['getSitesList']=1;
			tempDataArray['requiredData']['printGroupsForReloaData']=1;

			doCall(ajaxCallPath,tempDataArray,'processSaveChange','json');
		}
		else
		{
			$(".emptyError").remove();
			$(this).closest('.bottom_bar').prepend("<div class='emptyError'>Group name(s) is empty.</div>");
		}

	});


	$(".btn_create_group").live('click',function() {
		createGroup();
	});
	$(".showFooterSelector").live('click',function() {
		
		if($("#bottom_sites_cont").is(":visible"))
		{
			$("#dynamic_resize").css("margin-left","0");
			$(this).removeClass('pressed');
			$("#bottom_sites_cont").hide();
		}
		else{
			dynamicResize(1);
		}
		return false;
	});
	$("#spreadLove").live('click',function() {
		showOrHide(this,'pressed','spreadLoveItems','1');
	});

	// Plugins/Themes install and manage page.

	// Main page
	$(".navLinks").live('click',function() {
		$(".navLinks").removeClass("active");
		$(this).addClass("active");
		$("#header_nav .first-level").removeClass('active_color');
		getIWPTitle($(this));
		processPage($(this).attr('page')); 
	});

	$(".loginIWPAtInitial").live('click',function() { 
		if($(this).hasClass('no'))
		{
			$("#modalDiv").dialog('close');
			return false;
		}
		$(this).prepend('<div class="btn_loadingDiv left"></div>').addClass('disabled');
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['IWPAuthUser']={};
		if($(this).hasClass('overwrite') && $(this).hasClass('yes'))
		{
			tempArray['requiredData']['IWPAuthUser']['process']="changeRegister";	
			tempArray['requiredData']['IWPAuthUser']['username']=usernameTemp;
			tempArray['requiredData']['IWPAuthUser']['password']=passwordTemp;
		}
		else{
			$(".loginError").html('');
			usernameTemp=$("#username").val();
			passwordTemp=$("#password").val();
			tempArray['requiredData']['IWPAuthUser']['username']=usernameTemp;
			tempArray['requiredData']['IWPAuthUser']['password']=passwordTemp;
			tempArray['requiredData']['IWPAuthUser']['login']="yes";
		}
		if($(this).attr('actionvar')!=undefined)
		tempArray['requiredData']['IWPAuthUser']['action']=$(this).attr('actionvar');

		tempArray['requiredData']['IWPAuthUser']['initialCall']="1";

		doCall(ajaxCallPath,tempArray,"processIWPLoginAtInitial");
		
	});
	
	
	// History page
	$(".historyToolbar").live('click',function() {
		showOrHide(this,'','historyQueue','');
		return false;
	});
	$(".historyItem").live('click',function() {
		if ($(this).hasClass('active')) {
			$("#"+$(this).attr('actionid').replace('.','')).hide();
			$(this).removeClass('active');
			activeProcessQueueActionID = false;
			return false;
		}
		if (activeProcessQueueActionID == $(this).attr('actionid')) {
			return false;
		}
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['getHistoryPanelDetailedHTML']={}
		activeProcessQueueActionID = $(this).attr('actionid');
		tempArray['requiredData']['getHistoryPanelDetailedHTML']['actionid']=$(this).attr('actionid');
		doCall(ajaxCallPath,tempArray,"loadHistoryPanelDetailedHTML","json","none");
		return false;
	});
	
	$(".fetchInstall").live('click',function() {
		if(!$(this).hasClass('disabled'))
		{
			siteArray=getSelectedSites();
			$(this).addClass('disabled');
			$(this).attr('tempname',$(this).text());
			$(this).append('<div class="btn_loadingDiv"></div>');
			var tempArray={};
			var requireDataArray={};
			tempArray['args']={};
			tempArray['action']="get"+activeItem.toTitleCase();
			tempArray['args']['siteIDs']=siteArray;
			requireDataArray['getSearchedPluginsThemes']=1;
			tempArray['requiredData']= requireDataArray;
			doCall(ajaxCallPath,tempArray,'formArrayLoadPlugins','json',"none");
		}
	});	

	$(".actionButton").live('click',function() {
		applyChangesCheck('applyChangesCheck',2); // For Apply changes (shouldn't change the button content like update page)
	});

	$(".typeSelector").live('click',function() {
		if(!$(this).hasClass('active'))
		{
			optionSelect(this);
			activeItem=$(this).attr('utype');
			$('.itemName').text(activeItem.toTitleCase());
			$('.itemNameLower').text(activeItem.toLowerCase());
			$('.itemUpper').text(activeItem.toUpperCase());
			if($(".manage").hasClass('active'))
			{
				$(".manage").removeClass('active')
				$(".manage").click();
			}
			if($(".install").hasClass('active'))
			{
				$(".install").removeClass('active')
				$(".install").click();
			}
		}
		return false;
	});

	$(".js_changes").live('click',function() {

		applyChanges(this);
		$(this).addClass('disabled');
		removeActiveElements();
		return false;
	});

	$(".favItems").live('click',function() {
		if (!$(this).attr('gid')){
		makeSelection(this);
		checkFavItems();
		}
	});

	$(".favSearch").live('keyup',function() {

		searchSites(this,'3');
	});

	$(".manage_websites_view").live('click',function() {

		pluginsListPanel('sites',activeItem);
		resetFilterText('.actionContent');

	});
	$(".manage_themes_view").live('click',function() {

		pluginsListPanel('themes',activeItem);
		resetFilterText('.actionContent');
	});
	$(".installFavourites").live('click',function() {
		//installFavourites();
		$(this).addClass('disabled');
		getLinksAndInstallFavourites();
		return false;
	});

	$(".favGroup").live('click',function() {
		makeSelectionPluginsThemesFavorites(this);
	});

	$(".manage_plugins_view").live('click',function() {

		pluginsListPanel('plugins',activeItem);
		resetFilterText('.actionContent');
	});
	$(".searchItem").live('click',function() {
		$(this).addClass('disabled');
		$(this).prepend('<div class="btn_loadingDiv"></div>');
		var tempArray={};
		tempArray['requiredData']={};
		var valArray={};
		valArray['type']=activeItem;
		valArray['searchVar']=1;
		valArray['searchItem']=$(".searchText").val();
		tempArray['requiredData']['getWPRepositoryHTML']=valArray;
		doCall(ajaxCallPath,tempArray,"ajaxRepositoryCall","json","none");

	});


	$(".searchVar").live('click',function() {
		if($(this).attr('dval')=='search')
		{
			$(".wp_repository_cont").html('');
			$(".searchCont").show();
		}
		else
		{
			tempArray={};
			tempArray['requiredData']={};
			valArray={};
			valArray['type']=activeItem;
			valArray['searchVar']=0;
			valArray['searchItem']=$(this).attr('dval');
			tempArray['requiredData']['getWPRepositoryHTML']=valArray;

			doCall(ajaxCallPath,tempArray,"ajaxRepositoryCall");

		}

	});

	$(".installOptions").live('click',function() {
		currentUploader = '';
		var loadFunction=$(this).attr('function');
		if(loadFunction=='loadFavourites')
		{
			$(".favOption").show();
			$("#addToFavouritesCustom").show();
			$("#createFavoriteGroup").show();
		}
		else
		{
			$(".favOption").hide();
			$("#addToFavouritesCustom").hide();
			$("#createFavoriteGroup").hide();
		}
		var returnVar=eval(loadFunction+"()");
		$(".installSubPanel").html(returnVar);
		if(loadFunction=="loadComputer")
		createUploader();
	});
	$(".website_cont").live('click',function(e) {
		makeSelection(this);
		if(currentPage=="comments" && forceBackup!=1){
			showCommentOptions();    //To show load Comments option on click
		}else if(currentPage=="clientReporting" && forceBackup!=1){
			showReportingOptions();
		}else if(currentPage=="upTimeMonitor" && forceBackup!=1){
			showUptimeOptions();
		}else if(currentPage=="wpOptimize" && forceBackup!=1){
			showOptimizeOptions();
		}else if(currentPage=="items" && forceBackup!=1){
			showItemOptions();
			triggerNanoScrollerFavoritesGroup();
		}else if(currentPage=="posts" && forceBackup!=1){
			showPostsOptions();    //TO show load Posts option on click
		}else if(currentPage=="backups" || forceBackup==1){
			showBackupOptions();
		}
		if(currentPage=="userManagement"){ // User management
			if(addNewUserOn==0){
				showUserOptions();
			}
			else{
				showAddUserOptions();
			}
		}
		if(currentPage=="codeSnippet"){
			showCodeSnippetOptions();
		}
		if(currentPage=="wordFence") {
			showWordFenceOptions();
		}
        if(currentPage=="ithemesSecurity") {
			showIthemesSecurityOptions();
		}
		
		if(currentPage=="brokenLinks"){
			loadBrokenLinksContent(e);
		}

		if(currentPage=="googleWebMasters"){
			loadGoogleWebMastersContent(e);
		}

		if(currentPage=="fileEditor"){
			loadFileEditorContent();
		}

		if(currentPage=="gPageSpeed"){
			loadGooglePageSpeedPageContent();
		}

		if(currentPage=="yoastWpSeo"){
			loadYoastContent(e);
		}

	});

	$(".installNotInstalled").live('click',function() {
		notInstalledSiteID = $(this).attr('sid');
		$(this).addClass('disabled');
		//$(this).prepend('<div class="btn_loadingDiv"></div>');
		var tempArray={};
		tempArray['requiredData']={};
		var valArray={};
		valArray['type']=activeItem;
		valArray['siteID']=notInstalledSiteID;
		valArray['plugin_slug']=$(this).attr('plugin_theme_slug');
		tempArray['requiredData']['installNotInstalledPlugin']=valArray;
		doCall(ajaxCallPath,tempArray,"installNotInstallCallback","json","none");

	});

	$(".installItem").live('click',function() {
		$(this).addClass('disabled');
		$(this).text("Queued..");
		wpRepositoryFlag = 1;
		if($(this).hasClass('multiple'))
		{
			installItems(this,'',1);
		}
		else
		installItems(this);
		return false;
	});
	
	$(".manage").live('click',function() {
		$("#processType").text('MANAGE');
		

		$(".optionsContent").html('<div style="padding:10px 0;"> <div class="btn_action float-right fetchInstallCont"><a class="rep_sprite fetchInstall">Load <span class="itemName">'+activeItem.toTitleCase()+' </span></a></div> <div class="float-right" style="margin-right:10px"> <input name="" type="text" onenterbtn=".fetchInstall" class="fetchInstallTxt txt onEnter" style="width: 300px; height: 17px; margin: 5px;" /> </div> <div class="float-right" style="text-align:right; color:#737987; padding-top: 10px;"><span class="droid700" style="font-size:11px;">SEARCH <span class="itemName itemUpper">'+activeItem.toUpperCase()+'</span><br /> Leave blank to load all <span class="itemName">'+activeItem+'</span></div> <div class="clear-both"></div><div class="actionContent siteSearch" style="display:none;margin-top:10px "></div> </div>');
		$(".optionsContent").addClass('result_block_noborder').hide();
		$(".advancedInstallOptions").hide();
		$(".siteSelectorContainer").html(siteSelectorVar);

		siteSelectorNanoReset();
	});
	$(".install").live('click',function() {
		$(".siteSelectorContainer").html(siteSelectorRestrictVar);
		siteSelectorNanoReset();
		$("#processType").text('INSTALL');
		$(".optionsContent").removeClass('result_block_noborder');


		loadInstallPanel("optionsContent");
		$(".optionsContent").hide();
		$(".advancedInstallOptions").hide();
	});

	$(".generalSelect").live('click',function() {
		makeSelection(this);
		if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
				WPVulnsSettingNotesUpdate();
		} 
	});

	// Bottom toolbar
	$(".groupEditText").live('focus',function(e) {
		if(!$(this).hasClass('focus') && !$(this).hasClass('error'))
		$(this).blur();
		
	});
	$(".groupEditText.focus").live('blur',function(e) {
		
		groupNameArray[$(this).closest('.ind_groups').attr('gid')]=$(this).val();
		$(this).removeClass('focus');
	});

	$(".groupEditText.error").live('blur',function(e) {
		groupNameArray[$(this).closest('.ind_groups').attr('gid')]=$(this).val();
		$(this).removeClass('error');
	});

	$(".editGroup").live('click',function() {
		$("#save_group_changes").show();
		closestVar=$(this).closest('.ind_groups');
		$(".groupEditText",closestVar).addClass('focus').focus();
		removeDeleteConf();
		return false;
		
	});

	$(".deleteConf").live('click',function() {
		var closestVar=$(this).closest('.ind_groups');
		$(".del_conf").hide();
		$(".ind_groups","#bottom_sites_cont").removeClass('error');
		$(".del_conf",closestVar).show();
		$(closestVar).addClass('error');
		return false;
	});

	$(".deleteGroup").live('click',function() {
		var closestVar=$(this).closest('.ind_groups');
		if($(this).hasClass('yes')){
			groupDeleteArray[$(closestVar).attr('gid')]=$(closestVar).attr('gid');
			$(".js_sites","#bottom_sites_cont").removeClass('active');
			$(".ind_groups","#bottom_sites_cont").removeClass('active');
			
			groupChangeArray[$(closestVar).attr('gid')]= groupNameArray[$(closestVar).attr('gid')] = groupCreateArray[$(closestVar).attr('gid')] = '' ;
			$(closestVar).fadeOut();
			$("#save_group_changes").show();
			if($(".ind_groups","#bottomToolBarSelector").length==0)
			$("#bottom_toolbar #bottom_sites_cont .list_cont .ind_sites a").css({'background-position': '-25px 0', 'padding': '11px 0 9px 5px','width': '255px'});
		}else{
			$(".del_conf",closestVar).hide();
			$(closestVar).removeClass('error');
		}
		return false;
	});
	// Add site

	$(".addSiteButton").live('click',function() {
		var editSite='';
		var contentType='';
		if($(this).hasClass('editSite'))
		editSite=1;
		$("#addSiteErrorMsg").remove();
		var  tempArray={};
		tempArray['args']={};
		tempArray['args']['params']={};
		tempArray['requiredData']={};
		var addsiteWebsite=$("#adminURL","#modalDiv").val();
		var addsiteSiteURL=$("#websiteURL","#modalDiv").val();
		var websiteIP = $("#websiteIP", "#modalDiv").val();
		
		var addsiteUsername=$("#username",".add_site.form_cont").val();
		var addsiteGroupText =$("#groupText",".add_site.form_cont").val();
		var addsiteActivationKey =$("#activationKey",".add_site.form_cont").val();
		var addSiteConnectURL =$(".connectURLClass.active",".add_site.form_cont").attr('def');
		if($(".cTypeRadio.active").hasClass('customTxt')){
			if($(".customTxtVal").val()=='custom type'){
				contentType='';
			}else{
				contentType=$(".customTxtVal").val();
			}
		}else{
			contentType=$(".cTypeRadio.active").text();
		}

		if(addsiteGroupText=='eg. group1, group2'){
			addsiteGroupText='';
		}
		
		var groupIDs=groupSelected();
		var sslVersion = $('#sslv option:selected').val();
		var httpVersion = $('#httpv option:selected').val();
		var selectedManagers={};
		if(typeof multiUserAddonFlag !='undefined' && multiUserAddonFlag){
			selectedManagers =managerSelected();
		}

		if(editSite!=1){
			tempArray['action']='addSite';
			tempArray['args']['params']['URL_b64encoded']=$.base64('btoa',addsiteWebsite, true);
			tempArray['args']['params']['websiteURL_b64encoded']=$.base64('btoa',addsiteSiteURL, true);
			tempArray['args']['params']['activationKey']=addsiteActivationKey;
			tempArray['args']['params']['username']=addsiteUsername;
			tempArray['args']['params']['siteName']=$("#addSiteSiteName").val();
			tempArray['args']['params']['groupsPlainText']=addsiteGroupText;
			tempArray['args']['params']['groupIDs']=groupIDs;
			tempArray['args']['params']['managerID']=selectedManagers;
			tempArray['args']['params']['callOpt']={};
			tempArray['args']['params']['callOpt']['SSLVersion']=sslVersion;
			tempArray['args']['params']['callOpt']['HTTPVersion']=httpVersion;
			tempArray['args']['params']['callOpt']['websiteIP']=websiteIP;

			if($(this).hasClass('advanced')){
				tempArray['args']['params']['callOpt']['contentType']=contentType;
				tempArray['args']['params']['connectURL']=addSiteConnectURL;
				tempArray['args']['params']['advancedCUCT']=1;
			}else{
				tempArray['args']['params']['callOpt']['contentType']='';
				tempArray['args']['params']['connectURL']='default';
				tempArray['args']['params']['advancedCUCT']=0;
			}

			if($("#addSiteAuthUsername").val()!="username")
			{
				tempArray['args']['params']['httpAuth']={};
				tempArray['args']['params']['httpAuth']['username']=$("#addSiteAuthUsername").val();
				tempArray['args']['params']['httpAuth']['password']=$("#addSiteAuthUserPassword").val();
				
			}
			tempArray['args']['params']['addSiteFtpDetails']= getSaveFtpReqVar(false);;
			tempArray['args']['params']['schedules']={};
			if(typeof scheduleAddonFlag != 'undefined' && scheduleAddonFlag == 1){
				tempArray['args']['params']['schedules']['scheduleBackupIDs']=$("#sbs2").val();
			}
			if(typeof isClientReport != 'undefined' && isClientReport == 1){
				tempArray['args']['params']['schedules']['CRScheduleID']=$("#CRS2").val();
			}
			if(typeof isWPOptimize != 'undefined' && isWPOptimize == 1){
				tempArray['args']['params']['schedules']['WPScheduleID']=$("#wps2").val();
			}
			tempArray['requiredData']['getMultisitesByParentSiteURL'] = {};
			tempArray['requiredData']['getMultisitesByParentSiteURL']['URL_b64encoded']=$.base64('btoa',addsiteWebsite, true);
		}
				
		
		if(editSite==1){
			tempArray['requiredData']['updateSite'] = {};
			tempArray['requiredData']['updateSite']['URL_b64encoded']=$.base64('btoa', $("#websiteURL",".add_site.form_cont").val(), true);
			tempArray['requiredData']['updateSite']['siteID']=$(this).attr('sid');
			tempArray['requiredData']['updateSite']['adminURL_b64encoded']=$.base64('btoa',$("#adminURL",".add_site.form_cont").val(),true);
			tempArray['requiredData']['updateSite']['groupsPlainText']=addsiteGroupText;
			tempArray['requiredData']['updateSite']['groupIDs']=groupIDs;
			tempArray['requiredData']['updateSite']['managerID']=selectedManagers;
			tempArray['requiredData']['updateSite']['adminUsername']=addsiteUsername;
			tempArray['requiredData']['updateSite']['connectURL']=addSiteConnectURL;
			tempArray['requiredData']['updateSite']['callOpt']={};
			tempArray['requiredData']['updateSite']['callOpt']['SSLVersion']=sslVersion;
			tempArray['requiredData']['updateSite']['callOpt']['HTTPVersion']=httpVersion;
			tempArray['requiredData']['updateSite']['callOpt']['contentType']=contentType;
			tempArray['requiredData']['updateSite']['callOpt']['websiteIP']=websiteIP;
			tempArray['requiredData']['updateSite']['siteName']=$("#addSiteSiteName").val();
			tempArray['requiredData']['updateSite']['siteName']=$("#addSiteSiteName").val();
			if($("#addSiteAuthUsername").val()!="username")
			{
				tempArray['requiredData']['updateSite']['httpAuth']={};
				tempArray['requiredData']['updateSite']['httpAuth']['username']=$("#addSiteAuthUsername").val()
				tempArray['requiredData']['updateSite']['httpAuth']['password']=$("#addSiteAuthUserPassword").val()
			}
			// if(typeof isStaging!='undefined' && isStaging == 1){
				var saveFtpReqVar = getSaveFtpReqVar($(this).attr('sid'));
				tempArray['requiredData']['saveSiteFtpDetails'] = {};
				tempArray['requiredData']['saveSiteFtpDetails'] = saveFtpReqVar;
				tempArray['requiredData']['saveSiteFtpDetails']['siteID']=$(this).attr('sid');
			// }
			
		}
		tempArray['requiredData']['getSitesUpdates']=1;
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
		tempArray['requiredData']['getGroupsSites']=1;
		tempArray['requiredData']['getRecentPluginsStatus']=1;
		tempArray['requiredData']['getRecentThemesStatus']=1;
		tempArray['requiredData']['getSites']=1;
		tempArray['requiredData']['getSitesList']=1;
		
		tempArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
		tempArray['requiredData']['getAddonPlanSiteLimit']=1;
		tempArray['requiredData']['getAddonSuitePlanActivity']=1;
		
		$(this).addClass('disabled');
		$(this).prepend('<div class="btn_loadingDiv left"></div>');
		if(editSite==1){
 
			if(typeof isGoogle != 'undefined' && isGoogle == 1){
				tempArray['requiredData']['googleAnalyticsSetProfilesAndSites'] = {};
				tempArray['requiredData']['googleAnalyticsSetProfilesAndSites']['googleProfileID']=$('#gg option:selected').attr('profileID');
				tempArray['requiredData']['googleAnalyticsSetProfilesAndSites']['siteID']=$(this).attr('sid');
				tempArray['requiredData']['googleAnalyticsSetProfilesAndSites']['gaID']=$('#gg option:selected').attr('gaID');
			}
			if(typeof isGoogleWM != 'undefined' && isGoogleWM == 1){
				tempArray['requiredData']['googleWebMastersSetProfilesAndSites'] = {};
				tempArray['requiredData']['googleWebMastersSetProfilesAndSites']['googleProfileID']=$('#ggwm option:selected').attr('profileID');
				tempArray['requiredData']['googleWebMastersSetProfilesAndSites']['siteID']=$(this).attr('sid');
			}

			if(typeof scheduleAddonFlag != 'undefined' && scheduleAddonFlag == 1){
				tempArray['requiredData']['scheduleBackupScheduleSites'] = {};
				tempArray['requiredData']['scheduleBackupScheduleSites']['scheduleIDs']=$("#sbs2").val();
				tempArray['requiredData']['scheduleBackupScheduleSites']['siteID']=$(this).attr('sid');
			}
			if(typeof isClientReport != 'undefined' && isClientReport == 1){
				tempArray['requiredData']['clientReportingScheduleSites'] = {};
				tempArray['requiredData']['clientReportingScheduleSites']['scheduleIDs']=$("#CRS2").val();
				tempArray['requiredData']['clientReportingScheduleSites']['siteID']=$(this).attr('sid');
			}
			if(typeof isWPOptimize != 'undefined' && isWPOptimize == 1){
				tempArray['requiredData']['wpOptimizeScheduleSites'] = {};
				tempArray['requiredData']['wpOptimizeScheduleSites']['scheduleIDs']=$("#wps2").val();
				tempArray['requiredData']['wpOptimizeScheduleSites']['siteID']=$(this).attr('sid');
			}
			doCall(ajaxCallPath,tempArray,"processEditSite","json","none");		
		} else{
			doCall(ajaxCallPath,tempArray,"processAddSite","json","none");
		}
	});

	$(".js_addSite").live('click',function() {
		makeSelection(this);
	});

	$('.select2_bottom .select_group_toolbar').select2({
		width:'177px'		
	}); 

	$('.select2_bottom .select_group_toolbar').live('change',function(){
		var value = $(this).val();
		currentGroupID = value;
		filterByGroup('',value);
	});

	$('.update_by_group .select_group_toolbar').live('change',function(){
		var value = $(this).val();
		$('.group_error_message').remove();
		currentGroupID = value;
		if (currentUpdatePage == 'siteViewUpdateContent') {
			initWebsitesView(currentGroupID);
		}else if(currentUpdatePage == 'hiddenViewUpdateContent'){
			initHiddenView(currentGroupID);
		}else if (currentUpdatePage == 'securityViewUpdateContent') {
			initSecurityUpdatesView(currentGroupID);
		}
		//filterByGroupUpdate(this,value);
	});

	$(".installFromComputer").live('click',function() {
		$('#favAlreadyExist').hide();
		var arrayCounter=0;
		var URL={};
		var testURL='';
		var funcURL=systemURL;
		var isaddToFavorite = '';
		var favoritesArray = {};
		if(settingsData.data.getSettingsAll.settings.general.httpAuth!=undefined)
		{
			if(settingsData.data.getSettingsAll.settings.general.httpAuth.username!='')
			funcURL=funcURL.replace("://","://"+settingsData.data.getSettingsAll.settings.general.httpAuth.username+":"+settingsData.data.getSettingsAll.settings.general.httpAuth.password+"@");
		}
		if ($("#addToFavoriteCheckbox").hasClass('active')){
			favoritesArray['type'] 	= activeItem.toTitleCase();
			var fileName 		= $('.installFileNames').html().replace(/ /g,"%20");
			favoritesArray['currentURL'] = funcURL;
			favoritesArray['fileName'] = fileName;
			favoritesArray['folderPath']	= "uploads/";
			favoritesArray['name'] 	= $("#uploadZipName").val();
			var isAlreadyExist = isFavoritesAlreadyExist(activeItem, favoritesArray['name']);
			if (isAlreadyExist) {
				return true;
			}
			if (!favoritesArray['name']) {
				$("#uploadZipName").addClass("error_text_box");
			} else if(favoritesArray['folderPath'] && favoritesArray['type']){
				$('.installFromComputer').addClass('disabled');
				tempArray['requiredData']['addFavourites'] = {};
				tempArray['requiredData']['addFavourites'] = favoritesArray;
				tempArray['requiredData']['getFavouritesGroups'] = 1;
				tempArray['requiredData']['getFavourites'] = 1;
				doCall(ajaxCallPath, tempArray, 'reloadFavourites', 'json');
				isaddToFavorite = 'verified';
			} 
		} else {
			isaddToFavorite = 'verified';
		}
		if (isaddToFavorite == 'verified') {
			$('.installFromComputer').addClass('disabled');
		$(".installFileNames").each(function () {
			var fileName = $(this).html().replace(/ /g,"%20");
				testURL = funcURL+"uploads/"+fileName;
				URL[arrayCounter] = testURL; arrayCounter++;
		});
			$("#addToFavoriteCheckbox").hide();
			$(".zipNameAfterAddFavorite").hide();
		installItems('',URL,2);
		$(".qq-upload-list").html('');
		return false;
		}
	});
	$("#uploadZipName").live('click',function() {
		$("#uploadZipName").removeClass("error_text_box");
	});

	$("#installFromURLTxt").live('keyup',function () {
		if(validateZipURL($(this).val()))
		$("#installFromURL").removeClass('disabled');
		else
		$("#installFromURL").addClass('disabled');
	});
	$("#installFromURL").live('click',function() {
		$('#favAlreadyExist').hide();
		URL=$("#installFromURLTxt").val();
		var isaddToFavorite = '';
		valArray = {};
		if ($("#addToFavoriteCheckbox").hasClass('active')){
			valArray['type'] = activeItem.toTitleCase();
			valArray['URL']  = URL;
			valArray['name'] = $("#uploadZipName").val();
			var isAlreadyExist = isFavoritesAlreadyExist(activeItem, valArray['name']);
			if (isAlreadyExist) {
				return true;
			}
			if (!valArray['name']) {
				$("#uploadZipName").addClass("error_text_box");
			} else if(valArray['URL'] && valArray['type']){
				$('.installFromURL').addClass('disabled');
				tempArray['requiredData']['addFavourites'] = {};
				tempArray['requiredData']['addFavourites'] = valArray;
				tempArray['requiredData']['getFavouritesGroups'] = 1;
				tempArray['requiredData']['getFavourites'] = 1;
				doCall(ajaxCallPath, tempArray, 'reloadFavourites', 'json');
				isaddToFavorite = 'verified';
			} 
		} else {
			isaddToFavorite = 'verified';
		}
		if (isaddToFavorite == 'verified')
		{
		installItems('',URL,1);
		}	
		return false;
	});

	$('.favorites_group_btn').live('mouseenter',function(){
		if ($(this).hasClass('disabled')){
			$(".delete_user_post_ressign_tip").show();
		}
	});
	
	
	$('.favorites_group_btn').live('mouseleave',function(){
		$(".delete_user_post_ressign_tip").hide();
	});
	
	// Favorites 
	$(".addToFavorites").live('click',function() {
		$(this).addClass('disabled');
		$(this).text('Favourite');
		var tempArray = {};
		tempArray['requiredData'] = {};
		var valArray = {};
		valArray['name'] = $(this).attr('iname');
		valArray['slug'] = $(this).attr('islug');
		valArray['URL']  = $(this).attr('dlink');
		valArray['type'] = $(this).attr('utype');
		tempArray['requiredData']['addFavourites'] = {};
		tempArray['requiredData']['addFavourites'] = valArray;
		tempArray['requiredData']['getFavouritesGroups'] = 1;
		tempArray['requiredData']['getFavourites'] = 1;
		doCall(ajaxCallPath, tempArray, 'reloadFavourites', 'json');
		
	});

	$(".hideItem").live('click',function() {
		var closestVar=$(this).closest('.item_ind');
		var tempArray={};
		tempArray['requiredData']={};
		var valArray={};
		
		if(currentUpdatePage == 'siteViewUpdateContent' || currentUpdatePage == 'hiddenViewUpdateContent' || currentUpdatePage == 'securityViewUpdateContent'){
			typeText=$(".row_"+$(closestVar).attr('selector')+" .label").text();
		}
		prevCount=parseInt($(".row_"+$(closestVar).attr('selector')+" .count span").text());
		if($(this).text()=="Hide"){
			
			$(closestVar).removeClass('active');
			$(closestVar).addClass('hidden').show();
			$(".row_checkbox",closestVar).hide();
			
			
			if(viewHiddenFlag==0 && !$("#viewHidden").hasClass('active')){
				$(closestVar).fadeOut(300);
			}
			
			$(".row_"+$(closestVar).attr('selector')+" .count span").text(prevCount-1);
			$("#totalUpdateCount").text(parseInt($("#totalUpdateCount").text())-1);
			if(currentUpdatePage=='siteViewUpdateContent' || currentUpdatePage=='hiddenViewUpdateContent' || currentUpdatePage=='securityViewUpdateContent'){
				$(".updateCount_"+typeText.toLowerCase()+"_"+$(this).attr('parent')+" span").text(prevCount-1);
			}
			// if (currentUpdatePage=='securityViewUpdateContent' ) {
			// 	securityUpdateCount -=1;
			// }
			$(this).text('Show');
			tempAction="addHide";
			
		}
		else{
			$("#totalUpdateCount").text(parseInt($("#totalUpdateCount").text())+1);
			$(closestVar).removeClass('hiddenView');
			$(closestVar).removeClass('active');
			$(closestVar).addClass('hidden').show();
			$(".row_checkbox",closestVar).hide();
			if(viewHiddenFlag==0 && !$("#viewHidden").hasClass('active')){
				$(closestVar).fadeOut(300);
			}
			$(this).text('Hide');
			tempAction="removeHide";
			$(".row_"+$(closestVar).attr('selector')+" .count span").text(prevCount-1);
			if(currentUpdatePage=='hiddenViewUpdateContent')
			$(".updateCount_"+typeText.toLowerCase()+"_"+$(this).attr('parent')+" span").text(prevCount-1);
		}		
		
		valArray['name']=$(closestVar).attr('iname');
		if(valArray['name']==undefined){
			valArray['name']='';
		}
		valArray['path']=$(closestVar).attr('did');		
		valArray['type']=$(closestVar).attr('utype');
		if(valArray['type']=="WP"){
			valArray['type']="core";
		}
		tempArray['requiredData'][tempAction]={};
		tempArray['requiredData'][tempAction][$(closestVar).attr('sid')]=valArray;
		tempArray['requiredData']['getUpdateCounts']=1;
		doCall(ajaxCallPath,tempArray,'processHideUpdate','json');
		checkGeneralSelect($(this).attr('selector'),'',1);
		checkGeneralSelect($(this).attr('parent'),'',1);
		checkGeneralSelect('ind_row_cont','');
		return false;
		
	});

	$(".qty_btn").live('click',function() {
		var parent_qty_sel = $(this).closest(".qty_selector_cont");
		var thisInputID = $("input",parent_qty_sel).attr("id");
		addNumber(this, thisInputID);
	});
	//  Backup
	$("#backupNow").live('click',function() {
		//RP start
		var isScheduledBackup = $(this).hasClass('scheduleBackup');
		if(repositoryAddonFlag==1)
		{
			if($(this).hasClass('selectRepo'))
			{
				$(".dialog_cont .th_sub.rep_sprite .current").removeClass('current');
				$(".dialog_cont #enterDetailsTab").addClass('completed').removeClass('clickNone');
				$(".dialog_cont #selectRepository").addClass('current');
				$(".dialog_cont .backupTab").hide();
				$(this).closest('div').addClass('btn_action').removeClass('btn_next_step rep_sprite');
				$(this).show();
				if($(".dialog_cont #completeRepository").length==0)
				loadBackupRepository();
				else
				{
                    if($( "#backup_new" ).hasClass( "active" )) {
                        $("#use_sftp").removeClass("active");
                        $("#use_sftp").hide();
                    } else {
                        $("#use_sftp").show();
                    }
                    if($( ".phoenix_backup" ).hasClass( "active" )) {
                        
                        if($( "#use_sftp" ).hasClass( "active" )) {
                        	$('.phoenix_key').show();
                        }else{
                        	$('.phoenix_key').hide();
                        }
                    } else {
                        $('.phoenix_key').hide();
                    }
                    if($( ".phoenix_backup" ).hasClass( "active" )) {
                    	$('#phoenix_aws_bucket').hide();
                    }else{
                    	$('#phoenix_aws_bucket').show();
                    }
					$(".dialog_cont #completeRepository").show();
				}
				centerDialog();
				$(this).addClass('repoSubmit rep_sprite').removeClass('selectRepo');
				if(scheduleAddonFlag==1 && $(this).hasClass('scheduleBackup'))
				{
					if($(this).attr("schedulekey")!=undefined && $(this).attr("schedulekey")!="")
					$(this).html('Reschedule Backup')
					else
					$(this).html('Schedule Backup')
				}
				else
				$(this).html('Backup Now');
				return false;
				
			}
		}
		//RP end
		var closestVar="#backupOptions";
		var tempArray={};
		tempArray["args"]={};
		tempArray["args"]["params"]={};
		var valArray={};
		var backupParentClass='.create_backup';
		var backupIncludeFolders=$("#includeFolders",backupParentClass).val();
		var backupExcludeTables=$("#excludeTables",backupParentClass).val();
		if($("#compression",backupParentClass).hasClass('active')){
			var backupCompression=1;
		}else{
			var backupCompression='';
		}
		if($("#databaseOptimize",backupParentClass).hasClass('active')){
			var backupDatabaseOptimize=1;
		}else{
			var backupDatabaseOptimize='';
		}
		var backupExcludeFiles=$("#excludeFiles",backupParentClass).val();

		if(backupExcludeFiles=='wp-admin,old-backup.zip'){
			var backupExcludeFiles='';
		}
		var backupexcludeExtensions = $("#excludeExtensions").val();
		if(backupExcludeFiles=='eg. .zip,.mp4'){
			backupExcludeFiles='';
		}
		
		if($("#full",backupParentClass).hasClass('active')){
			var backupWhat="full";
		}else if($("#files",backupParentClass).hasClass('active')){
			var backupWhat="files";
		}else{
			var backupWhat="db";
		}
		var backupTaskName=$("#backupName",backupParentClass).val();
		if (iwpIsEmpty(backupTaskName) && isScheduledBackup) {
			backupTaskName = "Schedule Backup";
		}else if(iwpIsEmpty(backupTaskName)){
			backupTaskName = "Manual Backup";
		}
		var backupTotal = $("#backupOptions #backupTotal").val();
		
		valArray['limit']=backupTotal;
		valArray['mechanism'] = $(".backupMechanism.active").attr("mechanism");		
		tempArray['action']='backup';
		if(scheduleAddonFlag==1){
			if(isScheduledBackup){
				var sType=$("#backupOptions .whenType.active").text().toLowerCase();
				
				valArray['type']=sType;
				var sTime = valArray['scheduleHR'] = $("#backupOptions #timeSelectBtn a").attr('timeval');
				if(sType=='daily'){
					valArray['scheduleHR']=sTime;
				}
				else if(sType=='weekly'){
					valArray['scheduleHR']=sTime+"|"+$("#backupOptions .day_select_cont .optionSelect.active").attr('dayval');
				}
				else if(sType=='monthly'){
					valArray['scheduleHR']=sTime+"|"+$("#backupOptions .date_select_cont .selectDate.monthly.active").text();
				} 
				else if (sType == 'fortnightly') {
					valArray['scheduleHR']=sTime+"|"+$("#backupOptions .date_select_cont .selectDate.fortnightly.active").first().text()+"|"+$("#backupOptions .date_select_cont .selectDate.active").last().text();
				}
				if($(this).attr('schedulekey')!=undefined && $(this).attr('schedulekey')!=''){
					tempArray["args"]["params"]['schedulekey']=$(this).attr('schedulekey');
				}
				tempArray['action']='scheduleBackup';
			}
		}
		//SB end
		//RP start
		if(repositoryAddonFlag==1){
			if($(this).hasClass('repoSubmit')){
				var backupRepoType=[$(".dialog_cont .repBtn.active").attr('repType')];
				valArray['backupRepoType'] = backupRepoType;
				if(backupRepoType!='iwp_server'){
					var checkForm=validateForm($(".dialog_cont .repBtn.active").attr('rep')+"Repo");
					if(checkForm!=false){
						tempArray["args"]["params"]['accountInfo']={};
						tempArray["args"]["params"]['accountInfo'][backupRepoType]=checkForm;
						valArray['delHostFile']=1;

					}else{
						return false;
					}
				}else{
					valArray['delHostFile']='';
				}
			}
		}
		//RP end
		valArray['include']=backupIncludeFolders;
		valArray['excludeTables']=backupExcludeTables;
		valArray['disableCompression']=backupCompression;
		valArray['optimizeDB']=backupDatabaseOptimize;
		valArray['exclude']=backupExcludeFiles;
		valArray['what']=backupWhat;
		valArray['backupName']=backupTaskName;
		valArray['IWP_encryptionphrase']=$("#databaseEncryptionPhrase").val();
		if($("#excludeSize").hasClass("active")){
			valArray['excludeFileSize'] = $("#excludeSizesSelect").val();
		}else{
			valArray['excludeFileSize'] = 0;
		}
		valArray['excludeExtensions'] = $("#excludeExtensions").val();
		if($("#fail_safe_check_files").hasClass('active')){
			valArray['failSafeFiles']=1;
		}
		if($("#fail_safe_check_DB").hasClass('active')){
			valArray['failSafeDB']=1;
		}
		tempArray["args"]["params"]["config"]=valArray;
		//SB mod 
		
		if(!isScheduledBackup){
			tempArray["args"]["params"]["action"]='now';
		}
		
		if($(closestVar).hasClass('singleBackup')){
			tempArray["args"]["siteIDs"]={};
			tempArray["args"]["siteIDs"][0]=$("#siteIDForBackup").html();
		}else{
			tempArray["args"]["siteIDs"]=getSelectedSites(".dialog_cont");
		}
		
		if(isScheduledBackup){
			// for scheduledbackupNewMethod
			var reqDataArray = {};
			reqDataArray = tempArray["args"];
			tempArray['requiredData'] = {};
			tempArray['requiredData']['scheduleBackupCreate'] = {};
			tempArray['requiredData']['scheduleBackupCreate'] = reqDataArray;
			delete tempArray.action;
			delete tempArray.args;
			doCall(ajaxCallPath,tempArray,"scheduleBackupResponse","json");
		}else{
			doHistoryCall(ajaxCallPath,tempArray,"");
		}
		$("#modalDiv").dialog("close");
		$("#modalDiv").html('');
		return false;
		
		
	});
	$(".backupType").live('click',function() {
		if($(this).attr('id')=="db"){
			$("#backupDB").hide();
		}
		else{
			$("#backupDB").show();
		}
	});
	var btCheckFlag='';
	var btActiveCheck='';

	$(".bottomSites").live('mouseenter',function() {
		var position = $(this).position();
		if ($(this).hasClass('toUpdate') === false && $(this).hasClass('disconnected') === false && $(this).hasClass('maintenance') === false && $(this).hasClass('vulnsUpdate') === false) {
			$(this).find('a').css('color','white');
			$(this).prev('.bottomSites').attr('style', 'border-bottom: 1px solid #889297;');
			$(this).attr('style', 'border-bottom: 1px solid #889297;');
		}
		btCheckFlag=0;
		btActiveCheck=1;
		if(groupEditFlag==0)
		{
			if($('#bottomToolbarOptions').length != 0)
			$('#bottomToolbarOptions').remove();
			loadBottomToolbarOptions($(this).attr('sid'));
			if(bottomFullBar==1){
				if (iwpTrailPanel == true) {
					var topval=position.top-($(window).height()-88);
				}else{
					var topval=position.top-($(window).height()-43);
				}
			}
			else{
				var topval=position.top-392;
			}

			$("#bottomToolbarOptions").css("top",topval+5);

			var actH = $("#bottomToolbarOptions").offset().top+$("#bottomToolbarOptions").outerHeight();
			var maxH = $("#bottom_toolbar").offset().top;
			var diff = maxH-actH;
			var baseH = 20;
			if(diff < baseH){
				topval += diff-baseH;
				$("#bottomToolbarOptions").css("top",topval);
			}
		$("#bottomToolbarOptions").css("z-index",-1);
		}
	}).live('mouseleave',function() {
		$(this).find('a').css('color','#555');
		$(this).prev('.bottomSites').attr('style', 'border-bottom: 1px solid #d8dcdf;');
		$(this).attr('style', 'border-bottom: 1px solid #d8dcdf;');
		btActiveCheck=0;
		setTimeout(function() {  if(btCheckFlag==0 && btActiveCheck==0  ) $("#bottomToolbarOptions").remove();  }, 50);
	});
	$("#bottomToolbarOptions").live('mouseenter',function() {
		var siteID = "#s"+ $(this).attr('btsiteid');
		var currentSiteHTML = $('#bottom_sites_cont').find(siteID);
		if ($(currentSiteHTML).hasClass('toUpdate') === false && $(currentSiteHTML).hasClass('disconnected') === false && $(currentSiteHTML).hasClass('maintenance') === false  && $(currentSiteHTML).hasClass('vulnsUpdate') === false) {
			$(currentSiteHTML).addClass('bg_yellow');
			$(currentSiteHTML).find('a').css('color','white');
			$(currentSiteHTML).prev('.bottomSites').attr('style', 'border-bottom: 1px solid #889297;');
			$(currentSiteHTML).attr('style', 'border-bottom: 1px solid #889297;');
		}
		btCheckFlag=1;
		return false;
	}).live('mouseleave',function() {
		var siteID = "#s"+ $(this).attr('btsiteid');
		var currentSiteHTML = $('#bottom_sites_cont').find(siteID);
		$(currentSiteHTML).removeClass('bg_yellow');
		$(currentSiteHTML).prev('.bottomSites').attr('style', 'border-bottom: 1px solid #d8dcdf;');
		$(currentSiteHTML).attr('style', 'border-bottom: 1px solid #d8dcdf;');
		$(currentSiteHTML).find('a').css('color','#555');
		$(this).remove();
	}).live('click',function() {

	});
	$("#singleBackupNow").live('click',function() {
		loadBackup(1);
		forceBackup=1;
		$("#s"+$(this).attr('sid'),".siteSelectorContainer").click();

		$("#enterBackupDetails").click();
	});
	$("#viewBackups").live('click',function() {
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['getSiteBackupsHTML']=$(this).attr('sid');
		doCall(ajaxCallPath,tempArray,"loadBackupPopup","json");
	});

	$("#normalBackupSingle").live("click", function(){
		$("#scheduledbackshow").hide();
		$("#normalbackshow").show();
		if($(this).hasClass("active")){
			$("#scheduledBackupSingle").removeClass("active");
		}
		if($("#scheduledBackupSingle").hasClass("active")){
			$("#scheduledBackupSingle").removeClass("active");
			$("#normalBackupSingle").addClass("active");
		}
	});

	$("#scheduledBackupSingle").live("click", function(){
		$("#normalbackshow").hide();
		$("#scheduledbackshow").show();
		if($(this).hasClass("active")){
			$("#normalBackupSingle").removeClass("active");
		}
		if($("#normalBackupSingle").hasClass("active")){
			$("#normalBackupSingle").removeClass("active");
			$("#scheduledBackupSingle").addClass("active");
		}
	});
	$(".removeBackup").live('click',function() {
		
		var closestVar=$(this).closest('.item_ind');
		var topVar=$(closestVar).closest('.topBackup')
		
		$(".delConfHide",closestVar).hide();
		
		$(topVar).addClass('del');
		
		
	});
	$(".restoreBackup").live('click',function() {
		tempConfirmObject = '';
		if($(this).hasClass("needConfirm") && !$(this).hasClass("isNewBackup"))
		{
			loadConfirmationPopup($(this));
			return false;
		}	
		if($(tempConfirmObject).hasClass('restoreBackup'))
		{
			return false;
		}
		$("#modalDiv").dialog('close');
		if($(this).hasClass("isNewBackup"))
		{	
			var isCloudBackup = '';
			if($(this).hasClass("isCloudBackup"))
			{
				isCloudBackup = 'isCloudBackup';	
			}
			if ($(this).attr('parentsiteid') != '') {
				loadNewBackupChildSiteRestorePopup($(this).attr('sid'), $(this).attr('taskname'), $(this).attr('referencekey'), isCloudBackup, $(this).attr('basebackupfilename'), $(this).attr('parentsiteid'), $(this).attr('blogid'), $(this).attr('what'));
			}else{
				openRestorePopup($(this).attr('sid'), $(this).attr('taskname'), $(this).attr('referencekey'), isCloudBackup, $(this).attr('basebackupfilename'));
			}
			return false;
		}
		var tempArray={};
		tempArray['args']={};
		tempArray['args']['params']={};
		if($(this).hasClass("isCloudBackup")){
			tempArray['action']='restoreBackupDownlaod';
			tempArray['args']['params']['isCloudBackup']= 1;
		}else{
			tempArray['action']='restoreBridgeUpload';
		}
		tempArray['args']['params']['taskName']=$(this).attr('taskname');
		tempArray['args']['params']['resultID']=$(this).attr('referencekey');
		tempArray['args']['params']['backupFileBasename']=$(this).attr('basebackupfilename');
		tempArray['args']['siteIDs']=[$(this).attr('sid')];
		$(this).addClass('disabled');
		$(this).text('Queued..');
		doHistoryCall(ajaxCallPath,tempArray,"");
		
	});
	$(".confirmAction").live('click',function() {

		
		$("#modalDiv").dialog('close');
		$(tempConfirmObject).removeClass('needConfirm').click();
		if($(tempConfirmObject).hasClass('restoreBackup'))
		{
			if($(tempConfirmObject).hasClass("isNewBackup"))
			{	
				var isCloudBackup = '';
				if($(tempConfirmObject).hasClass("isCloudBackup"))
				{
					isCloudBackup = 'isCloudBackup';	
				}
				openRestorePopup($(tempConfirmObject).attr('sid'), $(tempConfirmObject).attr('taskname'), $(tempConfirmObject).attr('referencekey'), isCloudBackup, $(tempConfirmObject).attr('basebackupfilename'));
				return false;
			}
			var tempArray={};
			tempArray['args']={};
			tempArray['args']['params']={};
			if($(tempConfirmObject).hasClass("isCloudBackup")){
				tempArray['action']='restoreBackupDownlaod';
				tempArray['args']['params']['isCloudBackup']= 1;
			}else{
				tempArray['action']='restoreBridgeUpload';
			}
			tempArray['args']['params']['taskName']=$(tempConfirmObject).attr('taskname');
			tempArray['args']['params']['resultID']=$(tempConfirmObject).attr('referencekey');
			tempArray['args']['params']['backupFileBasename']=$(tempConfirmObject).attr('basebackupfilename');
			tempArray['args']['siteIDs']=[$(tempConfirmObject).attr('sid')];
			// $(this).addClass('disabled');
			// $(this).text('Queued..');
			doHistoryCall(ajaxCallPath,tempArray,"");
		}
		tempConfirmObject = '';
		return false;
	});
	$(".cancel").live('click',function() {
		$("#modalDiv").dialog('close');
	});
	$(".multiBackup").live('click',function() {
		loadBackup(1);
	});
	$(".refreshData").live('click',function() {
		//if($('.navLinks.active').attr("page") == "history")
		//{
		$(this).find('.reload_button').addClass('add_spin');
		var dates = $("#dateContainer").text();
		var userID = 0;
		if($("#activityUsers").length)	userID = $("#activityUsers").find('option:selected').attr('id');
		var searchByUser = 1;
		if(!userID){searchByUser = 0;}
		var keywords = $("#activityKeywordFilter").find('option:selected').attr('types');
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['getHistoryPageHTML']={};
		tempArray['requiredData']['getHistoryPageHTML']['dates']=dates;
		tempArray['requiredData']['getHistoryPageHTML']['searchByUser'] = searchByUser;
		tempArray['requiredData']['getHistoryPageHTML']['userID']=userID;
		tempArray['requiredData']['getHistoryPageHTML']['getKeyword']=keywords;
		tempArray['requiredData']['getHistoryPageHTML']['page']=1;
		$("#dateContainer").attr('exactdate',$("#dateContainer").text());
		$("#widgetCalendar").height(0);
		state = !state;
		doCall(ajaxCallPath,tempArray,'loadHistoryPageContent');		
	});

	$("#addWebsite").live('click',function() {
		if (completedAddonsInstallation == false) {
			return false;
		}
		var tempArray={};
		var performDocall = false;
		tempArray['requiredData']={};
		if (typeof scheduleAddonFlag !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getScheduleLists']=1;
			tempArray['requiredData']['getScheduleSiteLists']=1;
		}
		if (typeof isClientReport !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getCRSchedules']=1;
		}
		if (typeof isWPOptimize !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getOptimizeSchedules']=1;
			tempArray['requiredData']['getOptimizeScheduleSiteLists']=1;
		}
		if (performDocall) {
			doCall(ajaxCallPath,tempArray,'saveSchedulelists');
		}else{
			loadAddSite();
		}
		if(typeof isGoogle != 'undefined')
		{
			if(isGoogle=='1')
			{
				$('.googleEditOptions').hide();
			}
		}
		if(typeof isGoogleWM != 'undefined')
		{
			if(isGoogleWM=='1')
			{
				$('.googleWMEditOptions').hide();
			}
		}
		$(".add_site #adminURL,.add_site #username,.add_site #activationKey").addClass("cp_creds");
	});

	$(".del_conf").live('mousedown',function () { 
		return false;
	}).live('mouseup',function () { 
		return false;
	});
	$(".delFavourites").live('click',function() {
		var closestVar = $(this).closest('.favItems');
		$('#createFavoriteGroup').addClass('disabled');
		$('#createFavoriteGroup').css('opacity', '0.5');
		closestVar     = $("a",closestVar);
		var tempArray  = {};
		tempArray['requiredData'] = {};
		var valArray = {};
		var groupDataArray = {};
		valArray['name']   = $(closestVar).attr('iname');
		valArray['URL']	   = $(closestVar).attr('dlink');
		valArray['type']   = $(closestVar).attr('utype');
		if(!valArray['name'] && !valArray['URL']){
			closestVar     = $(this).closest('.favGroup');
			groupDataArray['groupID'] = $(closestVar).attr('gid');
			tempArray['requiredData']['removeFavouritesGroups'] = {};
			tempArray['requiredData']['removeFavouritesGroups'] = groupDataArray;
		} else {
			tempArray['requiredData']['removeFavourites'] = {};
			tempArray['requiredData']['removeFavourites'] = valArray;
		}
		tempArray['requiredData']['getFavouritesGroups']  = 1;
		tempArray['requiredData']['getFavourites'] = 1;
		doCall(ajaxCallPath,tempArray,'reloadAndLoadFavourites','json');
		
		$(this).closest('.favItems').fadeOut();
		return false;
	}).live('mouseenter',function() { $(this).closest(".favItems").addClass('delWarn')}).live('mouseout',function() { $(this).closest(".favItems").removeClass('delWarn')});
	
	$(".overflowTabs").live('mouseenter',function() {
		$(".overflowTabs").css('opacity','0.7');
		$(".overflowTabs").closest(".dropdownToggle.open_admin_tab_repo").css('display','block');
	}).live('mouseout',function() { 
		if (!$('.dropdown_btn.open').length) {
			$(".overflowTabs").css('opacity','0.5');
		} else {
			$(".overflowTabs").css('opacity','1');
		}
	});
	$("#reloadStats, .reloadSingleStats").live('click',function() {
	});


$(".toolbar_sites_cont.overflowTabs").live('click',function(){
	if ($(".open_admin_tab_repo").css('display') == 'block') {
		$(".open_admin_tab_repo").hide();
	} else if ($(".open_admin_tab_repo").css('display') == 'none'){
		$(".open_admin_tab_repo").show();
	}
});

	$("#reloadStats, .reloadSingleStats").live('click',function() {
		if(totalSites<1)
		return false;
		$('.fa.fa-repeat').addClass('fa-spin').css('color' ,'rgb(77, 167, 225)');
		var tempArray={};
		$('.fa.fa-repeat').addClass('fa-spin').css('color' ,'#49a1de');
		$("#reloadStats").addClass('disabled');
		$(".btn_reload_drop").addClass('disabled');
		$("#reloadStats").closest('div').addClass('disabled');
		$(".btn_reload_drop").closest('div').addClass('disabled');
		if($(this).hasClass('reloadSingleStats'))
		{
			tempArray['args']={};
			tempArray['args']['siteIDs']={};
			tempArray['args']['siteIDs'][0]=$(this).attr('sid');
			tempArray['args']['params']={};
			tempArray['args']['params']['forceRefresh']=1;
		}
		else
		{
			if($('.checkbox, .user_select_no').hasClass('active'))
			{
				tempArray['args']={};
				tempArray['args']['params']={};
				tempArray['args']['params']['forceRefresh']=1; // Clear Cache
			}
		}
		tempArray['action']='getStats';
		tempArray['requiredData']={};
		if(typeof isComment != 'undefined' && typeof isCommentNew != 'undefined' )
		{
			tempArray['requiredData']['manageCommentsGetRecent']=1; // To load the Recents comments on Reload Data
		}
		if(typeof isGoogle != 'undefined')
		{
			if(isGoogle=='1')
			{
				tempArray['requiredData']['googleAnalyticsEditSiteOptions']=1;
			}
		}
		tempArray['requiredData']['getSitesUpdates']=1;
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
        tempArray['requiredData']['getRecentPluginsStatus']=1;
        tempArray['requiredData']['getRecentThemesStatus']=1;
        tempArray['requiredData']['getSites']=1;
		doCall(ajaxCallPath,tempArray,"formArrayRefreshStats","json","none");
		
	});

	// Slider code
	$( "#slider-range01" ).slider({
		range: "min",
		min: 1,
		max: 30,
		step: 1,
		values: 10,
		slide: function( event, ui ) {
			$( "#amount01" ).val( ui.value );
			triggerSettingsButton();
		}
	});
	
	$( "#slider-range02" ).slider({
		range: "min",
		min: 1,
		max: 100,
		step: 1,
		values:1,
		slide: function( event, ui ) {
			$( "#amount02" ).val( ui.value );
			triggerSettingsButton();
			
		}
	});
	
	$( "#slider-range03" ).slider({
		range: "min",
		min: 0,
		max: 1000,
		step: 10,
		values:0,
		slide: function( event, ui ) {
			$( "#amount03" ).val( ui.value );
			triggerSettingsButton();
			
		}
	});
	$( "#amount01" ).val( $( "#slider-range01" ).slider( "values", 1 ) );
	$( "#amount02" ).val( $( "#slider-range02" ).slider( "values", 1 ) );		
	$( "#amount03" ).val( $( "#slider-range03" ).slider( "values", 1 ) );
	
	$(".settingsButtons").live('click',function() {
		$(".settingsItem").hide();
		var thisPage = $(this).attr('item');
		$("#saveSettingsBtn").attr("page",$(this).attr('item'));
		$("#"+$(this).attr('item')).show();
		if($(this).attr('item') == 'googleTab'){
			$('.th_sub.rep_sprite').hide();
		}else{
			$('.th_sub.rep_sprite').show();
		}
		if(thisPage == 'cronTab')
		{
			$("#saveSettingsBtn").hide();
		}
		else
		{
			$("#saveSettingsBtn").show();
		}
	});
	/***************/
	$("#settings_btn").live('click',function() {
                if(currentUserAccessLevel == 'admin'){
                    openSettingsPage('App'); 
                } else {
                    openSettingsPage('Account');
                }
                return false;
	});

	$('.settings_nav li').live('click',function(){
            if(currentUserAccessLevel == 'admin'){
		$('.settings_nav li').removeClass('active');
		$(this).addClass('active');
		var content = getSpecificSettingsContent($(this).text());

		$('.settings_main_content').html(content);
		loadSettingsPage(settingsData,$(this).text());
            }
		
	});

	$("#googleSaveSettingsBtn").live('click',function(){
		$(this).addClass('disabled');
		$(this).prepend('<div class="btn_loadingDiv left"></div>');
		$(".valid_error").hide();
		var page=$(this).attr('page');
		if(page=="googleTab")
		{
			var tempArray={};
			tempArray['action']='';
			tempArray['args']={};
			tempArray['args']['params']={};
			tempArray['requiredData']={};
			var paramsArray={};
			$("#clientCreds .formVal").each(function () {
				paramsArray[$(this).attr('id')]=$(this).val();
			});
			
			tempArray['requiredData']['googleServicesSaveAPIKeys']=paramsArray;
			doCall(ajaxCallPath,tempArray,'processSettingsUpdate');
		}
	});

	$("#saveSettingsBtn").live('click',function(){
		$(this).addClass('disabled');
		$(this).prepend('<div class="btn_loadingDiv left"></div>');
		$(".valid_error").hide();
		var page=$(this).attr('page');
		
		if(page=="settingsTab")
		{
			var accountEmail=$("#email").val();
			if(!echeck(accountEmail))
			{
				var closestVar=$("#email").closest('.valid_cont');
				$("#email").addClass("error");
				$(".valid_error",closestVar).text("Invalid email. Kindly retry");
				$(".valid_error",closestVar).show();
				$("#saveSettingsBtn").removeClass('disabled');
				$(".btn_loadingDiv").remove();
			}
			else if($(".change_pass_cont").is(":visible") && validateSettingsForm()==true )
			{
				$("#saveSettingsBtn").removeClass('disabled');
				$(".settings_cont .btn_loadingDiv").remove();	
				return false;
				
			}
			else
			{
				var tempArray={};
				var valArray={};
				tempArray['requiredData']={};
				if($(".change_pass_cont").is(":visible"))
				{
					
					valArray['newPassword']=$("#newPassword").val();
					valArray['currentPassword']=$("#currentPassword").val();
				}
				valArray['email']=$("#email").val();
				tempArray['requiredData']['updateAccountSettings']=valArray;
				tempArray['requiredData']['updateSettings']={};
				tempArray['requiredData']['updateSettings']['notifications']={};
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail'] = {};
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['frequency']=$(".emailFrequency.active").attr('def');
				if($("#notifyPlugins").hasClass('active')){
					notifyPlugins=1;
				} else{
					notifyPlugins=0;
				}
				if($("#notifyThemes").hasClass('active')){
					notifyThemes=1;
				} else {
					notifyThemes=0;
				}
				if($("#notifyWordpress").hasClass('active')){
					notifyWordpress=1;
				} else{
					notifyWordpress=0;
				}
				if($("#notifyTranslations").hasClass('active')){
					notifyTranslation=1;
				} else{
					notifyTranslation=0;
				}

				if ($("#notifyVulns").hasClass('active')) {
					notifyVulns=1;
				} else{
					notifyVulns=0;
				}

				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['coreUpdates']=notifyWordpress;
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['pluginUpdates']=notifyPlugins;
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['themeUpdates']=notifyThemes;
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['translationUpdates']=notifyTranslation;
				tempArray['requiredData']['updateSettings']['notifications']['updatesNotificationMail']['WPVulnsUpdates']=notifyVulns;
				
				tempArray['requiredData']['getSettingsAll']=1;
				
				doCall(ajaxCallPath,tempArray,"processSettingsUpdate","json","none");
			}
		}
		
		else if(page=="appSettingsTab")
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['updateSettings']={};
			tempArray['requiredData']['getSettingsAll']=1;
			tempArray['requiredData']['updateSettings']['general']={};
			var IPArray={};
			var arrayCounter=0;
			$(".ip_cont","#IPContent").each(function () { 
				IPArray[arrayCounter]=$(".IPData",this).text();
				arrayCounter++;
				
			});
			
			if($("#sendAnonymous").hasClass('active')){
				var anonymous=1;
			} else{
				var anonymous=0;
			}
			if($("#ipRangeSame").hasClass('active')){
				var ipRangeSame=1;
			} else{
				var ipRangeSame=0;
			}
			if($("#executeUsingBrowser").hasClass('active')){
				var executeUsingBrowser=1;
			} else{
				var executeUsingBrowser=0;
			}
			if($("#autoSelectConnectionMethod").hasClass('active')){
				var autoSelectConnectionMethod=1;
			} else{
				var autoSelectConnectionMethod=0;
			}
			if($("#enableReloadDataPageLoad").hasClass('active')){
				var enableReloadDataPageLoad=1;
			}else{
				var enableReloadDataPageLoad=0;
			}
			var tzVal = $('#timeZoneSelector').val();
			if(typeof tzVal!= 'undefined' && tzVal != ''){
				tempArray['requiredData']['updateSettings']['general']['TIMEZONE']=tzVal;
			}

			var autoDeleteLog = 0;
			if($(".cls_time.active").length){
				autoDeleteLog = $(".cls_time.active").attr('older');
			}

			tempArray['requiredData']['updateSettings']['general']['MAX_SIMULTANEOUS_REQUEST_PER_IP']=$("#amount01").val();
			tempArray['requiredData']['updateSettings']['general']['MAX_SIMULTANEOUS_REQUEST']=$("#amount02").val();
			tempArray['requiredData']['updateSettings']['general']['TIME_DELAY_BETWEEN_REQUEST_PER_IP']=$("#amount03").val();
			tempArray['requiredData']['updateSettings']['allowedLoginIPs'] = IPArray;
			tempArray['requiredData']['updateSettings']['allowedLoginIPsCount'] = arrayCounter;
			tempArray['requiredData']['updateSettings']['general']['sendAnonymous'] = anonymous;
			tempArray['requiredData']['updateSettings']['general']['executeUsingBrowser'] = executeUsingBrowser;
			tempArray['requiredData']['updateSettings']['general']['autoSelectConnectionMethod'] = autoSelectConnectionMethod;
			tempArray['requiredData']['updateSettings']['general']['enableReloadDataPageLoad'] = enableReloadDataPageLoad;
			tempArray['requiredData']['updateSettings']['general']['CONSIDER_3PART_IP_ON_SAME_SERVER'] = ipRangeSame;
			tempArray['requiredData']['updateSettings']['general']['autoDeleteLog'] = autoDeleteLog;
			tempArray['requiredData']['getSettingsAll']=1;

			doCall(ajaxCallPath,tempArray,"processSettingsUpdate","json","none");
			
		}
		else if(page=="securitySettingsTab")
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['updateSecuritySettings']={};
			var IPArray={};
			var arrayCounter=0;
			$(".ip_cont","#IPContent").each(function () { 
				IPArray[arrayCounter]=$(".IPData",this).text();
				arrayCounter++;
			});
                        var loginAuthType = $(".loginAuthType.active").attr('id');
			if($("#authUsername").val()!='username')
			{
				tempArray['requiredData']['updateSecuritySettings']['httpAuth']={};
				tempArray['requiredData']['updateSecuritySettings']['httpAuth']['username']=$("#authUsername").val();
				tempArray['requiredData']['updateSecuritySettings']['httpAuth']['password']=$("#authPassword").val();
			}

			tempArray['requiredData']['updateSecuritySettings']['allowedLoginIPs'] = IPArray;
			tempArray['requiredData']['updateSecuritySettings']['allowedLoginIPsCount'] = arrayCounter;
			tempArray['requiredData']['updateSecuritySettings']['loginAuthType'] = loginAuthType;
                        if(loginAuthType=="authDuo") {
                            var ikey = $("#duo_ikey").val();
                            var skey = $("#duo_skey").val();
                            var api_host = $("#duo_api_host").val();
                           if(ikey == "" || skey == "" || api_host == "") {
                                if(ikey == "")$("#duo_ikey").addClass("error");
                                if(skey == "")$("#duo_skey").addClass("error");
                                if(api_host == "")$("#duo_api_host").addClass("error");
                                $("#saveSettingsBtn").removeClass('disabled');
				$(".btn_loadingDiv").remove();
                                return false;
                            } else {
                                $("#duo_ikey").removeClass("error");
                                $("#duo_skey").removeClass("error");
                                $("#duo_api_host").removeClass("error");
                                tempArray['requiredData']['updateSecuritySettings']['duoAuth']={};
                                tempArray['requiredData']['updateSecuritySettings']['duoAuth']['duo_ikey']=ikey;
                                tempArray['requiredData']['updateSecuritySettings']['duoAuth']['duo_skey']=skey;
                                tempArray['requiredData']['updateSecuritySettings']['duoAuth']['duo_api_host']=api_host;
                            }
                            
                        }
			var enableHTTPS=0;
			if($("#enableHTTPS").hasClass('active')){enableHTTPS=1;}
			var enableSSLVerify=0;
			if($("#enableSSLVerify").hasClass('active')){enableSSLVerify=1;}
			tempArray['requiredData']['updateSecuritySettings']['enableSSLVerify'] = enableSSLVerify;
			tempArray['requiredData']['updateSecuritySettings']['enableHTTPS'] = enableHTTPS;
			tempArray['requiredData']['getSettingsAll']=1;
			doCall(ajaxCallPath,tempArray,'processSettingsUpdate');
		}
		else if(page=="uptimeTab")
		{
			var tempArray={};
			tempArray['requiredData']={};
			var paramsArray={};
			$("#uptimeTab .formVal").each(function () {
				paramsArray[$(this).attr('id')]=$(this).val();
			});
			
			tempArray['requiredData']['uptimeMonitorUptimeRobotSaveApiKey']=paramsArray;
			doCall(ajaxCallPath,tempArray,'processUptimeAPIKeySave');
		}
		else if(page=="stagingTab")
		{
			var tempArray={};
			tempArray['requiredData']={};
			
			var saveFtpReqVar = getSaveFtpReqVar();
			tempArray['requiredData']['stagingSaveMainStagingFtpDetails'] = {};
			tempArray['requiredData']['stagingSaveMainStagingFtpDetails'] = saveFtpReqVar;
			tempArray['requiredData']['getSettingsAll'] = 1;
			doCall(ajaxCallPath,tempArray,"stagingProcessSaveStagingSettings","json","none");
		}
		else if(page=="brandingTab")
		{
			var tempArray={};
			tempArray['action']='clientPluginBranding';
			tempArray['args']={};
			tempArray['args']['params']={};
			tempArray['requiredData']={};
			
			if($(".settingsItem").find(".hideClientPlugin").hasClass('active'))
			{
				tempArray['args']['params']['hide']=1;
			}
			else if($(".settingsItem").find(".doChangesCPB").hasClass('active'))
			{
                            var pluginName = $(".settingsItem").find("#pluginName").val();
                            var authourName = $(".settingsItem").find("#authourName").val();
                            var description = $(".settingsItem").find("#description").val();
                            if(pluginName == "" || authourName == "" || description == "") {
                                $("#pluginName").addClass("error");
                                $("#authourName").addClass("error");
                                $("#description").addClass("error");
                                $("#saveSettingsBtn").removeClass('disabled');
				$(".btn_loadingDiv").remove();
                                return false;
                            } else {
                                $("#pluginName").removeClass("error");
                                $("#authourName").removeClass("error");
                                $("#description").removeClass("error");
                            }
                            
                            
				var paramsArray={};
				$(".settings #brandingTab .formVal").each(function () {
					if($(this).attr('id')=="authourURL")
					{
						if($(this).val()!="http://")
						paramsArray[$(this).attr('id')]=$(this).val();
					}
					else
					paramsArray[$(this).attr('id')]=$(this).val();
				});
				tempArray['args']['params']=paramsArray;
			}
			if($(".settingsItem").find(".hideUpdatesCPB").hasClass('active')){
				tempArray['args']['params']['hideUpdatesCPB'] = 1;
			}
			if($(".settingsItem").find(".hideFWPCPB").hasClass('active')){
				tempArray['args']['params']['hideFWPCPB'] = 1;
			}
			if($(".settingsItem").find(".doChangesCPB").hasClass('active')){
				tempArray['args']['params']['doChangesCPB'] = 1;
			}
			tempArray['requiredData']['clientPluginBrandingGet']=1;
			doCall(ajaxCallPath,tempArray,"processBranding","json","none");
		}else if (page == 'appUpdateTab'){
                    $FTP = {};
                    $isDirectFS = 0;
                  	if($(".directMethod").hasClass('active')){
	                        $isDirectFS = 'Y';
	                }
	                else{
	                    	$isDirectFS = 'N';
	                }
                    if($isDirectFS == 'N' && !settingsData['data']['getSettingsAll']['settings']['FTP']['config']){
	                    var checkForm = validateForm(".FTP_form");
	                    if(!checkForm)
						{	
							$("#saveSettingsBtn").removeClass('disabled');
							$(".btn_loadingDiv").remove();
							return 0;
						}
		            }
		                    $FTP['HOST'] = $("#FTPHost").val();
		                    $FTP['PORT'] = $("#FTPPort").val();
		                    $FTP['BASE'] = $("#FTPBase").val();
		                    $FTP['USER'] = $("#FTPUser").val();
		                    $FTP['PASS'] = $("#FTPPass").val();
		                    $FTP['ftp_key'] = $("#ftp_key").val();
		                    $FTP['SSL'] = 0;
		                    $FTP['SFTP'] = 0;
		                    $FTP['PASV'] = 0;
		                    if($("#enableFTPSSL").hasClass('active')){
		                        $FTP['SSL'] = 1;	
		                    }
		                    if($("#enableSFTP").hasClass('active')){
		                        $FTP['SFTP'] = 1;	
		                    }
		                    if($('#FTPPasv').hasClass("active")){
		                    	$FTP['PASV'] = 1;
		                    }
		                    var tempArray ={};
		                    tempArray['requiredData'] = {};
		                    tempArray['requiredData']['saveAppUpdateSettings'] = {};
		                    tempArray['requiredData']['saveAppUpdateSettings']['FTPValues'] = $FTP;
		                    tempArray['requiredData']['saveAppUpdateSettings']['isDirectFS'] = $isDirectFS;
		                    doCall(ajaxCallPath,tempArray,'processSettingsUpdate');

		}else if(page == "mailSettingsTab"){
			
			var valArray = {};
			valArray = validateEmailSettingsAndGetValue();
			if(typeof valArray != 'undefined' && valArray != false){
				var tempArray={};
				tempArray['requiredData'] = {};
				tempArray['requiredData']['updateSettings'] = {};
				tempArray['requiredData']['updateSettings']['emailSettings'] = valArray;
				tempArray['requiredData']['getSettingsAll']=1;
				
				
				doCall(ajaxCallPath,tempArray,"processSettingsUpdate","json","none");
			}
			else{
				return 0;
			}
		} else if(page === 'scheduleBackupTab'){
			var emailSetting = 0;
			var tempArray ={};
			if ($("#scheduledBackupEmailSetting").hasClass('active')) {
				emailSetting = 1;
       	}
			tempArray['requiredData'] = {};
			tempArray['requiredData']["scheduledBackupProcessEmailSetting"] = {};
			tempArray['requiredData']['scheduledBackupProcessEmailSetting']['emailSetting'] = emailSetting;
			doCall(ajaxCallPath,tempArray,"processscheduledBackupProcessEmailSetting","json","none");
       	} else if(page === 'connectionMethod'){
                        var selectedMethodElem = $('#connectMethod .active')[0];
                        var connectionMethod = selectedMethodElem.id;
                        var connectionMode = '';
                        var connectionRunner = '';
                        if(connectionMethod=='manual'){
                            var selectedModeElem = $('#connectMode .active')[0];
                            connectionMode = selectedModeElem.id;
                            connectionRunner = '';
                            if(connectionMode=='commandMode'){
                                var selectedCmdElem = $('#cmdRunner .active')[0];
                                connectionRunner = selectedCmdElem.id;
                            }
                            if(connectionMode=='socketMode'){
                                var selectedSockElem = $('#sockRunner .active')[0];
                                connectionRunner = selectedSockElem.id;
                            }
                        }
                        var tempArray ={};
                        tempArray['requiredData'] = {};
                        tempArray['requiredData']['saveConnectionMethod'] = {};
                        tempArray['requiredData']['saveConnectionMethod']['connectionMethod'] = connectionMethod;
                        tempArray['requiredData']['saveConnectionMethod']['connectionMode'] = connectionMode;
                        tempArray['requiredData']['saveConnectionMethod']['connectionRunner'] = connectionRunner;
                        doCall(ajaxCallPath,tempArray,'processSettingsUpdate',"json","none");
        }
	});
	$('#testFTPConnection').live('click',function(){
		$("#completeForm .inner_cont .conn_test_error_cont").remove();
		if(!$(this).hasClass('testing')){
			$(this).removeClass('error successftp');
			var checkForm = validateForm(".FTP_form");
			if(checkForm)
			{
				tempArray={};
				ftpDetails={};
				ftpDetails ['hostName'] 	= checkForm['FTPHost'];
				ftpDetails ['hostUserName'] = checkForm['FTPUser'];
				ftpDetails ['hostPassword'] = checkForm['FTPPass'];
				ftpDetails ['ftp_key'] = checkForm['ftp_key'];
				ftpDetails ['ftp_port'] 	= checkForm['FTPPort'];
				ftpDetails['ftp_ssl'] 		= 0;
				ftpDetails['use_sftp'] 		= 0;
				ftpDetails['PASV'] 			= 0;
				if($("#enableFTPSSL").hasClass('active')){
				    ftpDetails['ftp_ssl'] = 1;	
				}
				if($("#enableSFTP").hasClass('active')){
				    ftpDetails['use_sftp'] = 1;	
				}
				if($('#FTPPasv').hasClass("active")){
					ftpDetails['PASV'] = 1;
				}
				ftpDetails['FTPBase'] = checkForm['FTPBase'];
				tempArray['requiredData'] = {};
				tempArray['requiredData']['FTPTestConnection'] = {};
				tempArray['requiredData']['FTPTestConnection'] = ftpDetails;
				tempArray['requiredData']['FTPTestConnection']['ftp_passive'] = ftpDetails['PASV'];
				tempArray['requiredData']['FTPTestConnection']['basePathValidation'] = true;
				doCall(ajaxCallPath,tempArray,'processFTPTestConnection');
				$(this).addClass('testing');
			}
		}
	});

	$(".e_close").live('click',function() { 
		$(this).closest('.conn_test_error_cont').remove();
		$("#testFTPConnection").removeClass("error");
	});
	
	$('.settings_nav li:contains("App Update")').live('click',function() {
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['isConfigWritable'] = 1;
		tempArray['requiredData']['getConfigFTP'] = 1;
		tempArray['requiredData']['appDirPermission'] = 1;
		
		doCall(ajaxCallPath,tempArray,"processAppUpdateSettings");
	});
        
        $('.settings_nav li:contains("Connection Method")').live('click',function() {
//		var tempArray = {};
//		tempArray['requiredData'] = {};
//		tempArray['requiredData']['isConfigWritable'] = 1;
//		tempArray['requiredData']['getConfigFTP'] = 1;
//		tempArray['requiredData']['appDirPermission'] = 1;
//		
//		doCall(ajaxCallPath,tempArray,"processAppUpdateSettings");
	});
	
	$('.participateInBetaCheck').live('click',function() {
		if($(this).hasClass('active'))
		var participateInBetaCheck = 1;
		else
		var participateInBetaCheck = 0;
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['updateSettingsMerge']={};
		tempArray['requiredData']['updateSettingsMerge']['general'] = {};
		tempArray['requiredData']['updateSettingsMerge']['general']['participateBeta'] = participateInBetaCheck;
		doCall(ajaxCallPath,tempArray,"","json","none");
	});
	
	$('.beta_in').live('click',function() {
		$('.participateInBetaCheck').removeClass('active');
		$('.participateInBetaCheck').click();
	});
	
	$('.beta_out').live('click',function() {
		$('.participateInBetaCheck').addClass('active');
		$('.participateInBetaCheck').click();
	});
	
	$(".triggerSettingsButton").live('keyup',function(){
		var closestVar=$(this).closest('.valid_cont');
		$('.valid_error',closestVar).hide();
		$("#saveSettingsBtn").removeClass('disabled');
	});

	$("#addIP").live('click',function() {
		$("#noIP").remove();
		var ipVal=$("#tempIP").val();
		if(ipVal!='')
		$("#IPContent").append('<div class="ip_cont"><span class="droid700 IPData">'+ipVal+'</span><span class="remove removeIP" onclick="">remove</span></div>');
		triggerSettingsButton();
	});
	$(".removeIP").live('click',function() {
		$(this).closest('.ip_cont').remove();
		triggerSettingsButton();
	});
	$("#sendAnonymous, #executeUsingBrowser, #enableReloadDataPageLoad, #clearPluginCache, #ipRangeSame").live('click',function() { 
		makeSelection(this);
	});
	$("#change_pass_btn").live('click',function () {
		
		showOrHide(this,'',"changePassContent")

	});
	$(".deleteBackup").live('click',function () {
		var closestVar=$(this).closest('.item_ind');
		var topVar=$(closestVar).closest('.topBackup');
		var closestUpdatee=$(closestVar).closest('.row_updatee');
		var mainClosestVar=$(this).closest('.row_backup_action');
		if($(this).hasClass('scheduleDelete'))
		topVar=$(this).closest('.ind_row_cont');
		

		if($(this).hasClass('yes'))
		{
			var tempArray={};
			tempArray['args']={};
			tempArray['action']='removeBackup';
			tempArray['args']['params']={};
			if($(this).attr('schedulekey')!=undefined && $(this).attr('schedulekey')!='') //SB mod need to discuss these may still execute when schedule Task is deleted.
			tempArray['args']['params']['taskName']=$(this).attr('schedulekey');
			else
			tempArray['args']['params']['taskName']=$('.trash.removeBackup',mainClosestVar).attr('taskname');
			tempArray['args']['params']['resultID']=$('.trash.removeBackup',mainClosestVar).attr('referencekey');
			tempArray['args']['siteIDs']=[$('.trash.removeBackup',mainClosestVar).attr('sid')];
			
			
			
			$(topVar).remove();
			if($(this).hasClass('scheduleDelete'))
			{
				tempArray={};
				tempArray['args']={};
				tempArray['args']['params']={};
				tempArray['action']='scheduleBackupDeleteTask';
				tempArray['args']['params']['scheduleKey']=$(this).attr('schedulekey');
			}
			if($(this).attr('schedulekey')==undefined ||  $(this).attr('schedulekey')=='') //SB mod
			{
				if($(".row_updatee_ind",closestUpdatee).length==0)
				$($(closestUpdatee).closest('.ind_row_cont').remove());
			}

			if($(this).hasClass("isNewBackup"))
			{
				tempArray['args']['params']['isNewBackup'] = 1;
			}
			doHistoryCall(ajaxCallPath,tempArray,"");
			
		}
		else
		{
			if($(this).hasClass('scheduleDelete'))
			{
				topVar=".ind_row_cont .row_summary, .row_detailed";
				closestVar=$(this).closest('.ind_row_cont');
			}
			$(topVar).removeClass('del');
			$(".delConfHide",closestVar).show();
		}
		return false;
	});

	$(".openHere").live('click',function() {
		loadAdminHere($(this).attr('sid'));
		var tabHTML = $('.toolbar_sites_cont');
	});

	$(".viewCurrentSite").live('click',function() { 
		$('.toolbar_sites_cont').css('opacity','0.5');
		var currentTabID = $(this).attr('sid');
 
		if ($(this).attr('position') === 'tabRepo'){
			var tabHTML = $('.toolbar_sites_cont');
			var totalTabs = $('.toolbar_sites_cont').length;
			var currentliTab = $(this).attr('sid');
			var currentliTabName = $(this).find('#tabSiteName').html();
			var lastTab = $(tabHTML[totalTabs-2]).attr('sid');
			var lastTabName = $(tabHTML[totalTabs-2]).find('.site_name').html()

			$(tabHTML[totalTabs-2]).find('.site_name').html(currentliTabName);
			$(tabHTML[totalTabs-2]).find('.site_name').attr('sid', currentliTab);
			$(tabHTML[totalTabs-2]).attr('sid', currentliTab);
			$(tabHTML[totalTabs-2]).find('.favicon_cont').find('img').attr('src' , site[currentliTab].favicon)
			$(tabHTML[totalTabs-2]).find('.removeTabToolTip').attr('sid', currentliTab);

			$(this).find('#tabSiteName').html(lastTabName);
			$(this).attr('sid',lastTab);

			$(this).prev('.favicon_cont').find('img').attr('src' , site[lastTab].favicon)
			$(this).find('.delTabRepo').attr('sid', lastTab);


			$("#modalDiv").show();
			$('.ui-widget-overlay').show();
			bottomToolBarHide();
			$('html').css('overflow','hidden')
			$('iframe').hide();
			$('iframe[sid="'+currentliTab+'"]').show();
			$('.toolbar_sites_cont[sid="'+currentliTab+'"]').attr('style', 'opacity: 1 !important');

			
			return;
		}

		$('.toolbar_sites_cont[sid="'+currentTabID+'"]').attr('style', 'opacity: 1 !important');

		if ( $('iframe:visible').attr('sid') === currentTabID) {
			$('iframe').hide();
			$("#modalDiv").hide();
			$('.ui-widget-overlay').hide();
			$("#pageContent").show();
			bottomToolBarShow();
			$("html").css({ overflow: 'auto' });
			$('.toolbar_sites_cont').css('opacity','0.5');
			return;
		}
		$('iframe').hide();
		$("#modalDiv").show();
		$('.ui-widget-overlay').show();
		$('iframe[sid="'+currentTabID+'"]').show()
		bottomToolBarHide();
		$('html').css('overflow','hidden')
	});

	$(".delTabRepo").live('click',function() { 
		var repoTabCount = $("#siteTabs").find("li").length;
		if (repoTabCount) {
			openAdminLoadRepoTabAfterDeletedTab(this);
		} else {
			$(".overflowTabs").remove(); 
		} 		
		return false;
	}).live('mouseenter',function() {
		$(this).closest("a").addClass('delWarn');
	}).live('mouseleave',function() {
		$(this).closest("a").removeClass('delWarn');
	});

	$(".link").live("click",function() { 
		resetBottomToolbar();
	});
	$(".adminPopout").live('click',function(e) {
		if($(this).attr('clicked')!=1)
		{
			loadAdminPopout(this,$(this).attr('sid'));
			$(this).attr('clicked','0');
		}
		
		e.stopImmediatePropagation();

	});
	$(".removeSite").live('click',function() {
		loadRemoveSite($(this).attr('sid'));
		resetBottomToolbar();

	});
	$(".newPost").live('click',function() {
		loadAdminHere($(this).attr('sid'),1);
	});

	$("#readdSite").live('click',function(){
		loadReaddSiteModal($(this).attr('sid'));
	});

	$("#iwp_maintenace").live('click',function(){
		loadMaintenaceModal($(this).attr('sid'));
	});

	
	$(".editEmail").live('click',function() {
		var closestVar=$(this).closest('.td');
		
		$(".emailEdit",closestVar).addClass('focus').focus();
	});
	$(".emailEdit").live('focus',function(e) {
		if(!$(this).hasClass('focus'))
		$(this).blur();
	});

	$(".closePopup").live('click',function(e) {
		if(toolTipData.adminPopup!="true")
		$('.showFooterSelector').qtip('hide');

		var closeTabID = $(this).closest('.toolbar_sites_cont').attr('sid');
		var tabHTML = $('.toolbar_sites_cont');
		var totalTabs = $('.toolbar_sites_cont').length;

		if($("#siteTabs").find("li").length){
			if ($('iframe:visible').attr('sid') == closeTabID) {
				$("#modalDiv").hide();
				$('.ui-widget-overlay').hide();
				$("#pageContent").show();
				$("html").css({ overflow: 'auto' });
				bottomToolBarShow(); 
				$("html").css({ overflow: 'auto' });
				$('.toolbar_sites_cont').css('opacity','0.5');
			}
			var replaceTabID = $("#siteTabs").find("li").first().find('.viewCurrentSite').attr('sid');
			var replaceTabName = $("#siteTabs").find("li").first().find('#tabSiteName').html();
			$(this).prev('.site_name').html(replaceTabName);
			$(this).prev('.site_name').attr('sid', replaceTabID);
			$(this).prev('.viewCurrentSite').attr('sid', replaceTabID);
			$(this).prev('.viewCurrentSite').prev('.favicon_cont').find('img').attr('src' , site[replaceTabID].favicon)

			$('.toolbar_sites_cont[sid="'+closeTabID+'"]').attr('sid', replaceTabID);
			$('.removeTabToolTip[sid="'+closeTabID+'"]').attr('sid', replaceTabID);

			$('iframe[sid="'+closeTabID+'"]').remove();
			$("#siteTabs").find("li").first().remove();
			if($("#siteTabs").find("li").length == 0 || $("#siteTabs").find("li").length === false){
				$(".overflowTabs").remove();
			} else {
				$('.overflowTabs').find('.dropdown_btn_val').html( $("#siteTabs").find("li").length +' <div class="arrow-up"></div>');
			}

			return ;
		}

		$(this).closest('.toolbar_sites_cont').remove();
		if ($('iframe:visible').attr('sid') == closeTabID) {
			closeOpenHereTab(closeTabID, false);
		} else {
			closeOpenHereTab(closeTabID, $('iframe:visible').attr('sid'));
		}
	});

	$('.toolbar_sites_cont').live('mouseenter',function(){
		var sid = $(this).attr('sid'); 
		if ($('iframe[sid="'+sid+'"]').css('display') === 'block'){
			$('.removeTabToolTip[sid='+sid+']').show();
			$('.toolbar_sites_cont[sid="'+sid+'"]').attr('style', 'opacity: 1 !important');

		}
	}).live('mouseleave',function(){
		$('.removeTabToolTip').hide();
	});

	$('.favorites_group_btn').live('mouseenter',function(){
		if ($(this).hasClass('disabled')){
			$(".delete_user_post_ressign_tip").show();
		}
	});
	
	
	$('.favorites_group_btn').live('mouseleave',function(){
		$(".delete_user_post_ressign_tip").hide();
	});

	$("#removeSiteConfirm").live('click',function(e) {
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['args']={};
		tempArray['args']['params']={};
		tempArray['args']['siteIDs']={};
		tempArray['action']='removeSite';
		tempArray['args']['params']['iwpPluginDeactivate']=1;
		tempArray['args']['siteIDs'][0]=$(this).attr('sid');
		tempArray['requiredData']['getGroupsSites']=1;
		tempArray['requiredData']['getSites']=1;
		tempArray['requiredData']['getSitesList']=1;
		tempArray['requiredData']['getSitesUpdates']=1;
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
		
		tempArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
		tempArray['requiredData']['getAddonPlanSiteLimit']=1;
		tempArray['requiredData']['getAddonSuitePlanActivity']=1;	
		
		$(this).addClass('disabled');
		$(this).prepend('<div class="btn_loadingDiv left"></div>');
		$("#dontRemoveSite","#modalDiv").hide();
		doCall(ajaxCallPath,tempArray,'processRemoveSite');
		return false;		
	});

	$("#readdSiteConfirm").live('click',function(){
                $(this).addClass('disabled');
		var tempArray={};
		tempArray['action']='readdSite';
		tempArray['args']={};
		tempArray['args']['params']={};
		tempArray['args']['siteIDs']={};
		tempArray['args']['params']['activationKey']=$("#readdAuthKey").val();
		tempArray['args']['siteIDs'][0]=$(this).attr('sid');
		tempArray['requiredData'] = {};
		tempArray['requiredData']['getReaddedSite'] = 1;
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
		doCall(ajaxCallPath,tempArray,"processReaddSite","json","none");
	});

	$(".addSiteToggleAction").live('click',function(e) {
		
		$(".addSiteToggleDiv").hide();
		if(!$(this).hasClass('active'))
		{
			if($(this).hasClass("assignToggleAction"))
			{
				$("#modalDiv .nano").nanoScroller({stop: true});
				$(".assignGroupItem").show();
				
				$("#modalDiv .group_selector").css('height',$("#modalDiv .group_selector").height()).addClass('nano');
				$("#modalDiv .nano").nanoScroller();
			}
			else if($(this).hasClass("folderToggleAction"))
			{
				$(".folderProtectionItem").show();
			}
			else if($(this).hasClass("advancedContentTypeAction"))
			{
				$(".advancedContentTypeItem").show();
				if(!$('.addSiteButton').hasClass('advanced')) $('.addSiteButton').addClass('advanced');
			}else if($(this).hasClass("updatesToggleAction")){
				$(".updatesConfiguringItem").show();
			}else if($(this).hasClass("siteNameToggleAction")){
				$(".siteNameItem").show();
			}
			else if($(this).hasClass("assignManagersToggleAction"))
			{	
				$("#modalDiv .nano").nanoScroller({stop: true});
				$(".assignManagersItem").show();
				
				$("#modalDiv .manager_selector").css('height',$("#modalDiv .manager_selector").height()).addClass('nano');
				$("#modalDiv .nano").nanoScroller();
			
			}else if($(this).hasClass("ftpDetailsToggleAction")){
				$('.addSiteButton').addClass('disabled');
				$(".ftpSettingsItem").show();
				if (!$(this).hasClass('noAutoFill')) {
					$('#loadingDiv').show();
					initiateFillingFtpSettings($(this).attr("sid"));
				}
				$('.addSiteToggleDiv #stagingFtpForm #hostPassword').attr('style', 'width: 170px !important; padding: 3px 41px 5px 5px;');
				$('.addSiteToggleDiv #stagingFtpForm .show_password').attr('style', 'position: absolute; right: 42px; top: 26px;');
			}
			
			$(".addSiteToggleAction").removeClass('active');
			$(this).addClass('active');
		}
		else
		{
			if($(this).hasClass("assignToggleAction"))
			{
				$(".assignGroupItem").hide();
			}
			else if($(this).hasClass("folderToggleAction"))
			{
				$(".folderProtectionItem").hide();
				
			}
			else if($(this).hasClass("advancedContentTypeAction"))
			{
				$(".advancedContentTypeItem").hide();
				
			}
			else if($(this).hasClass("assignManagersToggleAction"))
			{
				$(".assignManagersItem").hide();
			}
			$(this).removeClass('active');
		}
	});
	// Update count

	$(".panelInstall").live('click',function(e) {
		$(this).closest('.item_ind').addClass('updating');
	});
	$(".cutClass").live('click',function() {
		return false;
	});
	$("#viewHidden").live('click',function(e) {
		
		var topParent="#"+currentUpdatePage;
		makeSelection(this);
		
		if($(this).hasClass('active'))
		{
			$("#mainUpdateCont .hiddenCheck").hide();
			viewHiddenFlag=1;

			$("#mainUpdateCont .hideVar").show();
			$("#mainUpdateCont .hidden").show();
			
		}
		else
		{
			checkUpdateEmpty();
			viewHiddenFlag=0;
			$("#mainUpdateCont .hideVar").hide();
			$("#mainUpdateCont .hidden").hide();
			
			
		}

	});

	// Assign groups Hide

	$(".thumb").live('click',function() {
		var url=$(this).attr('preview');
		loadPreview(url);
	});

	
	$("#bottom_sites_cont, .settings_cont, #updates_centre_cont, #activityPopup").live('click',function(e) {
		
		if(e.target.nodeName!='A' || ($(e.target).attr('href')==undefined || $(e.target).attr('href')==''))
		return false;
		
	});
	$(".cancel_save").live('click',function() {
		$("#settings_btn").removeClass('active');
	});
	$("#enterBackupDetails").live('click',function() {
		
		$("#enterDetailsTab").removeClass('clickNone').click();
		$(".create_backup .completed").qtip({id: "backupToolTip",events: { show: function(event, api) { if(!$('#selectWebsitesTab').hasClass('completed')) return false; } },content: { text: 'Edit' }, position: { my: 'left center', at: 'right center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark' } });	
		//SB
		$('#modalDiv').dialog({width:'auto',modal:true,position: 'center',resizable: false});

	});
	$("#enterDetailsTab").live('click',function() {
		
		if(!$(this).hasClass('clickNone'))
		{
			$(".dialog_cont .th_sub.rep_sprite .current").removeClass('current');
			$(".dialog_cont .backupTab").hide();
			$(".th_btm_info").remove();
			
			$(this).addClass('current').removeClass('completed');
			$("#selectWebsitesTab").removeClass('current').addClass('completed');
			
			$("#backupOptions,#backupNow").show();
			
			$(".siteSelectorContainer,#enterBackupDetails",".dialog_cont").hide();
			//RP start
			if(repositoryAddonFlag==1)
			{
				$(".dialog_cont #backupNow").addClass('selectRepo').html('<span class="cant_schedule_tooltip">You need to set the cron to run.<br>Click to go to cron settings.</span>Select Repository<div class="taper"></div>').removeClass('rep_sprite');
				$(".dialog_cont #backupNow").closest("div").addClass('btn_next_step rep_sprite').removeClass('btn_action');
			}
			//RP End
			
			// To disable schedule backup when both the cron is disabled
			if(($("#backup_old.schedule_mech").hasClass("disabled"))&&($("#backup_new.schedule_mech").hasClass("disabled")))
			{
				$("#backupNow").parent().addClass("disabled");
			}
			else
			{
				$("#backupNow").parent().removeClass("disabled");
			}
			//end disable schedule backup
		}
	});
	$("#selectWebsitesTab").live('click',function() {
		$(".dialog_cont .th_sub.rep_sprite .current").removeClass('current');
		$(".dialog_cont .backupTab").hide();
		$(".th_btm_info").remove();
		showBackupOptions();
		$(this).addClass('current').removeClass('completed');
		$("#enterDetailsTab").removeClass('current completed').addClass('clickNone');
		$(".siteSelectorContainer,#enterBackupDetails").show();
		$("#backupOptions,#backupNow").hide();
		//RP start
		if(repositoryAddonFlag==1)
		{
			$("#backupNow").closest('div').removeClass('btn_next_step');
			$("#backupNow .taper").remove();
		}
		// RP end
		
		
	});

	$(".clear_input").live('click',function() {
		$(this).prev().val('').keyup().css("color","#AAAAAA ");
		$(this).hide();
	});
	
	
	$("#searchRepository").live('click',function() {
		$("[function='loadRepository']",$(this).closest('.optionsContent')).click();
	});

	$(".passwords").live('focus',function() {
		
		$(this).get(0).type = 'password';
		
	}).live('blur',function() {
		if($(this).val()=='Current Password' || $(this).val()=='New Password' || $(this).val()=='New Password Again' || $(this).val()=='')
		$(this).get(0).type = 'text';
		else
		$(this).get(0).type = 'password';
	});
	$(".onEnter").live('keypress',function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		
		if(code == 13) { 
			$($(this).attr("onenterbtn")).click();
		}
		else
		{
			if($(this).hasClass('groupClear'))
			$("#duplicateGroup").remove();
		}

	});
	$(".closeBottomToolBar").live('click',function(e) {
		resetBottomToolbar();
	});
	$(".groupClear").live('focus',function(e) {
		
	});
	loadSettingsPage(settingsData);
	var isAppVersionBeta = appVersion.search(/beta/i);
	if(totalSites>0 && (settingsData.data.getSettingsAll.settings.general.enableReloadDataPageLoad==undefined || settingsData.data.getSettingsAll.settings.general.enableReloadDataPageLoad==1) && reloadStatsControl==1)
	{
		$("#reloadStats").click();
	}
	else if((totalSites > 0)&&(isAppVersionBeta > 0))
	{
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
		tempArray['requiredData']['bypassHistoryAjaxCall'] =1;
		doCall(ajaxCallPath,tempArray,'getUpdateOnlyForBeta','json',"none");
	}

	$("#currentVersionNumber").text('v'+appVersion);
	if(updateAvailable==false)
	loadPanelUpdateDefault();
	else
	loadPanelUpdate(updateAvailable);
	
	if(updateAvailableV3==false){}else{
		loadPanelUpdateV3(updateAvailableV3);
	}
	if (typeof switchToV3 != 'undefined' && switchToV3 != null && switchToV3 == '1') {
		$('#switchToV3').show();
	}
	$(".sendReport").live('click',function() {
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['getReportIssueData']=$(this).attr('actionid');
		doCall(ajaxCallPath,tempDataArray,'loadReport','json');
		$("#historyQueue").hide();
		return false;

	});
	$(".retryAllFailure").live('click',function(){
		if($(this).hasClass("needConfirm")){
			$("#historyQueue").hide();
			loadConfirmationPopup($(this));
			return false;
		}
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['manualRetryALLFailedTask']=$(this).attr('actionid');
		doCall(ajaxCallPath,tempDataArray,'','json');
		$("#historyQueue").hide();
		return false;
	});
	$(".moreInfo").live('click',function() {
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['getResponseMoreInfo']=$(this).attr('historyid');
		doCall(ajaxCallPath,tempDataArray,'loadMoreInfo','json');
		$("#historyQueue").hide();
		return false;

	});
	$(".curlVerbose").live('click',function() {
			var tempDataArray={};
			tempDataArray['requiredData']={};
			tempDataArray['requiredData']['getResponseCurlVerbose']=$(this).attr('historyid');
			doCall(ajaxCallPath,tempDataArray,'loadCurlVerbose','json');
			$("#historyQueue").hide();
			return false;
	});

	$("#sendReportBtn").live('click',function() {
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['sendReportIssue']={};
		tempDataArray['requiredData']['sendReportIssue']['email'] = $("#emailToReport").val(); 
		if($(this).attr('actiontype')=='historyIssue'){
			tempDataArray['requiredData']['sendReportIssue']['report'] = $("#panelHistoryContent").val(); 
			tempDataArray['requiredData']['sendReportIssue']['actionID'] = $("#panelHistoryActionID").val();
		}
		tempDataArray['requiredData']['sendReportIssue']['comment'] = $("#customerComments").val(); 
		tempDataArray['requiredData']['sendReportIssue']['appVersion'] = appVersion;
		tempDataArray['requiredData']['sendReportIssue']['type'] = $(this).attr('actiontype');
		tempDataArray['requiredData']['sendReportIssue']['appInstallHash'] = appInstallHash;
		if($("#customerComments").val()!='')
		{
			$("#customerComments").removeClass('error');
			$(this).append('<div class="btn_loadingDiv left"></div>').addClass('disabled');
			doCall(ajaxCallPath,tempDataArray,'processReport','json','noProgress');
		}
		else 
		$("#customerComments").addClass('error');

	});

	$("#sendTestEmail").live('click',function() {
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['updatesNotificationMailTest']=1;
		$(this).removeClass('failure').removeClass('success');
		$(this).addClass('sending');
		doCall(ajaxCallPath,tempDataArray,'processTestEmail','json','noprogress');
	});
	$("#updateNotifyClose").live('click',function() { 
		var tempDataArray={};
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['updateHideNotify']=$(this).attr('version');
		doCall(ajaxCallPath,tempDataArray,'','json');
		$("#updates_centre_notif").remove();
	});
	$(".updateActionBtn").live('click',function() { 
		if($(this).hasClass("needConfirm")){
			loadConfirmationPopup($(this));
			return false;
		}
		$(this).prepend('<div class="btn_loadingDiv left"></div>').addClass('disabled');

		if($(this).attr('btnAction')=="check"){
			var tempDataArray={};
			tempDataArray['requiredData']={};
			tempDataArray['requiredData']['forceCheckUpdate']=1;
			tempDataArray['requiredData']['getCachedV3UpdateDetails']=1;
			tempDataArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
			tempDataArray['requiredData']['getAddonPlanSiteLimit']=1;
			tempDataArray['requiredData']['getAddonSuitePlanActivity']=1;
			tempDataArray['requiredData']['isAddonSuitePlanCancelMessage']=1;
			doCall(ajaxCallPath,tempDataArray,'processCheckUpdate','json');
			return false;
		} else if($(this).attr('btnAction')=="update"){   
			$(".updateActionBtn").not(".updateExtraBtn").remove();
			$("#updateOverLay").show();
			$("#updates_centre_cont").css({"z-index":"1021","box-shadow":"0 0 46px rgba(0,0,0,0.7)"});
			processUpdateNow($(this).attr('version'));
			stopAllAction=true;
			$("#updateCentreBtn").css({'position':'relative','z-index':'1020'}).die();
			return false;
		}

	});
	$(".updateActionBtnV3").live('click',function() { 
		$(this).prepend('<div class="btn_loadingDiv left"></div>').addClass('disabled');

		if($(this).attr('btnAction')=="check"){
			var tempDataArray={};
			tempDataArray['requiredData']={};
			tempDataArray['requiredData']['forceCheckUpdate']=1;
			tempDataArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
			tempDataArray['requiredData']['getAddonPlanSiteLimit']=1;
			tempDataArray['requiredData']['getAddonSuitePlanActivity']=1;
			tempDataArray['requiredData']['isAddonSuitePlanCancelMessage']=1;
			doCall(ajaxCallPath,tempDataArray,'processCheckUpdate','json');
			return false;
		} else if($(this).attr('btnAction')=="update"){   
			$(".updateActionBtnV3").not(".updateExtraBtnV3").remove();
			$("#updateOverLay").show();
			$("#updates_centre_cont_V3").css({"z-index":"1021","box-shadow":"0 0 46px rgba(0,0,0,0.7)"});
			processUpdateNowV3($(this).attr('version'));
			stopAllAction=true;
			$("#updateCentreBtnV3").css({'position':'relative','z-index':'1020'}).die();
			return false;
		}

	});
	$("#updateCentreBtn").live('click',function() { 
		$("#header_nav .first-level").removeClass('active_color');
		$(".checkUpdateError").remove();
		$("#updates_centre_cont_V3").hide();
		$("#updateCentreBtnV3").removeClass('active');
		showOrHide(this,'active','updates_centre_cont','');
		closeDialogs(1);
		return false;
	});
	$("#updateCentreBtnV3").live('click',function() { 
		$("#header_nav .first-level").removeClass('active_color');
		$(".checkUpdateError").remove();
		$("#updateCentreBtn").removeClass('active');
		$("#updates_centre_cont").hide();
		showOrHide(this,'active','updates_centre_cont_V3','');
		closeDialogs(1);
		return false;
	});
	$("#updateClientButton").live('click',function() {
		if(clientUpdatesAvailable != false && clientUpdatesAvailable.siteIDs != undefined){
				processClientUpdate(clientUpdatesAvailable.siteIDs);
		
		}	
	});
	//codeSprint
	$("#notifyCentreBtn").live('click',function() { 
		$("#header_nav .first-level").removeClass('active_color');
		showOrHide(this,'active','notifyCentreContent','');
		closeDialogs(1);
		return false;
	});
	
	
	$("#updateClientConfirm").live('click',function() { 
		var tempDataArray={};
		clientPluginUpdateSiteIDsCount=0;
		$(".clientUpdateNotification").hide();
		tempDataArray['action']='updateClient';
		tempDataArray['args']={};
		tempDataArray['args']['siteIDs']=clientUpdateSites;
		tempDataArray['args']['params']={};
		tempDataArray['args']['params']['clientUpdateVersion']=clientUpdatesAvailable.clientUpdateVersion;
		// tempDataArray['args']['params']['clientUpdatePackage_b64encoded']=clientUpdatesAvailable.clientUpdatePackage;// Modsec rule blocking the request 
		tempDataArray['requiredData']={};
		tempDataArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
		doHistoryCall(ajaxCallPath,tempDataArray,'formArrayClientUpdate','json');
		$("#modalDiv").dialog("destroy");
		clientUpdateSites=false;
		return false;
	});
	$(".notNowUpdate").live('click',function() { 
		notNowUpdate=true;
		$("#modalDiv").dialog("destroy");
	});
	$(".closeTour").live('click',function() { 
		tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['closeTour']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData');
	});
	$(".closeRenewalNotification").live('click',function() { //codeSprint
		var tempArray={}; 
		var valArray={}; 
		valArray[$(this).attr('notifingitem')]=true;
		tempArray['requiredData']={};
		tempArray['requiredData']['updateUserhelp']= valArray; 
		tempArray['requiredData']['getUserHelp']= 1;
		doCall(ajaxCallPath,tempArray,'setTooltipData');
	});
	
	// if(toolTipData.closeTour!="true")
	// loadFeatureTourPopup();
	
	if(!completedInitialSetup && iwpTrailPanel == false && completedAddonsInstallation == true){
		loadBasicSettingsPopup();
	}

	if (completedAddonsInstallation == false) {
		loadIWPPopupInitalLogin();
		setTimeout(function () {
			$('.loginIWPAtInitial').click();
		},10)
	}

	// $(".takeTour").live('click',function() { 
	// 	loadFeatureTour();
	// });

	$(".closeUpdateNotification").live('click',function() { 
		tempArray={}; tempArray['requiredData']={}; valArray={}; valArray['closeUpdateNotification_2-3-0-beta-1']=true; tempArray['requiredData']['updateUserhelp']= valArray; tempArray['requiredData']['getUserHelp']= 1;  doCall(ajaxCallPath,tempArray,'setTooltipData');
	});
	dynamicResize();
	$(".nano").nanoScroller();
	$(".n_close").live('click',function() {
		var thisPar = $(this).closest(".notification_cont");
		$(thisPar).find(".closeRenewalNotification").click();
		
		$(this).closest('.notification').remove();
	});
	dynamicResize();
	$(".editSiteBtn, .editSiteBtnForFtpSettings").live('click',function() {

		var tempArray={};
		var performDocall = false;
		tempArray['requiredData']={};
	 	tempEditObject = $(this).attr('sid');
		if (typeof scheduleAddonFlag !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getScheduleLists']=1;
			tempArray['requiredData']['getScheduleSiteLists']=1;
		}
		if (typeof isClientReport !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getCRSchedules']=1;
		}
		if (typeof isWPOptimize !='undefined') {
			performDocall = true;
			tempArray['requiredData']['getOptimizeSchedules']=1;
			tempArray['requiredData']['getOptimizeScheduleSiteLists']=1;
		}
		if (performDocall) {
			doCall(ajaxCallPath,tempArray,'saveSchedulelists');
		}else{
			loadAddSite($(this).attr('sid'));
			loaEditSiteContent($(this).attr('sid'));
		}
		
	});	
	
	$(".dropdown_btn").live('click',function() { 

		$(this).toggleClass('open');
		$(".dropdownToggle",$(this).closest('.dropdown_cont')).toggle();
		return false;
	});
	$(".dropOption").live('click',function() { 
		$(".dropdown_btn span.dropdown_btn_val",$(this).closest('.dropdown_cont')).text($(this).text()).attr('dropopt',$(this).attr('dropopt'));
		$(".dropdownToggle",$(this).closest('.dropdown_cont')).hide();
		$(".dropdown_btn",$(this).closest('.dropdown_cont')).removeClass("open");
		return false;
	});
	$("#installIWPAddons").live('click',function() { 
		if(!$(this).hasClass('disabled'))
		loadIWPPopup(this);
	});
	$("#checkNowAddons").live('click',function() { 
		if($(this).attr('register')=="no")
		{
			loadIWPPopup(this);
		}
		else
		{
			var tempDataArray={};
			tempDataArray['requiredData']={};
			tempDataArray['requiredData']['forceCheckUpdate']=1;
			tempDataArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
			tempDataArray['requiredData']['getAddonPlanSiteLimit']=1;
			tempDataArray['requiredData']['getAddonSuitePlanActivity']=1;
			tempDataArray['requiredData']['isAddonSuitePlanCancelMessage']=1;
			doCall(ajaxCallPath,tempDataArray,'checkIWPAddons','json');
		}

	});

	$(".updateIWPAddons").live('click',function() { 
		if($(this).hasClass("needConfirm")){
			loadConfirmationPopup($(this));
			return false;
		}
		if(!$(this).hasClass('disabled'))
		installIWPAddons($(this).attr('authlink'),1);
	});
	$(".cc_addon_mask").live('click',function() { 
		var tempArray={};
		tempArray['requiredData']={};

		if($(".cc_addon_img",this).hasClass('on'))
		{
			tempArray['requiredData']['deactivateAddons']={};
			tempArray['requiredData']['deactivateAddons']['addons']={};
			tempArray['requiredData']['deactivateAddons']['addons'][0]={};
			tempArray['requiredData']['deactivateAddons']['addons'][0]["slug"]=$(this).attr("addonslug");
			tempArray['noGeneralCheck']=1;

		}
		else
		{
			tempArray['requiredData']['activateAddons']={};
			tempArray['requiredData']['activateAddons']['addons']={};
			tempArray['requiredData']['activateAddons']['addons'][0]={};
			tempArray['requiredData']['activateAddons']['addons'][0]["slug"]=$(this).attr("addonslug");
			tempArray['noGeneralCheck']=1;
		}
		doCall(ajaxCallPath,tempArray,'processAddonActivation');
		$(".cc_addon_img",this).toggleClass("on off");
		return false;
	});
	$("#iwpAddonsBtn").live('click',function() { 
		$("#modalDiv").dialog('close');
		document.title = 'InfiniteWP - Addons';
		$('.page_section_title').html('Addons');
		$(this).addClass('active_color');
		$(".navLinks").removeClass('active');
		processPage("addons");
	});
	$(".error").live('focus',function() { 
		$(this).removeClass('error');
	});
	$(".stopCall").live('click',function() { 
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['getWaitData']={};
		tempArray['requiredData']['getWaitData'][$(this).attr('actionid')]='sendData';
		doCall(ajaxCallPath,tempArray,'');

	});
	$(".loginIWP").live('click',function() { 
		if($(this).hasClass('no'))
		{
			$("#modalDiv").dialog('close');
			return false;
		}
		$(this).prepend('<div class="btn_loadingDiv left"></div>').addClass('disabled');
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['IWPAuthUser']={};
		if($(this).hasClass('overwrite') && $(this).hasClass('yes'))
		{
			tempArray['requiredData']['IWPAuthUser']['process']="changeRegister";	
			tempArray['requiredData']['IWPAuthUser']['username']=usernameTemp;
			tempArray['requiredData']['IWPAuthUser']['password']=passwordTemp;
		}
		else{
			$(".loginError").html('');
			usernameTemp=$("#username").val();
			passwordTemp=$("#password").val();
			tempArray['requiredData']['IWPAuthUser']['username']=usernameTemp;
			tempArray['requiredData']['IWPAuthUser']['password']=passwordTemp;
			tempArray['requiredData']['IWPAuthUser']['login']="yes";
		}
		if($(this).attr('actionvar')!=undefined)
		tempArray['requiredData']['IWPAuthUser']['action']=$(this).attr('actionvar');
		doCall(ajaxCallPath,tempArray,"processIWPLogin");
		
	});


	$(".yesReport").live('click',function() {
		$(".dialog_cont .issue_content, .dialog_cont #sendReportBtn").show();
		$(".dialog_cont .preReport").hide();
	});
	$("#autoSelectConnectionMethod").live('click',function() {
		makeSelection(this);
		if($(this).hasClass('active'))
		{
			$("#executeUsingBrowser").addClass('disabled').removeClass('active');
		}
		else
		$("#executeUsingBrowser").removeClass('disabled');
	});
	$("#addToFavouritesCustom").live('click',function() {
		loadAddToFavourites();
	});

	$("#createFavoriteGroup").live('click',function() {
		loadAddFavoritesGroup();
	});

	$('#createFavoriteGroup').hover(function(){
		$('.delete_user_post_ressign_tip').css('display','block');
	  }, 
	  function () {
	    $('.delete_user_post_ressign_tip').css('display','none');
	  }
	);

	$("#uploadZipFavorites").live("click", function(){
		$("#iname").removeClass('error').val('');
		$('#favAlreadyExist').hide();
		$("#uploadZipRequiredError").hide();
		if ($(".upload_name").length) {
			$(".addFavoriteZipDisplaySpace").css('margin','20px 20px 40px');
		}
		$("#uploadURLFavoritesContent").hide();
		$("#uploadZipFavoritesContent").show();
		if($(this).hasClass("active")){
			$("#uploadURLFavorites").removeClass("active");
		}
		if($("#uploadURLFavorites").hasClass("active")){
			$("#uploadURLFavorites").removeClass("active");
			$("#uploadZipFavorites").addClass("active");
		}
	});

	$("#uploadURLFavorites").live("click", function(){
		$("#iname").removeClass('error').val('');
		$('#favAlreadyExist').hide();
		$("#dlink").removeClass('error');
		$(".addFavoriteZipDisplaySpace").css('margin','20px 20px 20px');
		$("#uploadZipFavoritesContent").hide();
		$("#uploadURLFavoritesContent").show();
		if($(this).hasClass("active")){
			$("#uploadZipFavorites").removeClass("active");
		}
		if($("#uploadZipFavorites").hasClass("active")){
			$("#uploadZipFavorites").removeClass("active");
			$("#uploadURLFavorites").addClass("active");
		}
	});

	$("#uploadFavouriteThemesAndPlugins").live("click", function(){
		$(".addFavoriteZipDisplaySpace").css('margin','20px 20px 50px');
		$("#uploadZipRequiredError").hide();
	});
	$("#addToFavoriteCheckbox").live("click", function(){
		if($(this).hasClass('active')){
			$(this).removeClass('active');
			$('.zipNameAfterAddFavorite').hide();
		} else {
			$(this).addClass('active');
			$('.zipNameAfterAddFavorite').show();
		}
	});
	
	$(".addToFavouritesGroupBtn").live('click',function() {
		$('#favAlreadyExist').hide();
		$('#gname').parent('.dialog_content.inner_cont').css('margin','20px 20px 20px');
		var allFavorites = $('.favItems.active',".favSearch");
		var i = 0;
		var newfavoritesGroup = [];
		var tempArray = {};
		var valArray = {};
		tempArray['requiredData'] = {};
		$(allFavorites).each(function () {
			newfavoritesGroup.push($(allFavorites[i++].innerHTML).attr('id'));
		});	
		valArray['items'] = newfavoritesGroup;
		valArray['gname'] = $("#gname").val();
		valArray['type']  = activeItem.toTitleCase();
		var isAlreadyExist = isFavoritesGroupAlreadyExist(activeItem, valArray['gname']);
		if(isAlreadyExist){
			return false;
		}
		tempArray['requiredData']['addToFavouritesGroup'] = {};
		tempArray['requiredData']['addToFavouritesGroup'] = valArray;
		tempArray['requiredData']['getFavouritesGroups']  = 1;
		tempArray['requiredData']['getFavourites'] 		  = 1;
		if (valArray['gname']) {
			doCall(ajaxCallPath,tempArray,'reloadAndLoadFavourites','json');
			$("#modalDiv").html();
			$("#modalDiv").dialog("close");
			$("#createFavoriteGroup").addClass('disabled');
			$("#createFavoriteGroup").css('opacity','0.5');
		} else {
			$('#gname').addClass('error');
		}
	});

	$(".addToFavouritesBtn").live('click',function() {
		$('#favAlreadyExist').hide();
		var tempArray = {};
		tempArray['requiredData'] = {};
		var favoritesArray = {};
		favoritesArray['name'] = $(".dialog_cont #iname").val();
		var isAlreadyExist = isFavoritesAlreadyExist(activeItem, favoritesArray['name']);
		if (isAlreadyExist) {
			return true;
		}
		if ($('#uploadZipFavoritesContent').css('display') == 'block') {
			if ($(".upload_name").length) {
				var fileName    = $('.installFileNames').html().replace(/ /g,"%20");
				favoritesArray['folderPath'] = "uploads/favorites/";
				favoritesArray['currentURL'] = systemURL;
				favoritesArray['fileName'] = fileName;
				favoritesArray['directUpload'] = 1;
			} else {
				favoritesArray['folderPath'] = '';
			}
		} else if ($('#uploadURLFavoritesContent').css('display') == 'block') {
			favoritesArray['URL'] = $(".dialog_cont #dlink").val();
		}
		favoritesArray['type'] = $(this).attr('utype');
		if (!favoritesArray['name']) {
			$("#iname").addClass('error');
		} 
		if (!favoritesArray['URL'] && !favoritesArray['folderPath']) {
			$("#dlink").addClass('error');
			$("#uploadZipRequiredError").show();
		} 
		if ((favoritesArray['name']) && (favoritesArray['URL'] || favoritesArray['folderPath'])) {
			tempArray['requiredData']['addFavourites'] = {};
			tempArray['requiredData']['addFavourites'] = favoritesArray;
			tempArray['requiredData']['getFavouritesGroups'] = 1;
			tempArray['requiredData']['getFavourites'] = 1;
			
			doCall(ajaxCallPath,tempArray,'reloadAndLoadFavourites','json');
		$("#modalDiv").html();
		$("#modalDiv").dialog("close");
		}

		
	});
	$(".cTypeRadio").live('click',function() {
		$(".cTypeRadio").removeClass('active');
		$(this).addClass('active');
	});
	
	$("#backup_old").live('click',function() {
		$(".fail_safe_options").show();
	});
	
	$("#backup_new").live('click',function() {
		$(".fail_safe_options").hide();
	});

	$(window).on('scroll', function () {
		
		var scrollTop     = $(window).scrollTop();
		
		if( $('.actionContent').is(':visible') ) {
			
			var elementOffset = $('.actionContent').offset().top;
			
			distance      = (elementOffset - scrollTop);
			if(distance<10)
			{
				$('.actionContent .th.rep_sprite').addClass('fixed');
			}
			else
			{
				$('.actionContent .th.rep_sprite').removeClass('fixed');
			}
		}

	});
	$('.selectOnText').live('click',function(){
		$(this).focus();
		$(this).select();
		return false;
	});
	
	$('.backupMechanism').live('mouseenter',function(){
		var parent = $(this);
		if($(this).hasClass("disabled"))
		{
			$('.tooltip_backup_method',parent).show();
		}
	});
	
	$('.backupMechanism').live('mouseleave',function(){
		var parent = $(this);
		if($(this).hasClass("disabled"))
		{
			$('.tooltip_backup_method',parent).hide();
		}
	});

	$('.backupMechanism').live('click',function(){
		$('.phoenix-exclusion').show();
		$('.databaseEncryptionPhraseDiv').hide();
		if ($(this).attr('mechanism') == 'advancedBackup') {
			$('.phoenix-exclusion').hide();
			$('.databaseEncryptionPhraseDiv').show();
		}
	});
	
	$("#cron_activate_btn").live('click',function() {
		var tokenValue = $("#EasyCronApiToken").val();
		if((typeof tokenValue != 'undefined')&&(tokenValue != ''))
		{
			var tempArray={};
			tempArray['requiredData']={};
			tempArray['requiredData']['manageEasyCron::activate']= {};
			tempArray['requiredData']['manageEasyCron::activate']['token']= tokenValue;
			tempArray['requiredData']['manageEasyCron::isActive']= {};
			tempArray['requiredData']['manageEasyCron::isActive'] = 1;
			tempArray['requiredData']['manageEasyCron::getTokenFromDB']= {};
			tempArray['requiredData']['manageEasyCron::getTokenFromDB'] = 1;
			doCall(ajaxCallPath,tempArray,"setEasyCronActivate");
		}
	});
	
	$('.settings_nav li:contains("Cron")').live('click', function() {
		//$("#saveSettingsBtn").hide();
		$("#easycronNote").html('Checking easycron status...');
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['manageEasyCron::isActive']= {};
		tempArray['requiredData']['manageEasyCron::isActive'] = 1;
		tempArray['requiredData']['manageEasyCron::getTokenFromDB'] = 1;
		tempArray['requiredData']['getSystemCronRunningFrequency']= {};
		tempArray['requiredData']['getSystemCronRunningFrequency']['bothCheck']= 1;
		tempArray['requiredData']['Manage_IWP_Cron::isActive'] = 1;
		doCall(ajaxCallPath,tempArray, 'setEasyCronActivate');
		clipboardjsTrigger();
	});
	
	$("#cron_deactivate_btn").live('click',function() {
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['manageEasyCron::deactivate']= {};
		tempArray['requiredData']['manageEasyCron::isActive']= {};
		tempArray['requiredData']['manageEasyCron::getTokenFromDB']= {};
		tempArray['requiredData']['manageEasyCron::isActive'] = 1;
		tempArray['requiredData']['manageEasyCron::deactivate']= 1;
		tempArray['requiredData']['manageEasyCron::getTokenFromDB'] = 1;
		doCall(ajaxCallPath,tempArray,"setEasyCronActivate");
	});
	
	$("#EasyCronApiToken").live('focus',function() {
		if($(this).hasClass('disabled'))
		$(this).blur();
	});
	
	$(".tooltip_backup_method").live('click',function() {
		$("#modalDiv").dialog("destroy");
		openSettingsPage('Cron');
		return false;
	});
	
	$(".stop_pending").live('click',function() {
		var multicall = 0;
		if($(this).hasClass("stop_multicall")){multicall=1;}
		var clearWhat = 'history';
		if(!$(this).hasClass("single")){
			clearWhat = 'action';
		}
		var taskID;
		if($(this).hasClass("single")){
			taskID = $(this).attr("historyID");
		}else{
			taskID = $(this).attr("actionID");
		}
		killTaskConfirmationPopup(clearWhat,taskID,multicall);
		return false;
	});
	
	$(".stop_multicall,.stop_pending").live('mouseenter',function() {
		var parent_ind_queue = $(this).closest(".queue_ind_item.historyItem");
		$(parent_ind_queue).addClass("stopWarn");
	});
	
	$(".stop_multicall,.stop_pending").live('mouseleave',function() {
		var parent_ind_queue = $(this).closest(".queue_ind_item.historyItem");
		$(parent_ind_queue).removeClass("stopWarn");
	});
		
	$(".tooltip_backup_method").live('mouseenter',function() {
		$(this).show();
		$(".backupMechanism").removeClass("disabled");
	}).live('mouseleave',function() {
		$(this).hide();
		$(".backupMechanism").addClass("disabled");
		$(".backupMechanism.active").removeClass("disabled");
	});
	
	$(".cant_schedule_tooltip").live('click',function() {
		$("#modalDiv").dialog("destroy");
		bottomToolBarShow();
		$("html").css({ overflow: 'auto' });
		showOrHide(this,"active","settings_cont");
		$('.settingsButtons').each(function(){
			if($(this).attr("item")=="cronTab")
			{
				$(this).click();
			}
		});
		return false;
	});
	
	$(".cant_schedule_tooltip").live('mouseenter',function() {
		$(this).closest(".disabled").addClass("disabled_backup_mech");
		$(this).closest(".disabled").removeClass("disabled");
	});
	
	$(".cant_schedule_tooltip").live('mouseleave',function() {
		$(this).closest(".disabled_backup_mech").addClass("disabled");
		$(this).closest(".disabled_backup_mech").removeClass("disabled_backup_mech");
	});
	
    $(".iwpServerInfo").live('click', function(event){
        var siteIDs = new Array();
        siteIDs.push($(this).attr('sid'));
        
        var tempArray={};
        tempArray['action']='backupTest';
        tempArray['args']={};
        tempArray['args']['siteIDs']=siteIDs;
        var requireDataArray={};
        requireDataArray['iwpLoadServerInfo']=$(this).attr('sid');
        tempArray['requiredData']= requireDataArray;
        doCall(ajaxCallPath,tempArray,'iwpLoadServerInfo','json');
        return false; 
    });
	
	$(window).on('beforeunload', function(ev){
		if(typeof showBrowserCloseWarning != 'undefined'){
			if(showBrowserCloseWarning == 'backup'){
				return 'A backup is under progress. Closing the window / logging out may result in error. Please wait till its completed.';
			}
			if(showBrowserCloseWarning == 'staging'){
				return 'A staging is under progress. Closing the window / logging out may result in error. Please wait till its completed.';
			}
			if(showBrowserCloseWarning == 'installClone'){
				return 'A cloning is under progress. Closing the window / logging out may result in error. Please wait till its completed.';
			}
		}
	});

	$('.cp_creds').live("paste", function(e) {
		var id = $(this).attr('id');
		$(this).val('');
		$(this).clone().prependTo($(this).parent()).val('');
		setTimeout(function(){ 
			var myVal = $(e.target).val();
			$('.cp_creds#'+id+':last').remove();
			myVal = myVal.split('|^|');
			if(myVal.length > 1){
				if(id == 'readdAuthKey'){
					$('.cp_creds#readdAuthKey').val(myVal[2]);
				}else{
					$('.cp_creds#adminURL').val(myVal[0]);
					$('.cp_creds#username').val(myVal[1]);
					$('.cp_creds#activationKey').val(myVal[2]);
					if(typeof myVal[3] !='undefined'){
						$('.cp_creds#websiteURL').val(myVal[3]);
					}else{
						var URL = removeLastDirectoryPartOf(myVal[0]);
						$('.cp_creds#websiteURL').val(URL);
					}
				}
			}else{
			$('.cp_creds#'+id).val(myVal[0]);
			}
		},10);
	});


	$('.maintenanceRadio').live('click',function(){
		$('.maintenanceRadio').removeClass('active');
		$(this).addClass('active');
	});

	$("#maintenanceSiteConfirm").live('click',function(){
		$(this).addClass('disabled');
		$("#loadingDiv").show();
		var tempArray={};
		tempArray['action']='iwpMaintenance';
		tempArray['args']={};
		tempArray['args']['params']={};
		tempArray['args']['siteIDs']={};
		tempArray['args']['params']['mcheck']=$(".maintenanceRadio.active").attr('val');
		tempArray['args']['params']['mHTML_b64encoded']=$.base64('btoa', $("#maintenanceHTML").val(), true);
		tempArray['args']['siteIDs'][0]=$(this).attr('sid');
		tempArray['requiredData'] = {};
		tempArray['requiredData']['iwpMaintenance'] = 1;
		doCall(ajaxCallPath,tempArray,"processMaintenanceSite","json","none");
	});

	$('.add_links').live('click',function(){
		$('.edit_links').trigger('click');
	});

	$('.add_notes').live('click',function(){
		$('.edit_note').trigger('click');
	});
	
	$('.edit_note').live('click',function(){
		var site_notes = $(this).parent().parent().find('.site_notes');
		var siteID = $(this).closest('.site_flap_cont_data').attr('btsiteid');
		var note = '';
		if (site[siteID]['notes'] != null) {
			note = site[siteID]['notes'];
		}
		$('<textarea class="edit_site_notes" placeholder="For line breaks use <br>">'+note+'</textarea><i class="save_note">Save Note</i>').insertAfter(site_notes);
		site_notes.hide();
		var btmSiteSnap = $('.site_flap_cont_data[btsiteid="'+siteID+'"]');
		btmSiteSnap.find('.edit_note').parent().css({'height':$('.edit_site_notes').parent().height()+'px'});
		$(this).hide();
	});
	
	$('.edit_links').live('click',function(){
		var site_links = $(this).parent().parent().find('.site_links');
		var siteID = $(this).closest('.site_flap_cont_data').attr('btsiteid');
		var link = '';
		if (site[siteID]['links'] != null) {
			link = site[siteID]['links'];
		}
		$('<textarea class="edit_site_links" placeholder="separated by commas">'+link+'</textarea><i class="save_links">Save Links</i>').insertAfter(site_links);
		site_links.hide();
		var btmSiteSnap = $('.site_flap_cont_data[btsiteid="'+siteID+'"]');
		btmSiteSnap.find('.edit_links').parent().css({'height':$('.edit_site_links').parent().height()+'px'});
		$(this).hide();
	});

	$('.save_note').live('click',function(){
		var site_notes = $(this).parent().parent().find('.edit_site_notes').val();
		var tempArray={};
		var params={};
		params['siteID'] = $(this).closest('.site_flap_cont_data').attr('btsiteid');
		params['notes'] = site_notes;
		tempArray['requiredData'] = {};
		tempArray['requiredData']['iwpUpdateNotes'] = params;
		doCall(ajaxCallPath,tempArray,"processUpdateNotes","json","none");
	});
	$('.save_links').live('click',function(){
		var site_links = $(this).parent().parent().find('.edit_site_links').val();
		var tempArray={};
		var params={};
		params['siteID'] = $(this).closest('.site_flap_cont_data').attr('btsiteid');
		params['links'] = site_links;
		tempArray['requiredData'] = {};
		tempArray['requiredData']['iwpUpdateLinks'] = params;
		doCall(ajaxCallPath,tempArray,"processUpdateLinks","json","none");
	});

	

	$("#enableHTTPS").live('click',function(){
		if($(this).hasClass('active')){
                    $(this).removeClass('active');
		}else{
                    $(this).addClass('active');
		}
        if($(this).hasClass('checked')){
            $(this).removeClass('checked');
        }else{
            $(this).addClass('checked');
        }                
	});

	$("#enableSSLVerify").live('click',function(){
		if($(this).hasClass('active')){
                    $(this).removeClass('active');
		}else{
                    $(this).addClass('active');
		}
        if($(this).hasClass('checked')){
            $(this).removeClass('checked');
        }else{
            $(this).addClass('checked');
        }                
	});
        
    $(".FTPConnectionType").live('click',function(){
        $(".FTPConnectionType").removeClass('active');
        if (!$(this).is('#stagingDomainServer,#stagingDefaultServer')) {
        	$("#stagingCustomServer").addClass('active');
        }
        $(this).addClass('active');
    });
    
	$(".emailSet").live('click',function(){
		var this_parent = $(this).closest(".ftp_details_wrapper");
        $(".emailSet", this_parent).removeClass('active');
        $(this).addClass('active');
    });
    
    $("#enableFTP, #enableFTPSSL, #enableSFTP").live('click',function() {
        var hostPort = $("#FTPPort").val();
        if($("#enableSFTP").hasClass('active')) {
            if(parseInt(hostPort)==21) {
                $("#FTPPort").val('22');
            }
            $('.ftp_form_key').show();
        } else {
            if(parseInt(hostPort)==22) {
                $("#FTPPort").val('21');
            }
            $('.ftp_form_key').hide();
        }
    });

	$(".app_update_radio_select").live('click', function(){
		$(".textForHideAppUpdate").hide();
		$(".app_update_radio_select").removeClass("active");
		$(this).addClass("active");
		if($(".ftpMethod").hasClass('active')){
			$(".app_update_cont .FTP_form").show();
			$(".FTPtexts").show();
			$("#pluginName").focus();
			$(".test_conn_cont").show();
		}
		else{
			$(".app_update_cont .change_conts").hide();
		}
		
		if($(".directMethod").hasClass('active')){
			$(".app_update_cont .FTP_form").hide();
			$(".direct_texts").show();
			$(".test_conn_cont").hide();
		}
			
	});
	$(".notif_btn").live('click',function(){
		if(!$(this).hasClass("active")){
			$("#updates_centre_cont").hide();
			$(".notification_centre_cont").show();
			$(this).addClass('active');
			var tempArray = {};
			tempArray['requiredData'] = {};
			tempArray['requiredData']['iwpUpdateNotifCount'] = 'clear_offer';				//for clearing offer notification
			doCall(ajaxCallPath,tempArray,"processUpdateNotifCount","json","none");
		}
		else{
			$(".notification_centre_cont").hide();
			$(this).removeClass('active');
		}
		return false;
	});
	
	$(".notif_data_list li a").live('click',function(e){
		e.preventDefault();
		$(this).closest("li").click();
		return false;
	});
	
	$(".notif_data_list li").live('click',function(e){
		if($(this).hasClass("unread")){
			$(this).removeClass("unread");
			$(this).addClass("read");
		}
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['iwpUpdateNotifCount'] = $(this).attr("notif_id");
		doCall(ajaxCallPath,tempArray,"processUpdateNotifCount","json","none");
		window.open($(this).find("a").attr("href"),'_blank');
		e.preventDefault();
		return false;
	});
	
	$(".tweet_this").live('click', function(){
		//we should update tweet_status_three_sites, tweet_status_update_all options
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['updateIwpTweetStatus'] = $(this).attr("type");
		doCall(ajaxCallPath,tempArray,"processIwpTweetStatus","json","none");
		
		window.open($(this).attr("tweet_url"), '_blank');
		$("#modalDiv").dialog("destroy");
		bottomToolBarShow();
		$("html").css({ overflow: 'auto' });
	});
	
	$(".twitter_dismiss").live('click', function(){
		//we should update tweet_status_three_sites, tweet_status_update_all options
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['updateIwpTweetStatus'] = $(this).attr("type");
		doCall(ajaxCallPath,tempArray,"","json","none");
		
		$("#modalDiv").dialog("destroy");
		bottomToolBarShow();
	});

    $("#user_email_acc").live("click", function(){
        if(currentUserAccessLevel == 'admin'){
            openSettingsPage('App');
            $('.settings_nav li').removeClass('active');
            var content = getSpecificSettingsContent('Account');
            $('.settings_main_content').html(content);
            $('.settings_nav li:contains("Account")').addClass('active');
            loadSettingsPage(settingsData,'Account');
        } else {
            openSettingsPage('Account');
        }
        return false;
    });

	$(".clearHistory").live("click",function(){
		var clearWhat = $(this).attr('what');
		removeLogConfirmationPopup(clearWhat);
	});

	$(".cancel_clear_log,.cancel_kill_task").live("click",function(){
		$("#modalDiv").dialog("close");
	});

	$(".confirm_clear_log").live("click",function(){
		var clearWhat = $(this).attr('what');
                if(clearWhat == 'settingClearLog'){
                    $("#modalDiv").dialog("close");
                    clearLogSchedule();
                    return false;
                }
		var userID = 0;
			var tempArray={};
			tempArray['requiredData'] = {};
			tempArray['requiredData']['clearHistoryTasks'] = {};
			tempArray['requiredData']['getHistoryPageHTML']=1;
			if(clearWhat == 'searchList'){
				var dates = $("#dateContainer").text();
				if($("#activityUsers").length)  userID = $("#activityUsers").find('option:selected').attr('id');
				var searchByUser = 1;
				if(!userID){searchByUser = 0;}
				var keywords = $("#activityKeywordFilter").find('option:selected').attr('types');
				var reqDataParams = {
										'dates':dates,
										'userID':userID,
										'getKeyword':keywords,
										'searchByUser':searchByUser
									};
				tempArray['requiredData']['clearHistoryTasks'] = reqDataParams;
				tempArray['requiredData']['getHistoryPageHTML'] = reqDataParams;
			}else if(clearWhat == 'singleAct'){
				var actionID = $(this).attr('actionid');
				tempArray['requiredData']['clearHistoryTasks']['actionID'] = actionID;
			}
			tempArray['requiredData']['clearHistoryTasks']['clearWhat'] = clearWhat;
			doCall(ajaxCallPath,tempArray,"loadHistoryPageContent");
		$("#modalDiv").dialog("close");
	});

	$(".confirm_kill_task").live('click',function() {
		var clearWhat = $(this).attr('what');
		var taskID = $(this).attr('taskID');
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['terminatePendingProcess']= {};
		tempArray['requiredData']['terminatePendingProcess'][clearWhat+'ID'] = taskID;
		if($(this).hasClass('multicall'))
			tempArray['requiredData']['terminatePendingProcess']['multiCall'] = 1;
		doCall(ajaxCallPath,tempArray,"");
		$("#modalDiv").dialog("close");
	});


	$("#clearLogSchedule").live('click',function() {
            if($("#clearLogSchedule").hasClass('active')) {
		clearLogSchedule();
            } else {
                removeLogConfirmationPopup("settingClearLog");
            }
	});
        
        var clearLogSchedule = function(){
            if($("#clearLogSchedule").hasClass('active'))
		{
			$("#cls_times,#cls_times .cls_time").addClass('disabled').removeClass('active');
		}
		else{
			$("#cls_times,#cls_times .cls_time").removeClass('disabled');
			$(".cls_time[older='90']").addClass('active');
		}
		makeSelection("#clearLogSchedule");
        }

	$(".cls_time").live("click",function(){
		if($(this).hasClass('active')){
			// $(this).removeClass('active');
		}else{
			$(".cls_time").removeClass('active');
			$(this).addClass('active');
		}
		return false;
	});

	$(".removeThisAct").live("click",function(){
		var actionID = $(this).attr('actionid');
		var clearWhat = 'singleAct';
		removeLogConfirmationPopup(clearWhat,actionID);
		return false;
	});

    $(".loginAuthType").live('click',function(){
        $(".loginAuthType").removeClass('active');
        $(this).addClass('active');
        
        var authType = $(this).attr('id');
        if(authType=="authNone") {
            $("#loingTypeContent").html("");
        } else if(authType=="authBasic") {
            //$("#loingTypeContent").html("Basic e-Mail Auth will sent the passcode or loginauth url to your registered mail id. Once you enter the passcode or click the link on your mail, then your app will be open");
            $("#loingTypeContent").html('<div style="line-height: 22px;margin-top: 10px;padding:0 10px" id="loingTypeContent">A link and a 6-digit passcode will be sent to your email. You can either click on the link to login instantly or paste in the passcode to login.</div>');
        }
    });

	$(".test_send_mail_smtp").live("click", function(){
		var valArray = {};
		valArray = validateEmailSettingsAndGetValue();
		if(typeof valArray != 'undefined' && valArray != false ){
			$(this).addClass('disabled');
			$(this).prepend('<div class="btn_loadingDiv left"></div>');
		var tempArray={};
		tempArray['requiredData']={};
			tempArray['requiredData']['saveTestSmtpSettings'] = {};
			tempArray['requiredData']['saveTestSmtpSettings'] = valArray;
		doCall(ajaxCallPath,tempArray,"processTestSendMail","json","none");
		}
		return false;
	});
	
	$("#useSmtp").live("click", function(){
		if($(this).hasClass("active")){
			$(this).removeClass("active");
			$(".email_settings .ftp_details_wrapper").hide();
			$(".email_settings .ftp_Username_details").hide();
			$(".test_send_mail_smtp").hide();
			$(".email_settings .ftp_details_wrapper.smtp_from_email").show();
			$(".email_settings .ftp_details_wrapper.smtp_from_name").show();
		}
		else{
			$(this).addClass("active");
			$(".email_settings .ftp_details_wrapper").show();
			$(".email_settings .ftp_Username_details").show();
			$(".test_send_mail_smtp").show();
		}
	});

	$(".show_password").live('mousedown', function(e){
		var passwordInp = $(this).next(".passwords").get(0);
		passwordInp.blur();
		passwordInp.type = 'text';
		$(this).text('Hide');
		e.preventDefault();
	}).live('mouseup mouseleave', function(e){
		$(this).text('Show');
		$(this).next(".passwords").get(0).type = 'password';
	});

	$(".acc_settings #email").live("focus", function(){
		$(this).next(".valid_error").hide();
	});
	
	$(".acc_settings .passwords").live("focus", function(){
		$(this).next(".valid_error").hide();
	});

	$('#testIWPCronBtn').live('click', function() {
        var data = {
            requiredData: {
                'Manage_IWP_Cron::test':1
            }
        };
       doCall('ajax.php', data, 'setIWPCronActivate');
    });

    var activateIWPCron =  function(){
        var data = {
            requiredData: {
                'Manage_IWP_Cron::register':1
            }
        };
       doCall('ajax.php' ,data, 'setIWPCronActivate');
    };

    var deactivateIWPCron = function(){
        var data = {
            requiredData: {
                'Manage_IWP_Cron::update':1
            }
        };
       doCall('ajax.php' ,data, 'setIWPCronActivate');
    };

    $('.iwp-cron-chkbox').live('click', function(){
        $(this).toggleClass('active');
        var isActive = $(this).hasClass('active');
        if(isActive){
            $('#testIWPCronBtn').show();
            activateIWPCron();
        }else{
            deactivateIWPCron();
            $('#testIWPCronBtn').hide();
        }
    });

    $('.closeUpdateChangeNotification.confirmAction').live('click', function(){
            openSettingsPage('Cron');
            var data = {
                requiredData: {
                    'Manage_IWP_Cron::hideCronInviteNotification':1
                }
            };
            doCall('ajax.php' , data);
            return false;
	});

	$('.closeUpdateChangeNotification.cancel').live('click', function(){
            var data = {
                requiredData: {
                    'Manage_IWP_Cron::hideCronInviteNotification':1
                }
            };
            doCall('ajax.php' , data);
            return false;
	});
	if(isShowBetaWelcome == true){
		var content='<div class="dialog_cont update_client_plugin"> <div class="th rep_sprite"> <div class="title droid700">Whats new?</div> </div> <div style="padding:20px;"><div style="text-align:center;line-height: 20px;"><div class="clear-both"></div> <div><span class="droid700">V2.15.0beta</span><ul><li>Full Support for Multisite Installations.</li><li>SSH support - You can use your SSH keys to backup your WordPress sites.</li><li>You can Encrypt your DB backups using the Phoenix backup method. </li><li>Server Side encryption for Amazon S3 backups is enabled for all three backup mechanisms.</li><li>Notifications for WooCommerce DB updates.</li><li>IWP client plugin will add a must-use plugin to WordPress sites.</li><li>Support for WPTC backups to include on IWP Client reports.</li></ul></div></div><div class="clear-both"></div><div style="text-align: center; padding-top: 10px;">&bull;</div><div class="" style="padding-top: 10px;    text-align: center; line-height: 22px;">Try out the new features and be sure to report any errors you face. <br>Thanks for being part of the beta program :)</div></div> <div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;"><div class="btn_action float-right"><a class="rep_sprite" id="closeBetaWelcome">Okay</a></div></div>';
		$("#modalDiv").dialog("destroy");
		$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:false,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	}
	$("#closeBetaWelcome").live("click", function(){
		$("#modalDiv").dialog("close");
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['closeBetaWelcome']=1;
		doCall(ajaxCallPath,tempArray);
		isShowBetaWelcome = 0;
	});
	if(isShowReSchedulePopup == true){
		var content='<div class="dialog_cont update_client_plugin"> <div class="th rep_sprite"> <div class="title droid700">IMPORTANT NOTICE</div> </div>  <div style="background-color: #EFDEDE;border-left: 2px solid #8A1010;padding: 10px; margin: 10px;line-height: 18px;">We have turned OFF your monthly backup schedule due to the recent bug which we have fixed now. Please reschedule your montly backups by selecting a date.Upon rescheduling, TURN ON your monthly backups. Sorry for the inconvenience caused.</div><div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;"><div class="btn_action float-right"><a class="rep_sprite" id="closeReSchedulePopup">Okay</a></div></div>';
		$("#modalDiv").dialog("destroy");
		$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:false,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	}
	$("#closeReSchedulePopup").live("click", function(){
		var tempArray={};
		tempArray['requiredData']={};
		tempArray['requiredData']['closeReSchedulePopup']=1;
		doCall(ajaxCallPath,tempArray,'processReSchedulePopup');
	});

	$('#view_login_detail').live("click", function(){
		$('.page_section_title').html('Login log');
		var tempArray = {};
		tempArray['requiredData']={};
		tempArray['requiredData']['getLogPageHTML']={};
		tempArray['requiredData']['getLogPageHTML']['ID'] = $(this).attr('loginid');
		doCall(ajaxCallPath,tempArray,'loadLogHistoryPageByID');
	});
	
	$(".rep_sprite_backup.completed").live("click", function(){
		var refClass = $(this).attr('refClass');
		manageInitialSetupUsageStatsLinks(refClass);
		$(".basic_options_details").hide();
		$(".basic_options_button").hide();
		$("."+$(this).attr('refClass')).show();
		$(this).addClass('current')
	});

	$(".basic_options_button").live("click", function(){
		$(".basic_options").removeClass('current');
		$(".basic_options_details").hide();
		$(".basic_options_button").hide();
		var refClass = $(this).attr('refClass');
		$("."+refClass).show();
		manageInitialSetupUsageStatsLinks(refClass)

		if (refClass === 'close_pop_up') {
			$("#modalDiv").dialog("close");
			$("#modalDiv").dialog("destroy");
			var tempArray = {};
			tempArray['requiredData'] = {} ;
			tempArray['requiredData']['updateInitialSetupCompletedStatus'] = 1;
			doCall(ajaxCallPath,tempArray);
		}
	});


	$("#initialSetupUsageStatsBtn").live("click", function(){
		var IPArray = {};
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['updateSecuritySettings'] = {};
		tempArray['requiredData']['initialSetupUpdateUsageStats'] = {} ;

		var userInputIP = $("#initialSetupIPRestriction").val();
		if (userInputIP) {IPArray[0] = userInputIP;}
		if ($("#initialSetupSendAnonymous").hasClass('active')) {tempArray['requiredData']['initialSetupUpdateUsageStats']['sendAnonymous'] = 1;} 
		if ($("#enableHTTPSInitialSetup").hasClass('active')) { tempArray['requiredData']['initialSetupUpdateUsageStats']['enableHTTPS'] = 1;}
		tempArray['requiredData']['updateSecuritySettings']['allowedLoginIPsCount'] = 1;
		tempArray['requiredData']['updateSecuritySettings']['allowedLoginIPs'] = IPArray;
		tempArray['requiredData']['getSettingsAll']=1;
		doCall(ajaxCallPath,tempArray,"processSettingsUpdate","json","none");
	});

	$("#initialSetupIPRestrictionCheckBox").live("click", function(){
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$("#initialSetupIPRestriction").val('');
		} else {
			$(this).addClass('active');
			$("#initialSetupIPRestriction").val(IP);
		}
	});

	if(isInnoDBConversionNeeded != null && isInnoDBConversionNeeded != false){
		var html = '<div class="innoDBConvertionNotification" style="border-left-width: 2px; border-left-style: solid; border-left-color: #AAAAAA; padding: 10px; background-color: #e7e9eb;margin-top:20px;">We need to upgrade the database engine to InnoDB from MyISAM to optimise performance. <a id="confirmConvertInnoDB">Upgrade Now</a></div>';
		$("#panelNotifyHtml").prepend(html);
	}
	$("#confirmConvertInnoDB").live('click', function(){
		var IPArray = {};
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['getConversionNeededTableNames'] = 1 ;
		doCall(ajaxCallPath,tempArray,"showConversionNeededTableNames");
	});
	
	$("#initialSetupIPRestrictionCheckBox").live("click", function(){
		if ($(this).hasClass('active')) {
			$(this).removeClass('active');
			$("#initialSetupIPRestriction").val('');
		} else {
			$(this).addClass('active');
			$("#initialSetupIPRestriction").val(IP);
		}
	});


	$("#initialSetupSendAnonymous").live("click", function(){
		makeSelection(this);
		if ($(this).hasClass('active')) {
			$('#initialSetupThankYouMsg').show();
		} else{
			$('#initialSetupThankYouMsg').hide();
		}
	});

	$("#enableHTTPSInitialSetup").live("click", function(){
		makeSelection(this);
	});

	$(".dismiss_notification").live("click", function(){
		if (typeof mainJson.updatePageEmailCronReqNotification != 'undefined') {
			delete mainJson.updatePageEmailCronReqNotification;
		}
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['updateEmailCronReqNotification']=1;
		$(".setCronNotification").fadeOut("slow");
		doCall(ajaxCallPath,tempArray);
	});

	$("#goCronSettings").live('click',function() {
		$('#settings_btn').click();
		$('.settings_nav li:contains("Cron")').click();
	});
$('.vulns_site').live("click", function(){
		siteID = $(this).attr('siteid');
		$('.ind_row_cont[siteid='+siteID+']').find('.row_summary').click();
	});

	$(".single_website_cont").live('click',function() {
		$(".single_website_cont").removeClass('active');
		$(this).addClass('active');
		$("#totalBtnAction").closest("div").removeClass('disabled');
		if($(this).closest('.inner_cont').hasClass('useSiteID'))
		{
			$("#sourceID").remove();
			$("#cloneFromSites").prepend("<input type='hidden' id='sourceID' class='formVal' value='"+$(this).attr('sid')+"'>");
		}
		if($(this).closest('.inner_cont').hasClass('useSourceSiteID'))
		{
			$("#sourceSiteID").remove();
			$("#cloneFromSites").prepend("<input type='hidden' id='sourceSiteID' class='formVal' value='"+$(this).attr('sid')+"'>");
		}
		if ($(".cloneOptionTab.existingSite").hasClass('active')) {
			var siteID = $(this).attr('sid');
			var tempArray = {};
			tempArray['requiredData'] = {};
			tempArray['requiredData']['getSiteFtpDetailsClone'] = {};
			tempArray['requiredData']['getSiteFtpDetailsClone']['siteID'] = siteID;
			doCall(ajaxCallPath,tempArray,"populateProfileDropDown_IC_Common","json","none");
		}
	});
	
	$("#openAutoFillCpanel").live("click", function(){
		$("#clonePanel .cpanError").html('');
		var type = 'installClone';
		if($(this).hasClass("staging")){
			type = 'staging';
		}
		var content = "";
		content = content + '<div class="dialog_cont" id="clonePanel"><div class="steps_container "> <div class="th rep_sprite"> <div class="title droid700">Auto-fill via cPanel</div><a class="cancel rep_sprite_backup">cancel</a> </div>  <div id=""> <div id="" class="subtabData"> <div class="inner_cont cpanelAutofill"> <div style="color: #435358; font-weight: 700; font-size: 12px; text-transform: uppercase; margin-bottom: 40px; text-align:center;">ENTER YOUR CPANEL DETAILS</div> <input type="text" placeholder="Link to your cPanel" class="af_validate" id="cpanelURL_IC" name=""> <div style="text-align: right; margin-top: -20px; margin-bottom: 5px;">To avoid errors, copy &amp; paste from your browser</div> <input name="" type="text" class="af_validate" id="cpanelUserName_IC" placeholder="Enter your cpanel username"> <a class="show_password" style="position: absolute;right: 55px;top: 233px;">Show</a> <input name="" type="text" class="af_validate passwords" id="cpanelPassword_IC" placeholder="Enter your cpanel account password""><div style="margin-bottom: 20px;line-height: 22px;">The MySQL Database and username will be created with full rights. Please note that every time you click on the "Fill Details" button below, a new db and user will be created.</div><div style="margin:0px; line-height: 20px;padding: 5px 10px;text-align:left;display:none" class="errorMsg"></div></div> </div> </div>  <div class="th_sub th_sub_btm rep_sprite" style=""> <div id="fillDetails" class="btn_next_step rep_sprite float-right '+type+'"><a >Fill Details<div class="taper"></div></a></div>  </div> </div></div>';
		$("#modalDiv").dialog("close");
		$("#modalDiv").dialog("destroy");
		$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); forceBackup=0; $("#modalDiv").html(''); }});
	});

	$("#browseFiles_IC_staging").live('click',function() {
		$('.browseFileError').hide();
			var currentForm = $(this).parents('form');
			currentForm.find('#fileTreeContainer').toggle();
	
			if(currentForm.find('#fileTreeContainer').is(":visible")) {
				$(".fileTreeVal").removeClass("error");
				loadFileTreeCommon(currentForm, currentForm.find('#fileTreeContainer'));
				$("#placedBackup").show();
				$("#fileTreeContainer").addClass("addScroll");
			}
	});
	
	$(document).mouseup(function (e) {
		var container = $(".fileTreeClass");
	
		if (!container.is(e.target) // if the target of the click isn't the container...
			&& container.has(e.target).length === 0) // ... nor a descendant of the container
		{
			container.hide();
		}
	});
	
	$(".fileTreeSelector").live('click', function(){
		var thisFileName = $(this).attr("filename");
		var thisFolderName = $(this).attr("rel");
		var thisType = $(this).attr("type");
		if((thisType != "file")&& !($(".placedBackupAraea").is(":visible"))) {
				$(this).parents('form').find('#remoteFolder').attr("value", thisFolderName+"/");
				$(this).parents('form').find("#fileTreeContainer").hide();
		} else if($(".placedBackupAraea").is(":visible") && thisType=="file") {
			var thisFileArray = thisFileName.split(".");
		
			var arrLength = (thisFileArray.length - 1);
			var thisFileType = thisFileArray[arrLength];
	
			if(thisFileType == 'zip' || thisFileType == 'tmp'){
					$(".placedBackupAraea").find("#placedBackup").attr("value", thisFileName);
					$("#manualBackupFile").remove();
					$("#backupURL").val("");
					$("#cloneFromSites").prepend("<input type='hidden' id='manualBackupFile' class='formVal' value='"+$("#placedBackup").val()+"'>");
					thisFolderName = thisFolderName.replace(/\\/g,'/').replace(/\/[^\/]*$/, '');
					if(thisFolderName=="") {thisFolderName="/";} else {thisFolderName += "/";}
					$("#remoteFolder").attr("value", thisFolderName);
					$("#fileTreeContainer").hide();
					$(".PlaceAreaRelated").show();
			}
		}
	});
	
	$("#use_ftp, #hostSSL, #use_sftp").live('click',function(){
		var hostPort = $("#hostPort").val();
		if($("#use_sftp").hasClass('active')) {
			if ($(".phoenix_backup").hasClass('active')) {
				$('.phoenix_key').show();
			}
			if(parseInt(hostPort)==21) {
				$("#hostPort").val('22');
			}
			$('.ftp_form_key').show();
		} else {
			$('.phoenix_key').hide();
			if(parseInt(hostPort)==22) {
				$("#hostPort").val('21');
			}
			$('.ftp_form_key').hide();
		}

	});
	
	$(".e_close").live('click',function(){
		$(this).closest('.conn_test_error_cont').remove();
		$("#cloneTestConnection").removeClass("error");
	});
	
	$(".af_validate").live('click', function(){
		if($(this).hasClass("error")){
			$(this).removeClass("error");
		}
	});
	
	$(".input_radio").live("click", function(){
		$(".input_radio").removeClass("active");
		$(this).addClass("active");
	})
$(".active_all").live('click',function() {
		pluginsThemesSelection('active_all',$(this).attr('selector'),this);
		applyChangesCheck();
	});
	$(".deactivate_all").live('click',function() {
		pluginsThemesSelection('deactivate_all',$(this).attr('selector'),this);
		applyChangesCheck();
	});
	$(".delete_all").live('click',function() {
		pluginsThemesSelection('delete_all',$(this).attr('selector'),this);
		applyChangesCheck();
	});

$(".multiple_downloads").live('click', function(){
	var dataDownloads = $(this).attr('data-downloads');
	var splittedBackupData = $.parseJSON(dataDownloads);
	if(splittedBackupData!='undefined'){
		var downloadRowsContent ='';    
		var part = 1;
		$.each( splittedBackupData.downloadURL, function( index, value ){
			downloadRowsContent = downloadRowsContent + '<div style="padding: 5px;"><a class="part_download" href="'+value+'">Download Part - '+part+'</a></div>';
			part++;
		});
		var content = '<div class="dialog_cont" style="width: 500px;background: #F6F6F6"><div class="th rep_sprite"><div class="title droid700">DOWNLOAD BACKUP PART FILES</div><a class="cancel rep_sprite_maintenance">cancel</a></div><div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">The Backup of the site splitted into more files you can download the part files here</div></div><div style="padding:20px;"><div style="text-align:center; line-height: 22px;"><div class="" style="background: rgb(255, 255, 255) none repeat scroll 0% 0%; margin-left: 34%; width: 28%; box-shadow: 0px 1px 3px 1px rgb(203, 196, 196); padding: 10px; height: 193px; overflow-y: auto;">'+downloadRowsContent+'</div></div></div><div style="height: 37px; padding: 0px 20px 3px;"><div class="btn_action" style="cursor: pointer; height: 100%; margin-left: 35.5%;"><a data-downloads="" class="rep_sprite btn_blue downloadAllPartFiles confirmAction" style="color: #6C7277;  cursor:pointer;">Download All files</a></div></div><div style="margin-left: 42%; padding: 1px 13px 13px; height: 20px;"><div class="btn_action" style="position: relative;">Size '+splittedBackupData.size+'</div></div><div class="clear-both"></div></div>'
		$("#modalDiv").dialog("close");
		$("#modalDiv").dialog("destroy");
		$('#modalDiv').html(content).dialog({
			width:'auto',
			modal:true,
			position: 'center',
			resizable: false, 
			open: function(event, ui) {
			},
			close: function(event, ui) {
				$("#modalDiv").dialog("destroy");
			}
		});
	}
});

$('.downloadAllPartFiles.confirmAction').live('click', function(){
	var timeOutForDownload = 0;
	$('.part_download').each(function(i, obj) {
		setTimeout(function(){obj.click()},timeOutForDownload);
		timeOutForDownload=timeOutForDownload+5000;
	});
});

});
$('.groupReloadStats').live('click', function(event) {
	if(totalSites<1){
		return false;
	}
	var tempArray={};
	tempArray['requiredData']={};
	if ($(this).hasClass('groupReloadStats')) {
		tempArray['requiredData']['getSitesByGroupID']={}
		tempArray['requiredData']['getSitesByGroupID']['groupID']=$(this).attr('groupid');
		doCall(ajaxCallPath,tempArray,"reloadStatsByGroup","json","none");
	}
});

$('.group_reload').live('click', function(e) {
	if(!($('.group_reload .l1').hasClass('active')))
	{
		$('.group_reload .l1').addClass('active');
		$('.group_reload .l1 .caret-down').addClass('active');
		 e.stopPropagation();
		//showOrHide(this,'','active','');
	}
	else
	{
		$('.group_reload .l1').removeClass('active');
		$('.group_reload .l1 .caret-down').removeClass('active');
	}//
});
$(document).on("click", function(e) {
	if ($(e.target).is(".group_reload .l1") === false) {
		$(".group_reload .l1").removeClass("active");
		$('.group_reload .l1 .caret-down').removeClass('active');
	}
});

$(".emailFrequency").live('click', function(event) {
	if ($(this).attr('def')== 'never') {
		$("#notifyUpdates").addClass('disabled');
		$("#notifyVulnsUpdate").addClass('disabled');
		$("#notifyUpdates").css('opacity','.5');
		$("#notifyVulnsUpdate").css('opacity','.5');
	}else{
		$("#notifyUpdates").removeClass('disabled');
		$("#notifyVulnsUpdate").removeClass('disabled');
		$("#notifyVulnsUpdate").css('opacity','1');
		$("#notifyUpdates").css('opacity','1');
	}
});

$('.error_warning').live('click', function(event) {
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['getSitesErrorViewContent']=1;
	doCall(ajaxCallPath,tempArray,"loadSitesErrorViewContent");
});

$('.update_search').live('click', function(event) {
	if ($('.searchSiteUpdate').hasClass('siteSearch')) {
		initWebsitesView();
	} else if ($('.searchSiteUpdate').hasClass('pluginsSearch')) {
		initPluginsView();
	} else if ($('.searchSiteUpdate').hasClass('themesSearch')) {
		initThemesView();
	} else if ($('.searchSiteUpdate').hasClass('coreSearch')) {
		initWPView();
	} else if ($('.searchSiteUpdate').hasClass('hiddenSearch')) {
		initHiddenView();
	} else if ($('.searchSiteUpdate').hasClass('WPVulnsSearch')) {
		initSecurityUpdatesView();
	}
});

$('.connectionMethodRadio').live('click', function(){
    $('.connectionMethodRadio').removeClass('active');
    $(this).addClass('active');
    $('#manualContent').hide();
    $('#cmdRunnerContent').hide();
    $('#sockRunnerContent').hide();
});

$('#manual').live('click',function(){
    if($(this).hasClass('active')){
        $('#manualContent').show();
        if($('.connectionMode.active').length == 0){
            if(!$('#commandMode').hasClass('disabled')){
                $('#commandMode').addClass('active');
            }else if(!$('#socketMode').hasClass('disabled')){
                $('#socketMode').addClass('active');
            }else{
                $('#curlMode').addClass('active');
            }
        }
        $('.connectionMode.active').trigger('click');
    }
});

$('.connectionModeRadio').live('click', function(){
    $('.connectionModeRadio').removeClass('active');
    $(this).addClass('active');
    
    if($(this).attr('id')=='commandMode'){
        if($('#cmdRunner .active').length == 0){
            $('#cmdRunnerAuto').addClass('active');
        }
        $('#sockRunnerContent').hide();
        $('#cmdRunnerContent').show();
    }else if($(this).attr('id')=='socketMode'){
        if($('#sockRunner .active').length == 0){
            $('#sockRunnerAuto').addClass('active');
        }
        $('#cmdRunnerContent').hide();
        $('#sockRunnerContent').show();
    }else if($(this).attr('id')=='curlMode'){
        $('#cmdRunnerContent').hide();
        $('#sockRunnerContent').hide();
    }
});


$('.connectionRunnerRadio').live('click', function(){
    $('.connectionRunnerRadio').removeClass('active');
    $(this).addClass('active');
});


$('#restoreNewBackup').live('click', function(){
    	var tempArray={};
    	var types = [];
		tempArray['args']={};
		tempArray['args']['params']={};
		$("#modalDiv").dialog('close');
		if($(this).hasClass("isCloudBackup")){
			tempArray['action']='restoreBackupDownlaod';
			tempArray['args']['params']['isCloudBackup']= 1;
			if ($('#restoreSiteDown').hasClass('active')) {
				tempArray['args']['params']['isSiteDown']= 1;
			}
		}else if($('#restoreSiteDown').hasClass('active')) {
			tempArray['action']='restoreNewBackup';
			tempArray['args']['params']['isSiteDown']= 1;
		}else {
			tempArray['action']='restoreBridgeUpload';
		}
		if($('#restore_plugins').hasClass('active')){
			types.push('plugins');
		}
		if($('#restore_themes').hasClass('active')){
			types.push('themes');
		}
		if($('#restore_uploads').hasClass('active')){
			types.push('uploads');
		}
		if($('#restore_others').hasClass('active')){
			types.push('others');
		}
		if($('#restore_db').hasClass('active')){
			types.push('db');
		}
		if($('#restore_more').hasClass('active')){
			types.push('more');
		}
		if($('#restore_WP').hasClass('active')){
			types.push('wp');
		}
		tempArray['args']['params']['taskName']=$(this).attr('taskname');
		tempArray['args']['params']['types_to_downlaod']=types;
		tempArray['args']['params']['isNewBackup']= 1;
		tempArray['args']['params']['backupFileBasename']= $(this).attr('backupfilebasename');
		tempArray['args']['params']['resultID']=$(this).attr('referencekey');
		tempArray['args']['siteIDs']=[$(this).attr('sid')];
		tempArray['args']['siteIDs']=[$(this).attr('sid')];
		// $(this).addClass('disabled');
		// $(this).text('Queued..');
		doHistoryCall(ajaxCallPath,tempArray,"");
});

$('.retry_failed_task').live('click', function() {
		retryTaskConfirmationPopup($(this).attr('historyID'));
});

$('.confirm_retry_this_task').live('click', function() {
		$("#modalDiv").dialog('close');
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['manualRetryFailedTask'] = $(this).attr('historyID');
		doCall(ajaxCallPath,tempArray,"");
});

$('#restoreNewChildSiteBackup').live('click', function(){
    	var tempArray={};
    	var types = [];
		tempArray['args']={};
		tempArray['args']['params']={};
		$("#modalDiv").dialog('close');
		if($(this).hasClass("isCloudBackup")){
			tempArray['action']='restoreBackupDownlaod';
			tempArray['args']['params']['isCloudBackup']= 1;
			if ($('#restoreSiteDown').hasClass('active')) {
				tempArray['args']['params']['isSiteDown']= 1;
			}
		}else if($('#restoreSiteDown').hasClass('active')) {
			tempArray['action']='restoreNewBackup';
			tempArray['args']['params']['isSiteDown']= 1;
		}else {
			tempArray['action']='restoreBridgeUpload';
		}
		
		if($('#restore_uploads').hasClass('active')){
			types.push('uploads');
		}

		if($('#restore_db').hasClass('active')){
			types.push('db');
		}
	
		tempArray['args']['params']['taskName']=$(this).attr('taskname');
		tempArray['args']['params']['types_to_downlaod']=types;
		tempArray['args']['params']['isNewBackup']= 1;
		tempArray['args']['params']['childRestore']= 1;
		tempArray['args']['params']['parentSiteID']= $(this).attr('parentsiteid');
		tempArray['args']['params']['blogid']= $(this).attr('blogid');
		tempArray['args']['params']['backupFileBasename']= $(this).attr('backupfilebasename');
		tempArray['args']['params']['resultID']=$(this).attr('referencekey');
		tempArray['args']['siteIDs']=[$(this).attr('sid')];
		tempArray['args']['siteIDs']=[$(this).attr('sid')];
		// $(this).addClass('disabled');
		// $(this).text('Queued..');
		doHistoryCall(ajaxCallPath,tempArray,"");
});

$('.parent_site_checkbox').live('click', function(){
	if ($(this).hasClass('active')) {
		$(this).removeClass('active');
	}else{
		$(this).addClass('active');
	}
});

$('#addAllNetwork').live('click', function(){
	$("#modalDiv").dialog('close');
	var  tempArray={};
	var selectedSites = doReturnSelectedMultiSites();
	tempArray['args']={};
	tempArray['args']['params']={};
	tempArray['requiredData']={};
	tempArray['requiredData']['addBulkNetworkSite']={};
	tempArray['requiredData']['addBulkNetworkSite']['parentSiteID'] = $(this).attr('parentsiteID');
	tempArray['requiredData']['addBulkNetworkSite']['selectedSites'] = doReturnSelectedMultiSites();
	tempArray['requiredData']['getSitesUpdates']=1;
	tempArray['requiredData']['getClientUpdateAvailableSiteIDs']=1;
	tempArray['requiredData']['getGroupsSites']=1;
	tempArray['requiredData']['getRecentPluginsStatus']=1;
	tempArray['requiredData']['getRecentThemesStatus']=1;
	tempArray['requiredData']['getSites']=1;
	tempArray['requiredData']['getSitesList']=1;
	
	tempArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
	tempArray['requiredData']['getAddonPlanSiteLimit']=1;
	tempArray['requiredData']['getAddonSuitePlanActivity']=1;
	doCall(ajaxCallPath,tempArray,"processAddNetworkSite","json","none");
});
$("#showWooDbUpdates").live('click', function(){
		var tempArray = {};
		tempArray['requiredData'] = {};
		tempArray['requiredData']['getWooDBUpdateSites'] = 1 ;
		doCall(ajaxCallPath,tempArray,"showWooDBUpdateSites");
});
$('#closePopupNotification').live('click', function() {
	var tempArray = {};
	tempArray['requiredData'] = {};
	tempArray['requiredData']['closePopupNotification'] = {};
	tempArray['requiredData']['closePopupNotification']['ID'] = $(this).attr('notifyID');
	$("#modalDiv").dialog('close');
	doCall(ajaxCallPath,tempArray,"");
});