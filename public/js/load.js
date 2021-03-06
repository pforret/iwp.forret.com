/************************************************************
* InfiniteWP Admin panel									*
* Copyright (c) 2012 Revmakx								*
* www.revmakx.com											*
*															*
************************************************************/

// Loading contents
var manage,adminSiteLink,adminSiteID;
function loadItemManage()
{
	var content='<div class="site_nav_sub"> <ul> <li><a class="manage optionSelect">MANAGE</a></li> <li><a class="install optionSelect">INSTALL</a></li> <div class="clear-both"></div> </ul> </div> <ul class="btn_rounded_gloss"> <li><a class="rep_sprite active typeSelector optionSelect typePlugin" utype="plugins" onclick="">Plugins</a></li> <li><a class="rep_sprite typeSelector optionSelect typeThemes" utype="themes" onclick="">Themes</a></li> </ul><div class="steps_hdr">Select websites to <span id="processType">Manage</span> <span class="itemUpper">Plugins</span></div><div class="siteSelectorContainer">'+siteSelectorVar+'</div><div class="advancedInstallOptions" style="display:none"><div class="steps_hdr">INSTALLATION OPTIONS</div> <div style="width:225px; padding: 0px 0px 10px 0px;"> <div class="checkbox generalSelect activateItem" onclick="">Activate  <span class="itemNameLower">'+activeItem+'</span> after install</div> <div class="checkbox generalSelect overwriteItem" onclick="">Overwrite, if <span class="itemNameLower">'+activeItem+'</span> already exist.</div> </div></div> <div class="result_block shadow_stroke_box siteSearch itemPanel" style="border:0;box-shadow: 0 0 0  rgba(0, 0, 0, 0)"><div class="optionsContent "></div>';
	$("#pageContent").html(content);
	$(".manage").click();
	siteSelectorNanoReset();
}

function loadFavourites()
{
	pluginsThemesFavoritesSelector();
	if(activeItem == 'plugins'){
		if (jQuery.isEmptyObject(favourites.plugins)) {
			emptyFavorites = '<div class="empty_data_set"><div class="line1">You have no favorites :(</div><div class="line2">You can add favorites by <a id="searchRepository">searching through the WP Repository.</a></div></div>';
			$(".installSubPanel").html(emptyFavorites);
		} else {
			$(".installSubPanel").html(favoritesGroupsContent);
		}
	} else if (activeItem == 'themes') {
		if (jQuery.isEmptyObject(favourites.themes)) {
			emptyFavorites = '<div class="empty_data_set"><div class="line1">You have no favorites :(</div><div class="line2">You can add favorites by <a id="searchRepository">searching through the WP Repository.</a></div></div>';
			$(".installSubPanel").html(emptyFavorites);
		} else {
			$(".installSubPanel").html(favoritesGroupsContent);
		}
	}

	triggerNanoScrollerFavoritesGroup();
	if (activeItem.toTitleCase() == 'Themes'){
		$('.ind_site.favItems.searchable').find('a[utype=plugins]').parent().remove();
		$('.favGroup').find('a[type=plugins]').parent().remove();
	} else {
		$('.ind_site.favItems.searchable').find('a[utype=themes]').parent().remove();
		$('.favGroup').find('a[type=themes]').parent().remove();
	}
	$(".installFavourites").addClass('disabled');
	var groupForActiveItem = 0;
	$.each(favouritesGroupData, function( key, value ) {
		if(activeItem === value.type){
			groupForActiveItem = 1;
		}
	});
	if(!jQuery.isEmptyObject(favouritesGroupData) && groupForActiveItem === 1){
		$(".favouritesGroup").css('height','160px');
		$(".installSubPanel").find('.bywebsites').css('width','709px');
		$(".installSubPanel").find('.website_items_cont').css('width','710px');
		$(".favouritesItems").find('.fav_rows_cont .ind_site').attr('style', 'width: 235px !important');
	} else {
		$(".favouritesGroup").css('height','0px');
		$(".installSubPanel").find('.bywebsites').css('width','960px');
		$(".installSubPanel").find('.website_items_cont').css('width','960px');
		$(".favouritesItems").find('.fav_rows_cont .ind_site').attr('style', 'width: 239px !important');
	}
}

function loadManagePanel(data)
{
	if($(".fetchInstall").hasClass('disabled'))
	$(".fetchInstall").text($(".fetchInstall").attr('tempname')).removeClass('disabled');
	$(".btn_loadingDiv").remove();
	manage=data;
	var content='';
	content=' <div class="th rep_sprite"> <div class="title"><span class="droid700">Show </span></div> <ul class="btn_radio_slelect float-left"> <li><a class="active rep_sprite manage_'+activeItem+'_view itemName optionSelect">'+activeItem.toTitleCase()+'</a></li> <li><a class="rep_sprite manage_websites_view optionSelect">Websites</a></li> </ul> <div class="type_filter "> <input name="" id="manageFilter" type="text" class="input_type_filter searchItems manageFilter " placeholder="type to filter" /> <div class="clear_input rep_sprite_backup" onclick=""></div></div> <div class="btn_action float-right"><a class="rep_sprite status_applyChangesCheck js_changes disabled">Apply Changes</a></div> </div> <div id="view_content" ></div>';
	$(".actionContent").html(content).show();
	pluginsListPanel(activeItem,activeItem);
	text=$(".fetchInstallTxt").val();
	if(text!='')
	$("#manageFilter.input_type_filter").val(text).keyup();
	if(activeItem=="themes")
	$(".manage_themes_view").click();
	
	
}
function loadInstallPanel(loadClass)
{
	var content = '';
	activeItemHere = activeItem.substring(0, activeItem.length - 1);
	content = '<div class="th rep_sprite siteSearch"> <div class="title"><span class="droid700">Install from</span></div> <ul class="btn_radio_slelect float-left"> <li><a class="rep_sprite active installOptions optionSelect" function="loadFavourites">My Favorites</a></li> <li><a class="rep_sprite installOptions optionSelect" function="loadRepository">WP Repository</a></li> <li><a class="rep_sprite installOptions optionSelect" function="loadComputer">My Computer</a></li> <li><a class="rep_sprite installOptions optionSelect" function="loadURL">URL</a></li> </ul> <div class="type_filter"> <input name="" type="text" class="input_type_filter favSearch favOption float-left" placeholder="type to filter" /><div class="clear_input rep_sprite_backup" style="left:134px" onclick=""></div><div class="add_to_fav_link float-left rep_sprite_backup" style="position:relative; cursor:pointer" id="addToFavouritesCustom" style="margin-left: -5px; cursor:pointer">Add to My favorites</div><div class="add_to_fav_link favorites_group_btn float-left disabled" id="createFavoriteGroup" style="cursor:pointer">Create Group</div>  <div class="delete_user_post_ressign_tip" style="margin-left: 305px;margin-top: 34px; display:none">Select atleast one '+activeItemHere+' to create a group</div> </div> <div class="btn_action float-right"><a class="rep_sprite status_favItems installFavourites favOption itemName disabled" style="margin-top: -33px;">Install '+activeItem.toTitleCase()+'</a></div> </div><div class="content"><span class="installSubPanel"> </span><div class="clear-both"></div></div>';
	$("."+loadClass).html(content);
	loadFavourites();
}

function loadUpdateAllPanel(updateData){
	if(typeof updateData != 'undefined' && typeof updateData.actionResult != 'undefined' &&  updateData.actionResult.status != 'undefined' && updateData.actionResult.status == 'success' && updateAllTweetStatus != 'done'){
		showTweetDialog("update_all");				//updateAllTweetStatus is a global var; for one time show-dialog box
	}
}
function loadRepository()
{
	var itemCont='';
	if(activeItem=='plugins')
	var helpTxt="eg. seo, google";
	else
	var helpTxt="eg. black, two columns";
	var content='<div class="th_sub rep_sprite"> <ul class="th_sub_nav"> <li><a class="rep_sprite active searchVar optionSelect" dval="search">SEARCH</a></li> <li><a class="rep_sprite searchVar optionSelect" dval="featured">FEATURED</a></li> <li><a class="rep_sprite searchVar optionSelect" dval="popular">POPULAR</a></li> <li><a class="rep_sprite searchVar optionSelect" dval="new">NEWEST</a></li> <li><a class="rep_sprite searchVar optionSelect" dval="updated">RECENTLY UPDATED</a></li> </ul> </div> <div class="rows_cont"> <div class="content searchCont"> <div class="form_cont float-left" style="padding:0; border:0;width: 500px;"> <div class="tr"> <div class="tl two_liner" style="width: 19%;">Search <span class="itemName">'+activeItem.toTitleCase()+'</span><br /> by Keyword</div> <div class="td"> <input name="" type="text" class="searchText onEnter" onenterbtn=".searchItem" placeholder="'+helpTxt+'" style="color:#AAAAAA" /> </div> <div class="clear-both"></div> </div> </div> <div class="btn_action float-left" style="margin: 7px 0 0 -54px;"><a class="rep_sprite searchItem">Search <span class="itemName">'+activeItem.toTitleCase()+'</span></a></div> <div class="clear-both"></div> </div> <div class="wp_repository_cont"></div> </div>';

	return content;
}
function pluginsListPanel(view,type)
{
	$(".status_applyChangesCheck").addClass('disabled');
	var content='',statusVar,firstKey,actionVar,actionLi,siteID,dID,contName,delCont,extraDeactivateClass,styleSheetTmp,dLink,noContentText,inactiveFlag,activeFlag,deleteFlag;
	inactiveFlag = 0;activeFlag = 0;deleteFlag = 0;
	if(view=='sites')
	var json=manage.data.getSearchedPluginsThemes.siteView;
	else
	var json=manage.data.getSearchedPluginsThemes.typeView;
	if(json!=null &&  json!=undefined && getPropertyCount(json)>0)
	{
		$.each(json, function(i, object) {
			if(view!='sites')
			firstKey=getFirstKey(object,2);
			else
			firstKey=site[i];
			var versionCollapsedView = '';
			var versionExpandedView = '';
			var allDeactivate = '';
			var allActivate = '';
			var allDelete ='';
			var lableName = '';
			content=content+'<div class="ind_row_cont "> <div class="row_summary" > <div class="row_arrow"></div>  <div class="row_name searchable">'+firstKey.name+'</div> <div class="clear-both"></div> </div>';
			content=content+'<div class="row_detailed" style="display:none"> <div class="rh"> <div class="row_arrow"></div><div class="row_name">'+firstKey.name+'</div> <div class="clear-both"></div> </div><div class="rd">';
			$.each(object, function(status, value) {
				
				if(status=='notInstalled')
				{
					statusVar='Not Installed';
					actionLi='<li><a class="rep_sprite">Install</a><a class="rep_sprite">Install & Activate</a></li> ';
				}
				else if(status=='active')
				{
					statusVar='Active';
					actionVar='Deactivate';
					allDeactivate = 'inactive_'+inactiveFlag;
					inactiveFlag++;
					allActivate = ';'

				}
				else if(status=='inactive')
				{
					statusVar='Inactive';
					actionVar='Activate';
					actionLi='<li><a class="rep_sprite">Activate</a></li><li><a class="rep_sprite">Delete</a></li> ';
					allDeactivate = '';
					allActivate = 'active_'+activeFlag;
					allDelete = 'delete_'+deleteFlag;
					activeFlag++;deleteFlag++;
				}
				content=content+'<div class="row_updatee"> <div class="row_updatee_ind"> <div class="label_updatee  float-left"> <div class="label droid700 float-left"><span class="'+status+'">'+statusVar+'</span></div> <div class="count float-left"><span>'+getPropertyCount(value)+'</span></div> <div class="clear-both"></div> </div><div class="items_cont float-left">';
				if(getPropertyCount(value) > 1){
					if (status == 'inactive' && activeItem == 'themes' && view == 'sites') {
						content =content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">THEMES</span> <div class="select_cont_plugin float-right" style="margin-left: 54px !important;"><span>Select All: </span><a class="delete_all"  selector="'+allDelete+'">Delete</a></div></div>';
					}else if (status == 'inactive') {
						if (view == 'sites') {
							lableName = 'PLUGINS';
						} else{
							lableName = 'WEBSITES';
						}
						content =content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">'+lableName+'</span> <div class="select_cont_plugin float-right"><span>Select All: </span><a class="active_all" selector="'+allActivate+'">Activate</a><a class="delete_all"  selector="'+allDelete+'">Delete</a></div></div>';
					}else if(status =='active' && activeItem!="themes"){
						content =content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">PLUGINS</span> <div class="select_cont_plugin float-right"><span>Select All: </span><a class="deactivate_all"  selector="'+allDeactivate+'">Deactivate</a></div></div>';
					}else if (status == 'active' && activeItem == 'themes') {
						content = content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">WEBSITES</span> <div class="select_cont_plugin float-right" style="margin-top:10px;"></div></div>';
					}
				}else{
					if(activeItem == "themes" && view == 'sites'){
						content = content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">THEME </span> <div class="select_cont_plugin float-right" style="margin-top:10px;"></div></div>';
					}else if(activeItem != "themes" && view == 'sites'){
						content = content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">PLUGIN </span> <div class="select_cont_plugin float-right" style="margin-top:10px;"></div></div>';						
					}else{
						content = content+'<div class="item_ind float-left"> <span style="margin: 12px;font-weight: 700;position: absolute;">VERSION</span><span style="margin: 12px 0px 0px 100px;position: absolute;font-weight: 700;">WEBSITE</span> <div class="select_cont_plugin float-right" style="margin-top:10px;"></div></div>';
					}
				}
				$.each(value, function(id, array) {
					if(view=='sites')
					{
						siteID=i;
						dID=id;
						contName=array.name;
					}
					else
					{
						siteID=id;
						dID=i;
						
						contName=site[id].name;
					}
					if(status!='notInstalled')
					{
						
						if(status=='inactive')
						delCont='<li><a class="rep_sprite actionButton applyChangesCheck optionSelectOne  '+allDelete+'" sid="'+siteID+'" did="'+dID+'"  itemName="'+array.name+'" utype="'+type+'" action="delete">Delete</a></li>';
						else
						delCont='';
						if(status=='active')
						extraDeactivateClass='actionButtonRounded';
						else
						extraDeactivateClass='';
						
						if(activeItem=="themes" && status=="active")
						actionLi='';
						else
						{
							if(activeItem=="themes")
							styleSheetTmp="stylesheet="+array.stylesheet;
							else
							styleSheetTmp='';
							actionLi='<ul class="btn_radio_slelect small float-left"><li><a class="rep_sprite actionButton applyChangesCheck '+extraDeactivateClass+' optionSelectOne site'+siteID+' '+allDeactivate+' '+allActivate+'" itemName="'+array.name+'" '+styleSheetTmp+' sid="'+siteID+'" did="'+dID+'" utype="'+type+'" action='+actionVar.toLowerCase()+'>'+actionVar+'</a></li>'+delCont+'</ul>';
						}				
					}
					else
					{
						if(activeItem=="themes")
						dLink='http://wordpress.org/extend/themes/download/'+array.slug+'.zip';
						else
						dLink='http://downloads.wordpress.org/plugin/'+array.slug+'.zip';
						actionLi='<a class="installSinglePlugin installNotInstalled " style="padding: 11px;float: left;width: 122px;text-align: right;" action="install"  sid="'+siteID+'" did="'+dID+'" dLink="'+dLink+'"  itemName="'+array.name+'" plugin_theme_slug="'+array.slug+'" utype="'+type+'" >Install</a> ';
					}
					var versionSitesView = '';
					
					versionSitesView = '<li style="font-weight: 700;">v'+array.version+' </li>';
				
					content=content+' <div class="item_ind float-left"> <div class="item float-left"><ul>'+versionSitesView+'<li style="margin-left: 86px;margin-top:-14px;">'+contName+'</li></ul></div> <div class="select_operation"> '+actionLi+' </div></div>  ';
					

				});
				content=content+' </div><div class="clear-both"></div></div> </div>';
				
			});
			
			content=content+'</div></div></div>';
		});
	}
	if(view=="sites")
	noContentText='websites';
	else
	noContentText = activeItem;
	content='<div class="no_match hiddenCont" style="display:none">Bummer, there are no '+noContentText+' that match.<br />Try typing fewer characters.</div>'+content;
	$("#view_content").html(content);
	
}

/// Load for updates
function loadUpdateContent()
{
	var updateNotificationMessage='';
	var styleContent='display:none;';
	var TemplientPluginUpdatesiteIDCount='';
	if (typeof clientPluginUpdateSiteIDsCount != 'undefined' && clientPluginUpdateSiteIDsCount) {
		if (clientPluginUpdateSiteIDsCount<2) {
			updateNotificationMessage = 'site now.';
		} 
		else {
			updateNotificationMessage = 'sites now.';
		}
		if(updateNotificationMessage){
			styleContent='';
		}
		TemplientPluginUpdatesiteIDCount = clientPluginUpdateSiteIDsCount;
	}
	else {

		var TemplientPluginUpdatesiteIDCount = '';
	}
	var erorrCout = '';
	if (siteErrorCount) {
		erorrCout = '<span class="error_warning" style="padding: 13px 0px 0px 15px;position: relative;float: left;cursor: pointer;text-decoration: none;">'+siteErrorCount+'</span>'
	}
	var content='<div class="clientUpdateNotification" style="border-left: 2px solid #d0b000; '+ styleContent +'  background-color: #FEF5C2; padding: 10px;" >An update to the IWP Client Plugin is available. <a id="updateClientButton">Update on <span class="updateClientCount">'+TemplientPluginUpdatesiteIDCount+' '+updateNotificationMessage+' </span></a></div><div class="actionContent result_block shadow_stroke_box siteSearch" id="mainUpdateCont"><div class="">'+
	'<div class="th rep_sprite"> '+erorrCout+
		'<div class="title"><span class="droid700">Show</span></div>'+
		'<div style="float: left; width: 20%; margin-top: 6px;"><select class="update_view_dropdown float-left" style="width:204px;">'+
			'<option value="Websites">by Websites</option>'+
			'<option value="Plugins">by Plugins~'+pluginsUpdateCount+'</option>'+
			'<option value="Themes">by Themes~'+themesUpdateCount+'</option> '+
			'<option value="WP">by WP~'+WPUpdateCount+'</option>';
			content+='<option value="dot" disabled ></option><option value="Translations">Only Translations~'+transUpdateCount+'</option>'+
			'<option value="Hidden">Only Hidden Updates~'+hiddenUpdateCount+'</option>'+
		'</select></div>'+'<div class="update_by_group float-left select_box_cont groupView"></div>'+
		' <div class="type_filter">'+
			'<input name="filter" type="text" class="input_type_filter searchSiteUpdate" placeholder="Enter to search" id="searchbox" style="margin-left:12px;" />'+
			'<div class="clear_input update_search rep_sprite_backup"  onclick=""></div>'+
		'</div>'+
		'<div class="btn_action float-right"><a class="rep_sprite showTweet update_overall needConfirm" parent="item_ind" selector="item_ind" view ="sites" >Update All Sites</a></div>'+
	'</div>'+
	'<div class="th_sub rep_sprite info_bar"><div id="updatePagination"> <a id="updatePagination_m_left" class="jPaginatorMax page_left_update" style=" text-decoration: none;"></a><div id="updatePagination_o_left" class="jPaginatorOver page_o_left_update"></div> <div class="paginator_p_wrap" style="padding-left: 3px;padding-right: 3px;"> <div class="paginator_p_bloc"> </div> </div> <div id="updatePagination_o_right" class="jPaginatorOver page_o_right_update"></div><a id="updatePagination_m_right" class="jPaginatorMax page_right_update" style=" text-decoration: none;"></a> <div class="paginator_slider" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"> <a class="ui-slider-handle ui-state-default ui-corner-all" href="#"></a> </div> </div><div class="select_cont float-left" style="margin-left:15px;"><span>Select: </span><a class="all" selector="ind_row_cont">All</a><a class="invert" selector="ind_row_cont">Invert</a><a class="none" selector="ind_row_cont">None</a></div><div class=" float-right" style="margin: 5px 8px 5px 5px;"><a class="rep_sprite status_ind_row_cont update_in_page showTweet update_in_page needConfirm" parent="item_ind" selector="item_ind">Update All</a></div><div class="update-lable"><div class="label float-right" ><span class= "icon_translation_count" style="margin-right:81px;"></span></div><div class="label float-right" style="margin-right: 0%;"><span class= "icon_theme" style="margin-right:21px;" ></span></div><div class="label float-right"><span class= "icon_plugin" style="margin-right:21px;"></span></div><div class="label float-right" ><span class= "icon_wp" style="margin-right:31px;"></span></div></div></div><div id="siteViewUpdateContent" class="updateTabs" ></div>';
	content=content+"</div></div>";

	$("#pageContent").html(content);
	checkAndShowWooDBUpdatesCount();
	updateViewDropDown();
	$(".error_warning").qtip({content: { text: 'Show error sites' }, position: { my: 'bottom center', at: 'top center' }, show: { delay: 1000,effect: function() { $(this).fadeTo(500, 1);} }, hide: {inactive: 1000 }, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 9, height:4 } } });
	$(".update_in_page").qtip({content: { text: 'In Current page' }, position: { my: 'center left', at: ' center right' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy',  tip: {  corner: true, width: 5, height:8 } } });

}

function initWebsitesView(currentGroupID){
	currentUpdatePage = 'siteViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	if (typeof currentGroupID != undefined && currentGroupID != null) {
		tempArray['requiredData']['getSitesViewUpdatesContent']={};
		tempArray['requiredData']['getSitesViewUpdatesContent']['groupID']=currentGroupID;
	}else{
		tempArray['requiredData']['getSitesViewUpdatesContent']=1;
	}
	doCall(ajaxCallPath,tempArray,"loadSitesViewPageContent");
}

function initPluginsView(){
	currentUpdatePage = 'pluginViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	tempArray['requiredData']['getPluginsViewUpdatesContent']=1;
	doCall(ajaxCallPath,tempArray,"loadPluginsViewUpdatesContent");
	
}

function initThemesView(){
	currentUpdatePage = 'themeViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	tempArray['requiredData']['getThemesViewUpdatesContent']=1;
	doCall(ajaxCallPath,tempArray,"loadThemesViewUpdatesContent");
	
}

function initWPView(){
	currentUpdatePage = 'WPViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['getWPViewUpdatesContent']=1;
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	doCall(ajaxCallPath,tempArray,"loadWPViewUpdatesContent");
}

function initTranslationsView(){
	currentUpdatePage = 'TranslationViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['getTranslationViewUpdatesContent']=1;
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	doCall(ajaxCallPath,tempArray,"loadTranslationViewUpdatesContent");
	
}
function initHiddenView(currentGroupID){
	currentUpdatePage = 'hiddenViewUpdateContent';
	var tempArray={};
	tempArray['requiredData']={};
	tempArray['requiredData']['bypassHistoryAjaxCall']=1;
	if (typeof currentGroupID != undefined && currentGroupID != null) {
		tempArray['requiredData']['getHiddenViewUpdatesContent']={};
		tempArray['requiredData']['getHiddenViewUpdatesContent']['groupID']=currentGroupID;
	}else{
		tempArray['requiredData']['getHiddenViewUpdatesContent']=1;
	}
	doCall(ajaxCallPath,tempArray,"loadHiddenViewUpdatesContent");
}
function stripslashes_gg(str)
{
 return str.replace(/\\'/g,'\'').replace(/\"/g,'"').replace(/\\\\/g,'\\').replace(/\\0/g,'\0');
}
function isAddonSuiteMiniCancelMessage(addonSuiteMiniCancelMessage) {
	if(addonSuiteMiniCancelMessage=='show') {
		var cancelMessage='<div class="dialog_cont" style="width: 500px;">'+
								'<div class="th rep_sprite">'+
									'<div class="title droid700">'+
										'ADDON SUITE MINI LICENSE IS CANCELLED.'+
									'</div>'+
								'</div>'+
								'<div style="padding:20px;">'+
									'<div style="text-align:center; line-height: 22px;" id="removeSiteCont">'+
										'Your Addon Suite Mini license has been cancelled successfully.'+
										'&nbsp;<br>Reload the app once to remove the addons and the site limit.'+
									'</div>'+
									'<table style="width:320px; margin:20px auto;">'+
										'<tbody>'+
											'<tr>'+
												'<td>'+
													'<div class="btn_action float-right"></div>'+
												'</td>'+
												'<td align="center">'+
													'<div class="btn_action" style="margin: auto; cursor:pointer;">'+
														'<a class="rep_sprite btn_blue addon-suite-mini-upgrade" href="'+systemURL+'">'+
															'Reload App'+
														'</a>'+
													'</div>'+
												'</td>'+
											'</tr>'+
										'</tbody>'+
									'</table>'+									
								'</div>'+
								'<div class="clear-both"></div>'+	
							'</div>';						
		
		$('#modalDiv').html(cancelMessage).dialog({width:'auto', modal:true,position: 'center',resizable: false,open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); } });		
	}
}

function closeLimitExceededIllegallyPopup() {
	jQuery("#modalDiv").dialog("destroy");
	bottomToolBarShow();
}

jQuery('.close-button-for-limit-exceeded-illegally').live('click',function() {
	closeLimitExceededIllegallyPopup();
});

jQuery('.okay-close-button').live('click',function() {
	closeLimitExceededIllegallyPopup();
});

function addonSuiteMiniLimitExceeded(mode) {
	if(mode=='installAddons' && totalSites<=addonPlanSiteLimit) return(false);
		
	if(addonSuitePlanActivity=='installed' && isAddonPlanLimitExceeded==1) {
		var miniSiteLimit = addonPlanSiteLimit;
		var refundMessage='';
		
		var restrictionContent = '';				
		switch(mode) {
			case 'addonSuiteLimitExceededIllegally':
				restrictionContent+='<div class="dialog_cont" style="width: 697px;">'+
										'<div class="th rep_sprite">'+
											'<div class="title droid700">'+
												'YOUR ADDONS HAVE BEEN REMOVED TEMPORARILY'+
											'</div>'+
											'<a class="cancel rep_sprite_backup">'+
												'cancel'+
											'</a>'+
										'</div>'+
										'<div class="addons-plan-exceed">'+addonPlanDeails.illegalHTML+'</div>';				
			break;
			case 'installAddons':
				restrictionContent+='<div class="dialog_cont" style="width: 697px;">'+
										'<div class="th rep_sprite">'+
											'<div class="title droid700">'+
												'CANNOT INSTALL ADDONS'+
											'</div>'+
											'<a class="cancel rep_sprite_backup">'+
												'cancel'+
											'</a>'+
										'</div>'+
										'<div class="addons-plan-exceed">'+addonPlanLimitExceed.addSiteHTML+'</div>';				
			break;
			case 'installClone':	
			break;
			default:
				
				restrictionContent+='<div class="dialog_cont" style="width: 697px;">'+
										'<div class="th rep_sprite">'+
											'<div class="title droid700">'+
												'UPGRADE NOW'+
											'</div>'+
											'<a class="cancel rep_sprite_backup">'+
												'cancel'+
											'</a>'+
										'</div>'+
										'<div class="addons-plan-exceed">'+addonPlanLimitExceed.addSiteHTML+'</div>';				
			break;
		}
		
		if(mode!='installClone') $('#modalDiv').html(restrictionContent).dialog({width:'auto', modal:true,position: 'center',resizable: false,open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); } });
		return(true);	
	}
	return(false);
}

function loadAddSite(gSiteID)
{
	// if (completedAddonsInstallation == false) {
	// 	return false;
	// }

	var content='';
	var extra='';
	var googleHtml='';
	var googleWMHtml='';
	var scheduleHtml='';
	var CRScheduleHtml='';
	var reportName ='';
	var optimiseScheduleHtml= '';
	//googleHtml='<option value="AL">Alabama</option> <option value="WY">Wyoming</option>';
	if(typeof isGoogle!='undefined')
	{
		if(isGoogle==1)
		{
			var googleFirst = '<div class="tr googleEditOptions" style=""> <div class="tl" style="padding-top: 4px">Google Analytics Profile</div><div class="td"> <select name="rand_bottom" id="gg" style=""><option class="emptyG" profileID="" profileName="" profileURL="">No Profile</option>';
			var googleLast = '</select><div style="margin-top: 5px; color: #737987;">Add your site to your Google Analytics account to access it here.</div></div><div class="clear-both"></div>  </div> ';
			if(gData != null)
			{	
				$.each(gData,function(k,v){
					googleHtml = googleHtml+'<optgroup label="'+k+'">';
					$.each(v,function(m,w){
						if(w.gaProfileID!=undefined){
						googleHtml=googleHtml+'<option profileID='+w.gaProfileID+' gaID='+w.gaID+' profileName='+w.gaProfileName.replace(/ /gi, "_")+' profileURL='+w.gaSiteURL.replace(/\//gi, "_")+'>'+w.gaSiteURL.replace(/\//gi, "_")+' - '+w.gaProfileName+'</option>';
						}else{
							googleHtml=googleHtml+'<option class="emptyG" profileID="" profileName="" profileURL="">No profile found in this account</option';
						}
					});
					googleHtml = googleHtml+'</optgroup>';
				});
			}
			googleHtml=googleFirst+googleHtml+googleLast;
		}
	}
	if(typeof isGoogleWM!='undefined')
	{
		if(isGoogleWM==1)
		{
			var googleFirst = '<div class="tr googleWMEditOptions" style=""> <div class="tl" style="padding-top: 4px">Google WebMasters Profile</div><div class="td"> <select name="rand_bottom" id="ggwm" style=""><option class="emptyG" profileID="" profileName="" profileURL="">No Profile</option>';
			var googleLast = '</select><div style="margin-top: 5px; color: #737987;">Add your site to your Google Web Masters account to access it here.</div></div><div class="clear-both"></div>  </div> ';
			if(gwmData != null)
			{
				$.each(gwmData,function(k,v){
					if(v.gwtProfileID!=undefined){
						googleWMHtml=googleWMHtml+'<option profileID='+v.gwtProfileID+' profileURL='+v.gwtSiteURL.replace(/\//gi, "_")+'>'+v.gwtSiteURL.replace(/\//gi, "_")+'</option>';
					}
				});
			}
			googleWMHtml=googleFirst+googleWMHtml+googleLast;
		}
	}
	if (typeof scheduleAddonFlag!='undefined') {
		if (scheduleAddonFlag==1) {
			if (typeof schedulelists != 'undefined' && schedulelists != null) {
				var scheduleFirst = '<div class="tr scheduleBackupEditOptions" style=""> <div class="tl" style="padding-top: 4px">Backup Schedules</div><div class="td"> <select name="rand_bottom" id="sbs2" style="" multiple="multiple">';
				var scheduleLast = '</select><div style="margin-top: 5px; color: #737987;">Add this site to your existing Backup schedules.</div></div><div class="clear-both"></div>  </div> ';
				$.each(schedulelists,function(k,v){
					if(v.scheduleID!=undefined){
						scheduleHtml=scheduleHtml+'<option profileID='+v.scheduleID+' value='+v.scheduleID+'>'+v.backupName+'</option>';
					}
				});
				scheduleHtml = scheduleFirst+scheduleHtml+scheduleLast;
			}

		}
	}
	if (typeof isClientReport !='undefined') {
		if (isClientReport ==1) {
			var CRScheduleFirst = '<div class="tr CREditOptions" style=""> <div class="tl" style="padding-top: 4px">Client Reporting Schedules</div><div class="td"> <select name="rand_bottom" id="CRS2" style="" multiple="multiple">';
			var CRScheduleLast = '</select><div style="margin-top: 5px; color: #737987;">Add this site to your existing  Client Report schedules.</div></div><div class="clear-both"></div>  </div> ';
			if (typeof CRScheduleLists != 'undefined' && CRScheduleLists != null) {
				$.each(CRScheduleLists,function(k,v){
					if (k=='phoenix') {
						reportName = 'Phoenix Report Schedules'
					}else if(k=='new'){
						reportName = 'New Report Schedules'
					}else if(k=='old'){
						reportName = 'Old Report Schedules'
					}
					CRScheduleHtml = CRScheduleHtml+'<optgroup label="'+reportName+'">';
					$.each(v,function(m,w){
						if(w.clientReportScheduleID!=undefined){
						CRScheduleHtml=CRScheduleHtml+'<option profileID='+w.clientReportScheduleID+'  value='+w.clientReportScheduleID+'>'+w.name+'</option>';
						}else{
							CRScheduleHtml=CRScheduleHtml+'<option class="emptyG" profileID="" profileName="" profileURL="">No Schedules</option';
						}
					});
					CRScheduleHtml = CRScheduleHtml+'</optgroup>';
				});
			}
			CRScheduleHtml=CRScheduleFirst+CRScheduleHtml+CRScheduleLast;
		}
	}
	if (typeof isWPOptimize!='undefined') {
		if (isWPOptimize==1) {
			if (typeof optimiseSchedulelists != 'undefined' && optimiseSchedulelists != null) {
				var optimiseScheduleFirst = '<div class="tr optimiseEditOptions" style=""> <div class="tl" style="padding-top: 4px">WP Maintenance Schedules</div><div class="td"> <select name="rand_bottom" id="wps2" style="" multiple="multiple">';
				var optimiseScheduleLast = '</select><div style="margin-top: 5px; color: #737987;">Add this site to your existing WP Maintenance schedules.</div></div><div class="clear-both"></div>  </div> ';
				$.each(optimiseSchedulelists,function(k,v){
					if(v.scheduleID!=undefined){
						optimiseScheduleHtml=optimiseScheduleHtml+'<option profileID='+v.scheduleID+' value='+v.scheduleID+'>'+v.scheduleName+'</option>';
					}
				});
			}
		optimiseScheduleHtml = optimiseScheduleFirst+optimiseScheduleHtml+optimiseScheduleLast;

		}
	}
	if(group!=null && group!=undefined && group.length!=0)
	{
		extra='<div class="tr assignGroupItem addSiteToggleDiv" id="addSiteGroupsPanel"><div class="tl">Existing Groups</div><div class="td"><div class="addSiteGroups">'+groupGenerate(2)+'</div></div><div class="clear-both"></div></div>';
	}
	content=content+'<div class="dialog_cont add_site"> <div class="th rep_sprite " id="addSiteSprite"> <div class="title droid700">ADD A WORDPRESS SITE</div> <a class="cancel rep_sprite_backup">cancel</a></div> <div class="add_site form_cont " style="border:0;"> <div class="tr"> <div class="tl ">wp-admin URL</div> <div class="td"> <input name="" type="text" id="adminURL" class="onEnter" onenterbtn=".addSiteButton" /> </div> <div class="clear-both"></div> </div>  <div class="tr"> <div class="tl ">Admin Username</div> <div class="td"> <input name="" type="text" id="username" class="onEnter" onenterbtn=".addSiteButton" /> </div> <div class="clear-both"></div> </div> <div class="tr websiteURLCont" style=""> <div class="tl">Website URL</div> <div class="td"> <input name="website" type="text" id="websiteURL" class="cp_creds onEnter" onenterbtn=".addSiteButton" placeholder="" /> </div> <div class="clear-both"></div> </div><div class="tr activationKeyDiv" > <div class="tl ">Activation Key</div> <div class="td"> <input name="" type="text" id="activationKey" class="onEnter" onenterbtn=".addSiteButton" /> <div style="color: #737987;line-height: 16px;">The Activation Key will be displayed every time you activate the IWP Client Plugin on your website.</div> </div> <div class="clear-both"></div> </div> ';
	//<option value="AL">Alabama</option> <option value="WY">Wyoming</option>
	content=content+googleHtml+googleWMHtml+scheduleHtml+CRScheduleHtml+optimiseScheduleHtml+'<div class="tr" style="margin-top: 25px; margin-bottom: 15px;"> <div class="tl"></div> <div class="td"><span class="toggle_link"><span class="addSiteToggleAction assignToggleAction">Assign to groups</span><span class="addSiteToggleAction folderToggleAction">Folder protection</span><span class="addSiteToggleAction advancedContentTypeAction active">Advanced</span><span class="addSiteToggleAction siteNameToggleAction">Site Alias</span></span></div> <div class="clear-both"></div> </div> <div class="tr siteNameItem addSiteToggleDiv" style="display:none"> <div class="tl">Site Alias</div> <div class="td"> <input name="" type="text" class="siteName" id="addSiteSiteName" placeholder="site alias" style="color:#AAAAAA"/></div><div class="clear-both"></div></div> <div class="tr folderProtectionItem addSiteToggleDiv" style="display:none"> <div class="tl"></div> <div class="td"> <input name="" type="text" class="folder_protect" id="addSiteAuthUsername" placeholder="username" style="color:#AAAAAA"/> <input name="" type="password"  autocomplete="new-password" id="addSiteAuthUserPassword" class="folder_protect" placeholder="password" style="color:#AAAAAA"/> </div> <div class="clear-both"></div> </div> <div class="tr advancedContentTypeItem addSiteToggleDiv" style="display:none"> <div class="tl">CONTENT TYPE</div> <div class="td"> <div class="c_radio cTypeRadio active">application/x-www-form-urlencoded</div> <div class="c_radio cTypeRadio">multipart/form-data</div> <div class="c_radio cTypeRadio">text/plain</div> <div class="c_radio cTypeRadio customTxt"> <input name="" type="text" style="width:200px; height:10px; margin-top: -4px;" class="customTxtVal" placeholder="custom type"> </div> </div> <div class="clear-both"></div> <div class="tl">CONNECT USING</div> <div class="td"> <ul class="btn_radio_slelect float-left" style="margin-left:10px;"> <li><a class="rep_sprite optionSelect connectURLClass active" def="default">Default</a></li> <li><a class="rep_sprite optionSelect connectURLClass" def="siteURL">Site URL</a></li> <li><a class="rep_sprite optionSelect connectURLClass"  def="adminURL">wp-admin URL</a></li> </ul> </div> <div class="clear-both"></div><div class="tr SSLVersion" style=""> <div class="tl" style="padding-top: 4px">SSL Version</div><div class="td" style="width: 20%;margin-left: 9px;"><select class="float-left" id="sslv" style="margin: 34px 0px 0px 171px;"><option value="0">Auto</option><option value="6">TLS v1.2</option><option value="1">TLS v1.x</option><option value="2">SSL v2</option><option value="3">SSL v3</option><option value="4">TLS v1.0</option><option value="5">TLS v1.1</option></select></div></div><div class="clear-both"></div><div class="tl" style="padding-top: 4px">HTTP Version</div><div class="td" style="width: 20%;margin-left: 9px;"><select class="float-left" id="httpv" style=""><option value="auto">Auto</option><option value="CURL_HTTP_VERSION_1_0">v1.0</option><option value="CURL_HTTP_VERSION_1_1">v1.1</option><option value="CURL_HTTP_VERSION_2_0">v2.0</option><option value="CURL_HTTP_VERSION_2TLS">TLS v2</option></select> </div><div class="clear-both"></div><div class="tl">Website IP</div><div class="td" style="margin-left: 8px;width: 50%;"> <input name="website-IP" type="text" id="websiteIP" placeholder="example.com:80:127.0.0.1 or IP address" class="cp_creds onEnter" onenterbtn=".addSiteButton"><a href="'+supportURL+'support/solutions/articles/245699-adding-site-using-the-ip-address/'+GAHelpTracking+'" target="_blank" style="position: absolute;margin-top: 7px;margin-left: 11px;">What\'s this?</a> </div></div> <div class="tr assignGroupItem addSiteToggleDiv"> <div class="tl two_liner">New Group<br /> <span style="text-transform:none; font-size:12px;">(separate by comma)</span></div> <div class="td"> <input name="" type="text" placeholder="eg. group1, group2" id="groupText" style="color:#AAAAAA"/> </div> <div class="clear-both"></div> </div> '+extra+'</div> <div class="clear-both"></div> <div class="th_sub rep_sprite"><span class="rep_sprite_backup info float-left" id="clientPluginDescription">The IWP Client Plugin should be installed on the sites before adding them.</span> <div class="btn_action float-right "><a class="rep_sprite addSiteButton">Add Site</a></div> </div> <div class="clear-both"></div> </div>';
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	if(typeof gSiteID=='undefined' && addonSuiteMiniLimitExceeded('addSite')) {
		return(false);
	}
	$('#modalDiv').html(content).dialog({width:'auto', modal:true,position: 'center',resizable: false,open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); } });
	$(".assignGroupItem").hide();
	
	if(currentUserAccessLevel == 'admin' && typeof loadAddSiteManagerCont != 'undefined'){
		loadAddSiteManagerCont();
	}
	
	//if(typeof isStaging!='undefined' && isStaging == 1){
		addFtpButtonInSiteDetails(gSiteID);
	//}
	
	if(typeof isGoogle!='undefined')
	{   
		if(gSites!=null)
		{
			$.each(gSites,function(k,v){
				
				$('#gg option').each(function(ke,va){
					if(($(this).attr("profileID")==v.gaProfileID)&&(gSiteID == k))
					{
						$(this).attr("selected",'selected');
					}
					
				});
			});
		}
		$("#gg").select2();
	}
	if(typeof isGoogleWM!='undefined')
	{   
		if(gwmSites!=null)
		{
			$.each(gwmSites,function(k,v){
				
				$('#ggwm option').each(function(ke,va){
					if(($(this).attr("profileID")==v.gwtProfileID)&&(gSiteID == k))
					{
						$(this).attr("selected",'selected');
					}
					
				});
			});
		}
		$("#ggwm").select2();
	}
	if (typeof gSiteID != 'undefined' && typeof site[gSiteID] != 'undefined' && typeof site[gSiteID].callOpt != 'undefined' &&  site[gSiteID].callOpt != null && typeof site[gSiteID].callOpt.SSLVersion !='undefined') {
		$('#sslv option').each(function(ke,va){
			if($(this).val() == site[gSiteID].callOpt.SSLVersion){
				$(this).attr("selected",'selected');
			}
		});
	}
	if (typeof scheduleAddonFlag!='undefined') {
		if ( typeof scheduleSiteLists != 'undefined' && scheduleSiteLists != null) {
			$.each(scheduleSiteLists,function(k,v){
				
				$('#sbs2 option').each(function(ke,va){
					if(($(this).attr("profileID")==v.scheduleID)&&(gSiteID == v.siteID))
					{
						$(this).attr("selected",'selected');
					}
					
				});
			});
		}
		$("#sbs2").select2();
	}
	if (typeof isClientReport !='undefined') {
		if (typeof CRScheduleLists != 'undefined' && CRScheduleLists != null) {
			$.each(CRScheduleLists,function(k,v){
				$.each(v,function(m,w){
					$.each(w.sites,function(n,x){
						$('#CRS2 option').each(function(ke,va){
							if(($(this).attr("profileID")==w.clientReportScheduleID)&&(gSiteID == x))
							{
								$(this).attr("selected",'selected');
							}
							
						});
					});
				});
			});
		}
		$("#CRS2").select2();
	}
	if (typeof isWPOptimize!='undefined') {
		if ( typeof optimiseScheduleSiteLists != 'undefined' && optimiseScheduleSiteLists != null) {
			$.each(optimiseScheduleSiteLists,function(k,v){
				
				$('#wps2 option').each(function(ke,va){
					if(($(this).attr("profileID")==v.scheduleID)&&(gSiteID == v.siteID))
					{
						$(this).attr("selected",'selected');
					}
					
				});
			});
		}
		$("#wps2").select2();
	}
	if (typeof gSiteID != 'undefined' && typeof site[gSiteID] != 'undefined' && typeof site[gSiteID].callOpt != 'undefined' &&  site[gSiteID].callOpt != null && typeof site[gSiteID].callOpt.HTTPVersion !='undefined') {
		$('#httpv option').each(function(ke,va){
			if($(this).val() == site[gSiteID].callOpt.HTTPVersion){
				$(this).attr("selected",'selected');
			}
		});
	}
	$(".dialog_cont #gg").css("visibility", "visible");
	$(".dialog_cont #gg").css("top", "auto");
	$("#sslv").select2();
	$("#httpv").select2();
	$(".advancedContentTypeItem").show();
	if(!$('.addSiteButton').hasClass('advanced')) $('.addSiteButton').addClass('advanced');
	
	
}
function loadActivityPopup(data)
{
	$("#activityPopup").html(data.data.getHistoryPanelHTML);
}
function callActivityPopup()
{
	var tempArray={}
	tempArray['requiredData']={};
	tempArray['requiredData']['getHistoryPanelHTML']=1;
	doCall(ajaxCallPath,tempArray,'loadActivityPopup','json');
	
}

function loadComputer()
{
	var content = '<div class="itempanel_ind"><div class="lable" style="margin-bottom: -32px;margin-left: 120px; padding-top :15px">Upload .zip file</div><div id="uploaderContent" style="display: inline-block;vertical-align: top;padding: 12px 230px;width: 25%;"></div><div class="checkbox" id="addToFavoriteCheckbox" style="display:none; margin-top: 55px;margin-left:218px; position:relative;"><span style="margin-left:220;">Add to My Favorites</span></div><div class="zipNameAfterAddFavorite" style="display:none"><div class="label" style="margin-left: 230px;margin-top: 14px;">PLUGIN NAME</div><input id="uploadZipName" type="text" style ="margin-left: 230px;" class="small_input_box"><div id="favAlreadyExist" style="display: none;line-height: 18px;color: rgb(169, 42, 42);padding: 3px 5px;width: 251px;position: absolute;text-align: center;margin: -29px 0px 0px 431px;border-radius: 3px;border: 1px solid rgb(197, 136, 136);background: rgb(239, 222, 222);">Plugin already exists. Try something else.</div></div><div class="btn_action float-left" style="margin: 10px 0 0 220px;"><a class="rep_sprite installFromComputer disabled">Install '+activeItem+'</a></div><div class="clear-both"></div></div>';
	return content;
}
function loadURL()
{
	var content = '<div class="itempanel_ind"> <div class="float-left" style="text-align:right; color:#737987; padding-top: 15px;"><span class="droid700" style="font-size:11px;margin-left: 150px;">ENTER URL</span><br /></div><div class="float-left"> <input name="" type="text" id="installFromURLTxt" placeholder="http://"  class="txt onEnter " style="width: 300px; height: 17px; margin: 5px; color: rgb(170, 170, 170);"  onenterbtn="#installFromURL" /><br><span>eg. <span class="droid700">http://</span>www.wordpress.org</span> </div> <div class="checkbox" id="addToFavoriteCheckbox" style="margin-top: 70px; margin-left: 204px; position:relative"><span style="margin-left:220;">Add to My Favorites</span></div><div class="zipNameAfterAddFavorite" style="display: none;"><div class="label" style="margin-left: 217px;margin-top: 14px;">PLUGIN NAME</div><input id="uploadZipName" type="text" style ="margin-left: 217px;" class="small_input_box"><div id="favAlreadyExist" style="display: none;line-height: 18px;color: rgb(169, 42, 42);padding: 3px 5px;width: 251px;position: absolute;text-align: center;margin: -29px 0px 0px 431px;border-radius: 3px;border: 1px solid rgb(197, 136, 136);background: rgb(239, 222, 222);">Plugin already exists. Try something else.</div></div> <div class="btn_action float-left"><a class="rep_sprite disabled" id="installFromURL" style="margin-left: 215px;margin-top: 22px;">Install '+activeItem+'</a></div><div class="clear-both"></div> </div></div>';
	return content;
}

var FIVE_MINUTES = 5;
//SB
function loadBackup(multiple, siteID, scheduled, editData) {
	var extra,siteName,bkBtn,bkNowBtn;
	scheduleRepoEditData = '';

	if(multiple == 1){
		//SB start
		if(scheduled == 1){
			bkNowBtn = '<a class="rep_sprite scheduleBackup backupTab" id="backupNow" style="display:none"><span class="cant_schedule_tooltip">You need to set the cron to run.<br>Click to go to cron settings.</span>Schedule Now</a>';
			siteName = ' SCHEDULE';
		}else{
			bkNowBtn = '<a class="rep_sprite" id="backupNow" class="backupTab" style="display:none">Backup Now</a>';
			siteName = '';
		}
		//SB end
		extra = '<div class="th_sub rep_sprite"><ul class="two_steps"><li><a class="current rep_sprite_backup next" id="selectWebsitesTab">SELECT WEBSITES</a></li><li class="line"></li> <li><a id="enterDetailsTab" class="clickNone rep_sprite_backup next">ENTER DETAILS</a></li></ul> </div><div class="siteSelectorContainer backupTab">'+siteSelectorRestrictVar+'</div> <div id="backupOptions" class="backupTab" style="display:none">';
		bkBtn = '<div class="btn_next_step float-right rep_sprite disabled backupTab next" id="enterBackupDetails">Enter Details<div class="taper"></div></div><div class="btn_action float-right">'+bkNowBtn+'</div></div>';
	}
	
	if(scheduled == 1){
		enterDetailsCont = '<div class="left float-left"> <div class="label">SCHEDULE NAME</div> <input name="" type="text" id="backupName" style="width:212px;" /> <div class="float-right" style="width:36%;margin-top:-16px;"> <div class="label">NO OF BACKUPS TO KEEP</div> <div class="qty_selector_cont"> <div class="qty_btn decr rep_sprite pressed">-</div> <input name="" id="backupTotal" type="text" value="5"/> <div class="qty_btn incr rep_sprite">+</div> <div class="clear-both"></div> </div> </div> <div class="float-left" style="width:70%;"> <div class="label">WHEN?</div> <ul class="btn_radio_slelect"> <li><a class="rep_sprite active optionSelect whenType" id="dailySchedule">Daily</a></li> <li><a class="rep_sprite optionSelect whenType" id="weeklySchedule">Weekly</a></li> <li><a class="rep_sprite optionSelect whenType" id="fortnightlySchedule">Fortnightly</a></li> <li><a class="rep_sprite optionSelect whenType" id="monthlySchedule">Monthly</a></li> </ul> <div class="clear-both"></div> <div class="monthly weekly daily fortnightly whenArgs"><span style="float:left; padding:5px 10px 5px 27px;">at</span> <div class="time_select_btn rep_sprite" id="timeSelectBtn"><a timeval="0">12 am</a></div> <div class="time_select_options" style="display:none;"> <ul class="time_select_options_am"> <div class="label">am</div> <li><a std="am" timeval="0">12</a></li> <li><a std="am" timeval="1">1</a></li> <li><a std="am" timeval="2">2</a></li> <li><a std="am" timeval="3">3</a></li> <li><a std="am" timeval="4">4</a></li> <li><a std="am" timeval="5">5</a></li> <li><a std="am" timeval="6">6</a></li> <li><a std="am" timeval="7">7</a></li> <li><a std="am" timeval="8">8</a></li> <li><a std="am" timeval="9">9</a></li> <li><a std="am" timeval="10">10</a></li> <li><a std="am" timeval="11">11</a></li> <div class="clear-both"></div> </ul> <ul class="time_select_options_pm"> <div class="label">pm</div> <li><a std="pm" timeval="12">12</a></li> <li><a std="pm" timeval="13">1</a></li> <li><a std="pm" timeval="14">2</a></li> <li><a std="pm" timeval="15">3</a></li> <li><a std="pm" timeval="16">4</a></li> <li><a std="pm" timeval="17">5</a></li> <li><a std="pm" timeval="18">6</a></li> <li><a std="pm" timeval="19">7</a></li> <li><a std="pm" timeval="20">8</a></li> <li><a std="pm" timeval="21">9</a></li> <li><a std="pm" timeval="22">10</a></li> <li><a std="pm" timeval="23">11</a></li> <div class="clear-both"></div> </ul> </div> </div> </div>  <div class="clear-both"></div> <div style="margin-top:10px; display:none" class="monthly whenArgs"><span style="float:left; padding:5px 10px;">every</span> <div class="date_select_cont float-left"> <ul> <li><a class="rep_sprite active selectDate monthly">1</a></li> <li><a class="rep_sprite selectDate monthly">2</a></li> <li><a class="rep_sprite selectDate monthly">3</a></li> <li><a class="rep_sprite selectDate monthly">4</a></li> <li><a class="rep_sprite selectDate monthly">5</a></li> <li><a class="rep_sprite selectDate monthly">6</a></li> <li><a class="rep_sprite selectDate monthly">7</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate monthly">8</a></li> <li><a class="rep_sprite selectDate monthly">9</a></li> <li><a class="rep_sprite selectDate monthly">10</a></li> <li><a class="rep_sprite selectDate monthly">11</a></li> <li><a class="rep_sprite selectDate monthly">12</a></li> <li><a class="rep_sprite selectDate monthly">13</a></li> <li><a class="rep_sprite selectDate monthly">14</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate monthly">15</a></li> <li><a class="rep_sprite selectDate monthly">16</a></li> <li><a class="rep_sprite selectDate monthly">17</a></li> <li><a class="rep_sprite selectDate monthly">18</a></li> <li><a class="rep_sprite selectDate monthly">19</a></li> <li><a class="rep_sprite selectDate monthly">20</a></li> <li><a class="rep_sprite selectDate monthly">21</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate monthly">22</a></li> <li><a class="rep_sprite selectDate monthly">23</a></li> <li><a class="rep_sprite selectDate monthly">24</a></li> <li><a class="rep_sprite selectDate monthly">25</a></li> <li><a class="rep_sprite selectDate monthly">26</a></li> <li><a class="rep_sprite selectDate monthly">27</a></li> <li><a class="rep_sprite selectDate monthly">28</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate monthly">29</a></li> <li><a class="rep_sprite selectDate monthly">30</a></li> <li><a class="rep_sprite selectDate monthly">31</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <div class="clear-both"></div> </ul> </div> <div class="clear-both"></div> </div> <div style="margin-top:10px; display:none" class="fortnightly whenArgs"><span style="float:left; padding:5px 10px;">every</span> <div class="date_select_cont float-left"> <ul> <li><a class="rep_sprite active selectDate fortnightly 1f">1</a></li> <li><a class="rep_sprite selectDate fortnightly 2f">2</a></li> <li><a class="rep_sprite selectDate fortnightly 3f">3</a></li> <li><a class="rep_sprite selectDate fortnightly 4f">4</a></li> <li><a class="rep_sprite selectDate fortnightly 5f">5</a></li> <li><a class="rep_sprite selectDate fortnightly 6f">6</a></li> <li><a class="rep_sprite selectDate fortnightly 7f">7</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate fortnightly 8f">8</a></li> <li><a class="rep_sprite selectDate fortnightly 9f">9</a></li> <li><a class="rep_sprite selectDate fortnightly 10f">10</a></li> <li><a class="rep_sprite selectDate fortnightly 11f">11</a></li> <li><a class="rep_sprite selectDate fortnightly 12f">12</a></li> <li><a class="rep_sprite selectDate fortnightly 13f">13</a></li> <li><a class="rep_sprite selectDate fortnightly 14f">14</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate fortnightly 15f active">15</a></li> <li><a class="rep_sprite selectDate fortnightly 16f ">16</a></li> <li><a class="rep_sprite selectDate fortnightly 17f">17</a></li> <li><a class="rep_sprite selectDate fortnightly 18f">18</a></li> <li><a class="rep_sprite selectDate fortnightly 19f">19</a></li> <li><a class="rep_sprite selectDate fortnightly 20f">20</a></li> <li><a class="rep_sprite selectDate fortnightly 21f">21</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate fortnightly 22f">22</a></li> <li><a class="rep_sprite selectDate fortnightly 23f">23</a></li> <li><a class="rep_sprite selectDate fortnightly 24f">24</a></li> <li><a class="rep_sprite selectDate fortnightly 25f">25</a></li> <li><a class="rep_sprite selectDate fortnightly 26f">26</a></li> <li><a class="rep_sprite selectDate fortnightly 27f">27</a></li> <li><a class="rep_sprite selectDate fortnightly 28f">28</a></li> <div class="clear-both"></div> </ul> <ul> <li><a class="rep_sprite selectDate fortnightly 29f">29</a></li> <li><a class="rep_sprite selectDate fortnightly 30f">30</a></li> <li><a class="rep_sprite selectDate fortnightly 31f">31</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <li><a class="rep_sprite empty">&nbsp;</a></li> <div class="clear-both"></div> </ul> </div> <div class="clear-both"></div> </div><div style="margin-top:10px; display:none" class="weekly whenArgs"><span style="float:left; padding:5px 10px;">every</span> <div class="day_select_cont float-left"> <ul> <li><a class="rep_sprite active optionSelect" dayval="1">MON</a></li> <li><a class="rep_sprite optionSelect" dayval="2">TUE</a></li> <li><a class="rep_sprite optionSelect" dayval="3">WED</a></li> <li><a class="rep_sprite optionSelect" dayval="4">THU</a></li> <li><a class="rep_sprite optionSelect" dayval="5">FRI</a></li> <li><a class="rep_sprite optionSelect" dayval="6">SAT</a></li> <li><a class="rep_sprite optionSelect" dayval="7">SUN</a></li> </ul> </div> </div> <div class="clear-both"></div><div><div class="checkbox active generalSelect phoenix-exclusion" id="compression">Create zip without compression</div> <div class="checkbox generalSelect phoenix-exclusion" id="databaseOptimize">Optimize database tables before backup</div><div class="databaseEncryptionPhraseDiv" style="display:none;"><br><div class="label">Database encryption (Optional)<a style="font-weight: normal; margin-left: 10px; font-size: 12px;" href="'+supportURL+'solution/articles/236977-db-encryption/'+GAHelpTracking+'" target="_blank">What\'s this?</a></div><input placeholder="Enter a Phrase" name="" type="text" id="databaseEncryptionPhrase" style="width:212px;"></div></div> </div> <div class="right float-left"> <div class="backup_what" style="padding-bottom:0;"> <div class="label">WHAT?</div> <ul class="btn_radio_slelect" style="margin-bottom:20px"> <li><a class="rep_sprite active optionSelect backupType" id="full">Files & DB</a></li> <li><a class="rep_sprite optionSelect backupType" id="db">DB</a></li><li><a class="rep_sprite optionSelect backupType" id="files">Files</a></li> </ul> <div class="clear-both"></div> <div id="backupDB" style="width:390px;"> <div id="backupDB" style="width: 390px; display: block;"> <div class="float-left" style="width:180px;"><div class="label_sub">Exclude files &amp; folders</div> <input name=""  id="excludeFiles" type="text" placeholder="eg. wp-admin,old-backup.zip" style="color:#AAAAAA; height: 20px; margin-top: 5px;"></div> <div class="float-left" style="width:180px; margin-left:5%;"><div class="label_sub">Exclude files of these extension</div> <input name="" type="text" id="excludeExtensions"  placeholder="eg. .zip,.mp4"  style="height: 20px; margin-top: 5px;"></div><div class="float-left" style="width:210px; "> <div class="checkbox generalSelect active" id="excludeSize" style="margin: 0px 0px 5px -9px;">Exclude any file more than </div><div class="exclude_select2_wrapper" style="margin: -34px 0px 0px 171px;"><select class="float-left" id="excludeSizesSelect" style="margin: 34px 0px 0px 171px;"><option value="10">10MB</option><option value="50">50MB</option><option value="100">100MB</option><option value="200">200MB</option></select></div>  <div class="clear-both"></div> </div><div class="float-left" style="width: 378px; /* margin-left:5%; */"><div class="label_sub">Include folders</div> <input name="" type="text" id="includeFolders" style="height: 20px; margin-top: 5px;"></div> <div class="clear-both"></div> </div><div class="clear-both"></div> </div> <div class="float-left" style="width: 378px; /* margin-left:5%; */"><div class="label_sub">Exclude tables (These table structures only will be taken)</div> <input name="" type="text" id="excludeTables" style="height: 20px; margin-top: 5px;" placeholder="eg. posts,postmeta"></div><div class="label">BACKUP METHOD<a style="font-weight: normal; margin-left: 10px; font-size: 12px;" href="'+supportURL+'support/solutions/articles/212262-backup-methods/'+GAHelpTracking+'" target="_blank">What\'s this?</a></div><ul class="btn_radio_slelect float-left" style="margin-bottom:20px"> <li><a class="rep_sprite optionSelect backupMechanism schedule_mech active" mechanism = "singleCall" id="backup_old"><span class="tooltip_backup_method">You need to set the server cron to run every 20 min.<br>Click to go to Cron settings.</span>Single-call</a></li> <li><a class="rep_sprite optionSelect backupMechanism schedule_mech" mechanism="multiCall" id="backup_new"><span class="tooltip_backup_method">You need to set the cron to run every 5 min or activate Easycron.<br>Click to go to Cron settings.</span>Multi-call</a></li> <li><a class="rep_sprite optionSelect backupMechanism phoenix_backup schedule_mech" mechanism="advancedBackup" id="backup_new"><span class="tooltip_backup_method">You need to set the cron to run every 5 min or activate Easycron.<br>Click to go to Cron settings.</span>Phoenix (Beta)</a></li> </ul><div class="clear-both"></div><div class="fail_safe_options" ><span class="float-left" style="padding-top: 11px; margin-right: 9px;">Enable optimum memory for</span><div class="checkbox generalSelect float-left" id="fail_safe_check_files">Files</div><div class="checkbox generalSelect float-left" id="fail_safe_check_DB">DB</div><div class="clear-both"></div><div style="margin-left: 178px;margin-bottom: 10px">(Use when your backups fail)</div></div> </div>  <div class="clear-both"></div></div> <div class="clear-both"></div> </div> <div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height:35px">';
	}
	else{
		enterDetailsCont = '<div class="float-left left" style="padding:20px 20px 0; width:45%;"> <div class="label">BACKUP NAME</div> <input name="" type="text" id="backupName" /> <div class="float-left"> <div class="label">NO OF BACKUPS TO KEEP</div> <div class="qty_selector_cont"> <div class="qty_btn decr rep_sprite pressed">-</div> <input name="" id="backupTotal" type="text" value="5"> <div class="qty_btn incr rep_sprite">+</div> <div class="clear-both"></div> </div> </div> <div class="clear-both"></div><div class="clear-both" style="width:210px; margin-left:0px;"> <div class="checkbox active phoenix-exclusion generalSelect " id="compression">Create zip without compression</div> <div class="checkbox generalSelect phoenix-exclusion" id="databaseOptimize">Optimize database tables</div> <div class="clear-both"></div> </div> <div class="databaseEncryptionPhraseDiv" style="display:none;"><br><div class="label">Database encryption (Optional)<a style="font-weight: normal; margin-left: 10px; font-size: 12px;" href="'+supportURL+'solution/articles/236977-db-encryption/'+GAHelpTracking+'" target="_blank">What\'s this?</a></div><input name="" type="text" id="databaseEncryptionPhrase" placeholder="Enter a Phrase" style="width:212px;"></div></div> <div class="float-left right" style="padding:20px; width:45%;"> <div class="backup_what float-left" style="padding:0;"> <div class="label">WHAT?</div> <ul class="btn_radio_slelect float-left" style="margin-bottom:20px"> <li><a class="rep_sprite active optionSelect backupType" id="full">Files & DB</a></li> <li><a class="rep_sprite optionSelect backupType" id="db">DB</a></li><li><a class="rep_sprite optionSelect backupType" id="files">Files</a></li> </ul> <div class="clear-both"></div> <div id="backupDB" style="width:390px;"> <div id="backupDB" style="width: 390px; display: block;"> <div class="float-left" style="width:180px;"><div class="label_sub">Exclude files &amp; folders</div> <input name=""  id="excludeFiles" type="text" placeholder="eg. wp-admin,old-backup.zip" style="color:#AAAAAA; height: 20px; margin-top: 5px;"></div> <div class="float-left" style="width:180px; margin-left:5%;"><div class="label_sub">Exclude files of these extension</div> <input name="" type="text" id="excludeExtensions"  placeholder="eg. .zip,.mp4" style="height: 20px; margin-top: 5px;"></div><div class="float-left" style="width:210px; "> <div class="checkbox generalSelect active" id="excludeSize" style="margin: 0px 0px 5px -9px;" >Exclude any file more than </div><div class="exclude_select2_wrapper" style="margin: -34px 0px 0px 171px;"><select class="float-left" id="excludeSizesSelect" ><option value="10">10MB</option><option value="50">50MB</option><option value="100">100MB</option><option value="200">200MB</option></select></div>  <div class="clear-both"></div> </div><div class="float-left" style="width: 378px; /* margin-left:5%; */"><div class="label_sub">Include folders</div> <input name="" type="text" id="includeFolders" style="height: 20px; margin-top: 5px;"></div> <div class="clear-both"></div> </div><div class="clear-both"></div> </div><div class="float-left" style="width: 378px; /* margin-left:5%; */"><div class="label_sub">Exclude tables (These table structures only will be taken)</div> <input name="" type="text" id="excludeTables" style="height: 20px; margin-top: 5px;" placeholder="eg. posts,postmeta"></div><div class="label">BACKUP METHOD<a style="font-weight: normal; margin-left: 10px; font-size: 12px;" href="'+supportURL+'support/solutions/articles/212262-backup-methods/'+GAHelpTracking+'" target="_blank">What\'s this?</a></div><ul class="btn_radio_slelect float-left" style="margin-bottom:20px"> <li><a class="rep_sprite active optionSelect backupMechanism " mechanism = "singleCall" id="backup_old">Single-call</a></li> <li><a class="rep_sprite optionSelect backupMechanism " mechanism = "multiCall" id="backup_new">Multi-call</a></li> <li><a class="rep_sprite optionSelect backupMechanism phoenix_backup" mechanism = "advancedBackup" id="backup_new">Phoenix (Beta)</a></li></ul><div class="clear-both"></div> <div class="fail_safe_options"><span class="float-left" style="padding-top: 11px; margin-right: 9px;">Enable optimum memory for</span><div class="checkbox generalSelect float-left" id="fail_safe_check_files">Files</div><div class="checkbox generalSelect float-left" id="fail_safe_check_DB">DB</div><div class="clear-both"></div><div style="margin-left: 178px;">(Use when your backups fail)</div></div> </div> </div> </div> <div class="clear-both"></div> <div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height: 35px;">';
	}
	content = '<div class="dialog_cont create_backup create_backup_sitewise"> <div class="th rep_sprite"> <div class="title droid700">CREATE A NEW BACKUP'+siteName+'</div> <a class="cancel rep_sprite_backup">cancel</a></div>  '+extra+enterDetailsCont+bkBtn+'</div>';
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); forceBackup=0; $("#modalDiv").html(''); }});
	
	if(scheduled == 1){
		var isEzCronActive = typeof manageEasyCronActive != 'undefined' && manageEasyCronActive;
		var isSystemCronWithinThreshold = systemCronTimeSettings  === FIVE_MINUTES;
		//var isMultiCallScheduleSupported = isIWPCronActive || isEzCronActive || isSystemCronWithinThreshold; we disabled IWP Cron
		var isMultiCallScheduleSupported = isEzCronActive || isSystemCronWithinThreshold;
		var isSingleCallScheduleSupported = systemCronTimeSettings >= 1;

		$("#backup_old.schedule_mech").addClass("disabled");
		$("#backup_old.schedule_mech").addClass("disabled_backup_mech");
		$("#backup_new.schedule_mech").addClass("disabled");
		$("#backup_new.schedule_mech").addClass("disabled_backup_mech");			

		if(isSingleCallScheduleSupported){
			$("#backup_old.schedule_mech").removeClass("disabled");
			$("#backup_old.schedule_mech").removeClass("disabled_backup_mech");
			$("#backup_old").addClass('active');
		}

		if(isMultiCallScheduleSupported){
			$("#backup_new.schedule_mech").removeClass("disabled");
			$("#backup_new.schedule_mech").removeClass("disabled_backup_mech");
		}

		var isOnlyMultiCallSupported = isMultiCallScheduleSupported && !isSingleCallScheduleSupported;
		if(isOnlyMultiCallSupported){
			$("#backup_new").addClass('active');
		}
		
		var isNoCronRunning = !isMultiCallScheduleSupported && !isSingleCallScheduleSupported;
		if(isNoCronRunning){
			$(".schedule_mech").removeClass("active");		
		}		
	}
	
	$(".siteSelectorContainer .nano").nanoScroller({stop: true});
	$(".siteSelectorContainer .group_items_cont").css('height',$(".siteSelectorContainer .group_items_cont").height()).addClass('nano');
	$(".siteSelectorContainer .website_items_cont").css('height',$(".siteSelectorContainer .website_items_cont").height()).addClass('nano');
	$(".siteSelectorContainer .nano").nanoScroller();
	//SB start
	if(((typeof editData != 'undefined') && (editData != '')) && scheduled == 1){
		if(editData.siteName != undefined && getPropertyCount(editData.siteName) > 0){
			$.each(editData.siteName.siteIDs, function(counter, siteID) {
				$(".dialog_cont #s" + siteID).addClass('active');
			});
		}
		$(".dialog_cont .title.droid700").text("EDIT BACKUP");
		$(".dialog_cont #enterBackupDetails").removeClass('disabled');
		$(".dialog_cont .backupType").removeClass('active');
		var backupParentClass = '.create_backup';
		$("#backupName",backupParentClass).val(editData.backupName);
		$("#databaseEncryptionPhrase",backupParentClass).val(editData.IWP_encryptionphrase);
		$("#includeFolders",backupParentClass).val(editData.additionalDatas.args.include);
		$("#excludeTables",backupParentClass).val(editData.additionalDatas.args.excludeTables);
		if(typeof editData.additionalDatas.args.exclude != 'undefined'){
			$("#excludeFiles",backupParentClass).val(editData.additionalDatas.args.exclude);
			$("#excludeExtensions",backupParentClass).val(editData.additionalDatas.args.exclude_extensions);
			if(editData.additionalDatas.args.exclude_file_size > 1){
				$("#excludeSize",backupParentClass).addClass("active");
				$("#excludeSizesSelect",backupParentClass).val(editData.additionalDatas.args.exclude_file_size);
			}else{
				$("#excludeSize",backupParentClass).removeClass("active");
				$("#excludeSizesSelect",backupParentClass).val("10MB");
			}
		}
		if(editData.additionalDatas.args.disable_comp == "1"){
			$("#compression",backupParentClass).addClass('active');
		} else{
			$("#compression",backupParentClass).removeClass('active');
		}

		if(editData.additionalDatas.args.what == "full"){
			$("#full",backupParentClass).click();
		}else if(editData.additionalDatas.args.what == "db"){
			$("#db",backupParentClass).click();
		}else if(editData.additionalDatas.args.what == "files"){
			$("#files",backupParentClass).click();
		}

		if(editData.additionalDatas.args.optimize_tables == "1"){
			$("#databaseOptimize",backupParentClass).addClass('active');
		}

		if(editData.additionalDatas.args.fail_safe_files == "1"){
			$("#fail_safe_check_files",backupParentClass).addClass('active');
		}

		if(editData.additionalDatas.args.fail_safe_db == "1"){
			$("#fail_safe_check_DB",backupParentClass).addClass('active');
		}

		$("#backupTotal", backupParentClass).val(editData.additionalDatas.args.limit);

		if(editData.additionalDatas.mechanism != 'undefined'){
			$mechanism_b = editData.additionalDatas.mechanism;
			$(".backupMechanism").removeClass("active");
			$(".backupMechanism").each(function(key,val){
				var thisObj = $(this);
				if($(this).attr("mechanism") == $mechanism_b){
					thisObj.click();
				}
			});
		}

		$("#" + editData.type + "Schedule").click();
		var timeVar,timeVal;
		if(editData.time > 12){
			timeVal = editData.time - 12;
			timeVar = "pm";
		}else{
			timeVar = "am";
			if(editData.time == 0){
				timeVal=12;
			}else{
				timeVal = editData.time;
			}
		}

		$("#timeSelectBtn a",backupParentClass).attr("timeval",editData.time).text(timeVal + " " + timeVar);
		if(editData.type == "weekly"){
			$(".day_select_cont ul li a",backupParentClass).removeClass('active');
			$(".day_select_cont ul li a[dayval='" + editData.day + "']",backupParentClass).addClass('active');
		}else if (editData.type == "monthly"){
			$(".date_select_cont ul li a",backupParentClass).removeClass('active');
			$(".date_select_cont ul li a",backupParentClass).filter(function() {
				return $(this).text()  == editData.day;
			}).addClass('active');
		}else if (editData.type == "fortnightly"){
			$(".date_select_cont ul li a ",backupParentClass).removeClass('active');
			$(".date_select_cont ul li a ",backupParentClass).filter(function() {
				return $(this).text()  == editData.day1;
			}).addClass('active');
			$(".date_select_cont ul li a ",backupParentClass).filter(function() {
				return $(this).text()  == editData.day2;
			}).addClass('active');
		}

		$("#backupNow", backupParentClass).attr('schedulekey',editData.scheduleKey);
		//RP Start
		if(repositoryAddonFlag == 1){
			if(editData.additionalDatas.secure.account_info != undefined && getPropertyCount(editData.additionalDatas.secure.account_info) > 0){
				scheduleRepoEditData = editData.additionalDatas.secure.account_info;
				var tempScheduleRepoEditData = {};
				$.each(scheduleRepoEditData,function(key,val){
						tempScheduleRepoEditData['type'] = key;
				});
				$.each(scheduleRepoEditData[tempScheduleRepoEditData['type']],function(ke,va){
					tempScheduleRepoEditData[ke] = va;
				});
				delete scheduleRepoEditData;
				scheduleRepoEditData = tempScheduleRepoEditData;
			}
		}
		//RP End
	}
	// SB ends
	//RP Start
	if(repositoryAddonFlag == 1){
		$(".dialog_cont ul.two_steps").removeClass("two_steps").append('<li class="line"></li><li><a class="rep_sprite_backup next" id="selectRepository">SELECT REPOSITORY</a></li>');
	}
	//RP End
	
	$("#excludeExtensions").focus();
	$("#excludeExtensions").blur();
	$("#excludeSizesSelect").select2({   
		minimumResultsForSearch: -1,
		width: "75px"
	});
	$(".exclude_select2_wrapper").find(".select2-search, .select2-focusser").remove();

	if (iwpTrailPanel == true) {
		$("#backup_new").click();
		$('.fail_safe_options').hide();
		$("#backup_old").hide();
	}
}


function setEasyCronActivate(data){
	//isIWPCronActive = data.data['Manage_IWP_Cron::isActive']; We disabled IWP cron
	manageEasyCronActive = data.data['manageEasyCron::isActive'];
	systemCronTimeSettings = data.data.getSystemCronRunningFrequency;
	getTokenFromDB = data.data['manageEasyCron::getTokenFromDB'];

	$(".cron_message_area_error").remove();        
		
	if(manageEasyCronActive){
		$("#EasyCronApiToken").val(getTokenFromDB);
		$("#cron_activate_btn").hide();
		$("#cron_deactivate_btn").show();
		$("#EasyCronApiToken").addClass("disabled");
		$(".cron_message_area_error").remove();
		$("#easycronNote").html('The Cron will be deleted automatically once you click on Deactivate. You need not delete it manually at easycron.com');		
		$("#easycronStat").html('<div class="rep_sprite_backup block_success_icon" style="right:10px;margin-top:-12px;">Easycron is active.</div>');
	}else{
		$("#EasyCronApiToken").val(getTokenFromDB);
		$("#cron_activate_btn").show();
		$("#cron_deactivate_btn").hide();
		$("#EasyCronApiToken").removeClass("disabled");
		$("#easycronNote").html('The Cron job will be created automatically once you click on Activate. You need not create it manually at easycron.com');
		$("#easycronStat").html('<div class="rep_sprite_backup block_fail_icon" style="right:10px;margin-top:-10px;">Easycron is not active.</div>');
	}

	if((typeof data.data['manageEasyCron::activate'] != 'undefined')&&(typeof data.data['manageEasyCron::activate'].error != 'undefined')){
		var errorMsg = data.data['manageEasyCron::activate'].error;
		$(".cron_message_area").append('<div class="formLabel cron_message_area_error">'+errorMsg['message']+'</div>');
	}
	if((typeof data.data['manageEasyCron::deactivate'] != 'undefined')&&(typeof data.data['manageEasyCron::deactivate'].error != 'undefined')){
		var errorMsg = data.data['manageEasyCron::deactivate'].error;
		$(".cron_message_area").append('<div class="formLabel cron_message_area_error">'+errorMsg['message']+'</div>');
	}

}

function loadSettingButtonForCron(){
	var tempArray = {};
	tempArray['requiredData'] = {};
	tempArray['requiredData']['manageEasyCron::isActive'] = 1;
	tempArray['requiredData']['manageEasyCron::getTokenFromDB'] = 1;
	tempArray['requiredData']['getSystemCronRunningFrequency']= 1;	
	tempArray['requiredData']['Manage_IWP_Cron::isActive'] = 1;
	doCall(ajaxCallPath,tempArray,"setEasyCronActivate");
}

function loadBackupPage(data)
{
	var content=''; // Changed for SB
	data=data.data.getSitesBackupsHTML;
	//SB
	if(scheduleAddonFlag==1)
	content=content+'<div class="site_nav_sub"> <ul> <li><a class="optionSelect active" id="normalBackup">Manual</a></li> <li><a class="optionSelect" id="scheduledBackup">Scheduled</a></li> <div class="clear-both"></div> </ul> </div>';
	//SB content= changed to content=content+
	content=content+'<div class="result_block backup backup_sitewise shadow_stroke_box siteSearch" id="backupPageMainCont"><div class="th rep_sprite"><div class="type_filter "><input name="" type="text" class="input_type_filter searchItems" placeholder="type to filter"><div class="clear_input rep_sprite_backup"  onclick=""></div></div><div class="btn_action float-right" id="restrictToggleCreateBackup"><a class="rep_sprite multiBackup">Create New Backup</a></div></div><div class="no_match hiddenCont" style="display:none">Bummer, there are no backups that match.<br />Try typing fewer characters.</div>'+data+'</div>';
	$("#pageContent").html(content);
	$(".removeBackup").qtip({content: { text: 'Delete Backup' }, position: { my: 'left center', at: 'right center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 5, height:8} } });
	addQTipForDownlaodLinks();

	if(restrictToggleCreateBackupFlag == 'yes')
	{
		$('.restrictToggleCreateBackup').remove();
		$('#restrictToggleCreateBackup').remove();
		$('.scheduleBackupNowRestrictToggle').remove();
	}

}

function addQTipForDownlaodLinks()
{
	$(".download").each(function(){
		if(!$(this).hasClass('multiple_downloads')){
			$(this).qtip({content: { text: "Download Backup" }, position: { my: 'right center', at: 'left center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 5, height:8} } });
		}
		else
		{
			var numberOfDownFiles = $(this).attr('data-downcount');									//6.zip
			var displayText = "Download Backup ("+numberOfDownFiles+" parts)";   				//7
			//addQTipForDownlaodLinks(this, displayText);
			$(this).qtip({content: { text: displayText }, position: { my: 'right center', at: 'left center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark',  tip: {  corner: true, width: 5, height:8} } });
		}
	});
	
}

function loadHistoryPage(data)
{
	var userSearchOption = '';
	if(typeof multiUserAddonFlag !='undefined' && multiUserAddonFlag && currentUserAccessLevel == 'admin' && typeof data.data!='undefined' && typeof data.data.getAccessibleUsers != 'undefined' && data.data.getAccessibleUsers != null ){
		var gaUsers = data.data.getAccessibleUsers;
		var usersOptions = '<option id="0">All Users</option>';
		$.each(gaUsers,function(userID,uName){
			usersOptions += "<option id='"+userID+"'>"+uName+"</option>";
		});
		userSearchOption = '<div id="userCategorize"><select id="activityUsers" class="accUsers" >'+usersOptions+'</select></div>';
	}

	var clearHistorySearchList='';
	var clearHistoryUncompleted='';
	if(currentUserAccessLevel == 'admin'){
		clearHistorySearchList = '<div class="clearHistory deleteAllHis" what="searchList" title="Clear all activities"><span class="rep_sprite_activity_log allHistory fa_eraser" style="position:relative"></span></div>';
		clearHistoryUncompleted = '<div id="clearUncompleted" class="clearHistory" what="uncomplete" title="Clear uncompleted tasks"><span class="rep_sprite_activity_log uncompleteHistory"></span></div>';
	}

	var activityCategories = [];
	var searchOptions = '<option types="">All Actions</option>';
	var genericSearchOption = '';
	if(typeof data.data!='undefined' && typeof data.data.getActivityCategories != 'undefined'){
		activityCategories = data.data.getActivityCategories;

		$.each(activityCategories,function(category,types){
			var catypes = '';
			if(typeof types == 'object'){ catypes = types.join(","); }else if(typeof types == 'string'){catypes = types;}
			searchOptions += '<option types="'+catypes+'">'+category.replace(/_/g," ")+'</option>';
		});

		genericSearchOption += '<div id="activityKeywordFilterWrap"><select id="activityKeywordFilter">'+searchOptions+'</select></div>';
	}


	var content='<div class="result_block shadow_stroke_box history"> <div class="th rep_sprite"> <div class="title"><span class="droid700">Filter</span></div><div><div id="widget"> <div id="widgetField"> <span id="dateContainer">Select Date Range<div class="cal_arrow"></div></span> </div> <div id="widgetCalendar" style="height:0"> </div> </div>'+userSearchOption+genericSearchOption+'<span class="refreshData rep_sprite"><span class="rep_sprite_backup reload_button" style="position: relative;"></span></span></div>'+clearHistorySearchList+'<div id="historyPagination"> <a id="historyPagination_m_left" class="jPaginatorMax page_left"></a><div id="historyPagination_o_left" class="jPaginatorOver page_o_left"></div> <div class="paginator_p_wrap"> <div class="paginator_p_bloc"> </div> </div> <div id="historyPagination_o_right" class="jPaginatorOver page_o_right"></div><a id="historyPagination_m_right" class="jPaginatorMax page_right"></a> <div class="paginator_slider" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"> <a class="ui-slider-handle ui-state-default ui-corner-all" href="#"></a> </div> </div>'+clearHistoryUncompleted+'</div>';
	content=content+'<div id="historyPageContent">'+data+'</div> </div>';

	$("#pageContent").html(content);
	$("#activityUsers").select2({'width':'150px','placeholder':'Select a user'});
	$("#activityKeywordFilter").select2({'width':'150px','placeholder':'Select an action'});

	var now3 = new Date();
	now3.addDays(-4);
	var now4 = new Date();
	$('#widgetCalendar').DatePicker({
		flat: true,
		format: 'b d, Y',
		date: [new Date(now3), new Date(now4)],
		calendars:1,
		mode: 'range',
		starts: 1,
		onChange: function(formated) {
					$('#widgetField #dateContainer').get(0).innerHTML = formated.join(' - ');
					$("#widgetField #dateContainer").append('<div class="cal_arrow"></div>');
		}
	});
	loadHistoryPageContent(data);
	$(".history .refreshData").qtip({content: { text: 'Reload Data' }, position: { my: 'left center', at: 'right center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark' } });
	$(".history .deleteAllHis").qtip({content: { text: 'Clear all activities' }, position: { my: 'left center', at: 'right center' }, show: { event: 'mouseenter' }, hide: { event: 'mouseleave' }, style: { classes: 'ui-tooltip-shadow ui-tooltip-dark' } });
}
function loadHistoryPageContent(data)
{
	$('.rep_sprite_backup.reload_button').removeClass('add_spin');
	data=data.data.getHistoryPageHTML;
	$("#historyPageContent").html(data);
	
}
function loadLogHistoryPageContent(data)
{
	data=data.data.getLogPageHTML;
	$("#logHistoryPageContent").html(data);
	
}
function loadLogHistoryPage (data) {
	var content='<div class="result_block shadow_stroke_box history"> <div class="th rep_sprite"> <div id="logHistoryPagination"> <a id="logHistoryPagination_m_left" class="jPaginatorMax"></a><div id="logHistoryPagination_o_left" class="jPaginatorOver page_o_left"></div> <div class="paginator_p_wrap"> <div class="paginator_p_bloc"> </div> </div> <div id="logHistoryPagination_o_right" class="jPaginatorOver"></div><a id="logHistoryPagination_m_right" class="jPaginatorMax"></a> <div class="paginator_slider" class="ui-slider ui-slider-horizontal ui-widget ui-widget-content ui-corner-all"> <a class="ui-slider-handle ui-state-default ui-corner-all" href="#"></a> </div> </div><span class="rep_sprite_backup" style="background-position:1px -814px;padding: 14px 14px 14px 33px;width: auto;height:auto;float: right;">Data older than 90 days will be automatically deleted.</span></div>';
	content=content+'<div id="logHistoryPageContent">'+data.data.getLogPageHTML+'</div> </div>';
	$("#pageContent").html(content);

}
function loadLogHistoryPageByID(data){
	loadLogHistoryPage(data);
	var id	= $('#pageContent').find('.ind_row_cont').attr('loginid');
	$('.ind_row_cont[loginid='+id+']').find('.row_summary').click();
}
function loadBottomToolbarOptions(siteID)
{
	var readdSite='',content='';
	if($('.ind_sites#s'+siteID).hasClass('disconnected')){
		readdSite = '<li class="readd restrictToggleSite"><a class="link" sid="'+siteID+'" id="readdSite">Re-add Site</a><i class="fa fa-plus-circle site_hover_fa"></i></li>';
	}
	var removedCont = '',removedCont1='';
	var siteData = sitesjson[siteID];

	var currentWPVer = '';
	var updateWPVer = '';
	var siteIP = '';
	if(typeof site != 'undefined' && typeof site[siteID] != 'undefined' && typeof site[siteID].WPVersion != 'undefined')
		currentWPVer = 'v'+site[siteID].WPVersion;
	if(typeof siteData != 'undefined' && typeof siteData['core']!='undefined'){
		$.each(siteData['core'],function(updateVer,updateData){
			updateWPVer = '<span class="err_span">v'+updateData.current+' update available</span>';
		});
	}
	if(typeof site != 'undefined' && typeof site[siteID] != 'undefined' && typeof site[siteID].IP != 'undefined')
		siteIP = site[siteID].IP;

	var updatePluginCount = 0;
	var updatePluginNamesHTML = '';
	var old_version = '';
	if(typeof siteData != 'undefined' && typeof siteData['plugins']!='undefined'){
		$.each(siteData['plugins'],function(mainFile,pluginData){
			updatePluginCount += 1;
			if(pluginData["old_version"] != undefined) {
				old_version = pluginData["old_version"];
			}else if(pluginData["version"] != undefined) {
				old_version = pluginData["version"];
			}
			updatePluginNamesHTML += '<a>'+pluginData["name"]+' - v'+old_version+' to v'+pluginData["new_version"]+'</a>';
		});
	}
	if(updatePluginCount){
		updatePluginNamesHTML = '<div class="item_list_on_hover">'+updatePluginNamesHTML+'</div>';
		if(updatePluginCount > 1) {updatePluginCount += ' updates';}else{updatePluginCount += ' update';}
	}else{
		updatePluginCount += ' updates';
	}

	// var pluginsData = pluginsStatus[siteID];
	var installedPluginsCount = 0;
	var installedPluginNamesHTML = '';
	var activatedPluginsCount = 0;
	var inactivatedPluginsCount = 0;
	var activatedPluginNamesHTML = '';
	var inactivatedPluginNamesHTML = '';
	if(typeof pluginsStatus != 'undefined' && typeof pluginsStatus[siteID]!='undefined' && pluginsStatus[siteID]!=null){
		$.each(pluginsStatus[siteID],function(mainFile,pluginData){
			if(mainFile != 'iwp-client/init.php'){
				installedPluginsCount += 1;
				installedPluginNamesHTML += '<a>'+pluginData["name"]+' - v'+pluginData["version"]+'</a>';
				if(pluginData['isActivated']){
					activatedPluginsCount += 1;
					activatedPluginNamesHTML += '<a>'+pluginData['name']+' - v'+pluginData["version"]+'</a>';
				}else{
					inactivatedPluginsCount+=1
					inactivatedPluginNamesHTML += '<a>'+pluginData['name']+' - v'+pluginData["version"]+'</a>';
				}
			}
		});
	}
	if(installedPluginsCount){
		installedPluginNamesHTML = '<div class="item_list_on_hover">'+installedPluginNamesHTML+'</div>';
	}
	if(activatedPluginsCount){
		activatedPluginNamesHTML = '<div class="item_list_on_hover">'+activatedPluginNamesHTML+'</div>';
	}
	if(inactivatedPluginsCount){
		inactivatedPluginNamesHTML = '<div class="item_list_on_hover">'+inactivatedPluginNamesHTML+'</div>';
	}

	var updateThemesCount = 0;
	var updateThemesHTML = '';
	var activatedThemeNameHTML = '';
	var inactivatedThemeNameHTML = '';
	if(typeof siteData != 'undefined' && typeof siteData['themes']!='undefined'){
		$.each(siteData['themes'],function(mainFile,themeData){
			updateThemesCount += 1;
			updateThemesHTML += '<a>'+themeData["name"]+' - v'+themeData["old_version"]+' to v'+themeData["new_version"]+'</a>';
		});
	}
	if(updateThemesCount){
		updateThemesHTML = '<div class="item_list_on_hover">'+updateThemesHTML+'</div>';
		if(updateThemesCount > 1) {updateThemesCount += ' updates';}else{updateThemesCount += ' update';}
	}else{
		updateThemesCount += ' updates';
	}
	if(typeof themesStatus != 'undefined' && themesStatus != null && typeof themesStatus[siteID] != 'undefined' &&  themesStatus[siteID] != null){
		if(themesStatus[siteID].active !='undefined' && themesStatus[siteID].active !=null){
			var installedThemesCount = themesStatus[siteID].active.length;
			var activeTheme = 'N/A';
			var inactiveTheme = 'N/A';
			var activeThemeVersion = 'N/A';
			var inactiveThemeVersion = '';
			if(themesStatus[siteID].active[0] != 'undefined' && themesStatus[siteID].active[0] != null && themesStatus[siteID].active[0]['name'] != 'undefined' && themesStatus[siteID].active[0]['name'] != null ){
				activeTheme = themesStatus[siteID].active[0]['name'];
				activeThemeVersion = themesStatus[siteID].active[0]['version'];
			}
			if(themesStatus[siteID].inactive != 'undefined' && themesStatus[siteID].inactive != null ){
				$.each(themesStatus[siteID].inactive,function(mainFile,themeData){
					inactivatedThemeNameHTML += '<a>'+themeData['name']+' - v'+themeData['version']+'</a>';
				});
				inactivatedThemeNameHTML = '<div class="item_list_on_hover">'+inactivatedThemeNameHTML+'</div>';
			}
		}else{
			var installedThemesCount = 0;
		}
		var installedThemesHTML = '<a>'+activeTheme+' - v'+activeThemeVersion+'</a>';
		if(typeof themesStatus[siteID].inactive != 'undefined' && themesStatus[siteID].inactive.length>0){
			installedThemesCount += themesStatus[siteID].inactive.length;
			for(var i=0;i<themesStatus[siteID].inactive.length;i++)
				installedThemesHTML += '<a>'+themesStatus[siteID].inactive[i]['name']+' v -'+themesStatus[siteID].inactive[i]['version']+'</a>';
		}
		installedThemesHTML = '<div class="item_list_on_hover">'+installedThemesHTML+'</div>';
		activatedThemeNameHTML = '<div class="item_list_on_hover"><a>'+activeTheme+' - v'+activeThemeVersion+'</a></div>';
	}

	var notes = site[siteID]['notes'];
	var links = site[siteID]['links'];

	var linksHTML='';
	if(typeof links != 'undefined' && links != null && links.length){
		for(var i=0;i<links.length;i++){
			if(links[i] != '' && links[i]!='http://'){
				// if(links[i].indexOf('http://') != 0 ) links[i] = 'http://'+links[i];
				linksHTML += '<a target="_blank" href="'+links[i]+'">'+links[i]+'</a>';
			}
		}
	}else{
			linksHTML = '<a class="add_links"">Add Links</a>';
	}
	if(linksHTML == '')
		linksHTML = '<a class="add_links">Add Links</a>';

	if(notes == '' || notes == null || typeof notes == 'undefined'){
		notes = '<a class="add_notes" >Add Notes</a>';
	}

	var stagingButtonDiv = '';
	if(typeof(stagingGetSiteToggleStagingButtonDiv) == 'function'){
		stagingButtonDiv = stagingGetSiteToggleStagingButtonDiv(siteID);
	}
	var malwareButtonDiv = ''
	if(typeof(malwareGetSiteToggleMalwareButtonDiv) == 'function'){
		malwareButtonDiv = malwareGetSiteToggleMalwareButtonDiv(siteID);
	}

	var brandingButtonDiv = ''
	if(typeof(brandingToggleButtonDiv) == 'function'){
		brandingButtonDiv = brandingToggleButtonDiv(siteID);
	}

	content += '<div class="site_flap_cont_data" btsiteid="'+siteID+'" id="bottomToolbarOptions"><ul class="actions"><li style="background-color: #889297;padding-right: 616px; border-bottom:none;"><a class="link" style="margin-left: -34px; color:white; background-color: #889297; font-family : Open Sans; font-weight : 500; font-size : 16px;width: 620px;text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">'+site[siteID].name+'</a></li><li><a class="link" href="'+site[siteID].URL+'" target="_blank">View Site</a><a class="link newPost restrictToggleOpenAdmin" sid="'+siteID+'">Write new post</a><i class="fa fa-desktop site_hover_fa"></i></li>'+stagingButtonDiv+'<li class="restrictToggleOpenAdmin"><a class="link openHere"  sid="'+siteID+'">Open admin here </a><a  class="link adminPopout"  sid="'+siteID+'">Open admin in a new tab</a><i class="fa fa-user site_hover_fa"></i></li><li class="restrictToggleBackupSet">'+removedCont1+'<a class="link restrictToggleCreateBackup"  sid="'+siteID+'" id="singleBackupNow">Backup Now</a><a class="link restrictToggleBackups"  id="viewBackups" sid="'+siteID+'">View Backups</a><i class="fa fa-folder site_hover_fa"></i></li>'+malwareButtonDiv+brandingButtonDiv+'<li class="restrictToggleSite"><a class="link edit_site editSiteBtn" sid="'+siteID+'">Edit site details</a><i class="fa fa-pencil site_hover_fa"></i></li><li><a class="link reload reloadSingleStats" sid="'+siteID+'">Reload data</a><i class="fa fa-refresh site_hover_fa"></i></li><li><a class="link" sid="'+siteID+'" id="iwp_maintenace">Maintenance Mode</a><i class="fa fa-wrench site_hover_fa"></i></li><li class="restrictToggleCreateBackup"><a class="link iwpServerInfo" sid="'+siteID+'" id="iwpServerInfo">Server Info</a><i class="fa fa-info-circle site_hover_fa"></i></li>'+readdSite+'<li class="remove restrictToggleSite"><a class="link remove removeSite" sid="'+siteID+'">Remove Site</a><i class="fa fa-ban site_hover_fa"></i></li></ul><dl class="data"><dt style="padding: 24px 334px 12px 0px;"></dt><div class="clear-both"><dt>WORDPRESS</dt><dd>'+currentWPVer+updateWPVer+'</dd><div class="clear-both"></div><dt>PLUGINS</dt><dd><ul><li>'+updatePluginCount+updatePluginNamesHTML+'</li><li>'+installedPluginsCount+' installed'+installedPluginNamesHTML+'</li><li>'+activatedPluginsCount+' active'+activatedPluginNamesHTML+'</li><li>'+inactivatedPluginsCount+' inactive'+inactivatedPluginNamesHTML+'</li></ul></dd><div class="clear-both"></div><dt>THEMES</dt><dd><ul><li>'+updateThemesCount+updateThemesHTML+'</li><li>'+installedThemesCount+' installed'+installedThemesHTML+'</li><li>Active'+activatedThemeNameHTML+'</li><li>Inactive '+inactivatedThemeNameHTML+'</li></ul></dd><div class="clear-both"><dt>SITE IP</dt><dd>'+siteIP+'</dd><div class="clear-both"></div><div class="clear-both"></div>'+removedCont+'<dt><i class="edit_links"><i class="fa fa-pencil site_hover_fa"></i></i>LINKS</dt><dd style="position:relative;"><span class="site_links" >'+linksHTML+'</span></dd><div class="clear-both"></div><dt><i class="edit_note" ><i class="fa fa-pencil site_hover_fa"></i></i>NOTES</dt><dd style="line-height:18px;position:relative;"><span class="site_notes" >'+notes+'</span></dd></dl><div class="clear-both"></div></div>';	$("#bottomToolBarSelector").append(content);
	
	if(restrictAddEditDeleteSiteFlag == 'yes' ){
		$('.restrictToggleSite').remove();
	}
	if(restrictToggleOpenAdminFlag == 'yes' ){
		$('.restrictToggleOpenAdmin').remove();
	}
	if(restrictToggleCreateBackupFlag == 'yes' ){
		$('.restrictToggleCreateBackup').remove();
	}
	if(restrictToggleCreateBackupFlag == 'yes' && restrictTogglerestoreDeleteDownloadBackupFlag  == 'yes'){
		$('.restrictToggleBackupSet').remove();
	}
	var btmSiteSnap = $('.site_flap_cont_data[btsiteid="'+siteID+'"]');
	btmSiteSnap.find('.edit_links').parent().css({'height':$('.site_links').parent().height()+'px'});
	btmSiteSnap.find('.edit_note').parent().css({'height':$('.site_notes').parent().height()+'px'});
}

function iwpLoadServerInfo(data) {
	var content=data.data.iwpLoadServerInfo;
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false,open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}
function loadBackupPopup(data)
{

	var content=data.data.getSiteBackupsHTML;
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false,open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function loadAdminPopout(object,sid)
{
	var d = new Date();
    var n = d.getTime();
	var processLink=ajaxCallPath+'?action=loadSite&siteID='+sid+'&cache='+n;
	$(object).attr('href',processLink);
	$(object).attr('target','_blank');
	$(object).attr('clicked','1');
	
	resetBottomToolbar();
	
}

function loadPreview(url)
{
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	var heightVal=($(window).height())-140;
	var widthVal=$(window).width()-100;
	$("#loadingDiv").show();
	setTimeout(function () {	$("#loadingDiv").hide();},1000);
	var content = '<div class="preview_box"><iframe src="'+url+'" height="'+heightVal+'px" width="'+widthVal+'px" ></iframe><div class="preview_close cancel">close</div></div>';
	
	$('#modalDiv').html(content).dialog({width:'auto', modal:true,resizable: false,position: 'center',create: function(event, ui) {$("html").css({ overflow: 'hidden' }); bottomToolBarHide();  },close: function(event, ui) { $("html").css({ overflow: 'auto' }); bottomToolBarShow(); } });

}

function loadAdminHere(sid,extra,cid,viewCurrentSite)
{
	$("#pageContent").hide();
	var heightVal=($(window).height())-33;
	var widthVal=$(window).width();
	var where='',var_0='',var_1='';
	if(extra==1){
		where='&where=post-new';
	}else if(extra==2){
		where='&where=comment';
		var_0='var_0=action__IWPVAR__editcomment';
		var_1='var_1=c__IWPVAR__'+cid;
	}else if(extra==3){
		var pid=cid;
		where='&where=post';
		var_0='var_0=action__IWPVAR__edit';
		var_1='var_1=post__IWPVAR__'+pid;
	}else if(extra==4){
		var pid=cid;
		where='&where=link';
		var_0='var_0=action__IWPVAR__edit';
		var_1='var_1=link_id__IWPVAR__'+pid;
	}
	
	$("iframe").hide();
	$('iframe[sid="'+sid+'"]').remove();

	var processLink=ajaxCallPath+'?action=loadSite&siteID='+sid+where+'&'+var_0+'&'+var_1;
	var content = '<iframe sid='+sid+' src="'+processLink+'" height="'+heightVal+'px" width="'+widthVal+'px" style="display:block"></iframe>';
	if ($("iframe").length) {
		$("#modalDiv").show();
		$('.ui-widget-overlay').show();
		$("#modalDiv").append(content);
	} else {
		$("#modalDiv").dialog("destroy");
		$('#modalDiv').dialog({width:'auto',modal:true,resizable: false,dialogClass: 'padding_fix',position: 'center',create: function(event, ui) {
		$("#modalDiv").html(content);
		},
		close: function(event, ui) {
				$(".site",".toolbar_sites_cont").removeClass('animated');
				$("#pageContent").show();
				$("#modalDiv").dialog("destroy");
				$("#modalDiv").dialog("close");
				$("html").css({ overflow: 'auto' });
				$(".toolbar_sites_cont").hide("slide", { direction: "left" }, 500);
				$(".site",".toolbar_sites_cont").removeClass('animated');
				bottomToolBarShow(); 
			}});
	}	
	var tContent='<div class="toolbar_sites_cont" sid='+sid+' style="opacity:1"><div class="delete_user_post_ressign_tip removeTabToolTip" sid='+sid+' style="margin-left: 6px; margin-top: -23px; width: 108px; height: 20px; border-radius: 5px; display: none;line-height: 19px;"><span style="margin: 0px 0px 0px 7px;">Click to Minimise<div class="arrow-down"></div></span></div><div class="site rep_sprite"><div class="favicon_cont"><img src="'+site[sid].favicon+'" width="16" height="16"></div><div sid='+sid+' class="viewCurrentSite site_name">'+site[sid].name+'</div><div class="close rep_sprite_backup closePopup"  onclick="" style="position:relative"></div></div> </div>';
	var viewCurrentSiteRepo = false;
	if ($('.overflowTabs').find('[sid='+sid+']').length){
		$('.overflowTabs').find('[sid='+sid+']').closest('li').remove();
		if (!$(".overflowTabs").find('li').length) {
			$(".overflowTabs").remove();
		}
		viewCurrentSiteRepo = true;
	}
	var alreadySiteTabCreated = 0;
	$('.toolbar_sites_cont').each(function(){
		var tabStatus = $(this).css('display')
		if(tabStatus == 'none'){
			$(this).remove();
		} else {
			if ($(this).attr('sid') == sid && $(this).hasClass('overflowTabs') == false) {
				alreadySiteTabCreated = 1;
			}
		}
	});
	$("#siteTabs li").each(function(){
		if($(this).find('a').attr('sid') == sid ){
			alreadySiteTabCreated = 1;
		}
	});
	$('.toolbar_sites_cont').css('opacity','0.5');
	var totalTabs = $('.toolbar_sites_cont').length;
	var tabsOverFlowed = false; 
	var repoSize = $("#siteTabs").find("li").length;
	var maximumTabsOpen = 5;
	if (repoSize &&( (totalTabs - 1) + repoSize  >= maximumTabsOpen )) {
		tabsOverFlowed = true; 
	}
	var tabHTML = $('.toolbar_sites_cont');
	
		if ((!viewCurrentSite || viewCurrentSiteRepo)&& !alreadySiteTabCreated){
			if(openAdminTabSpaceCheker(totalTabs)){
				if (totalTabs >= maximumTabsOpen) {
					$(tabHTML[totalTabs-1]).remove();
				}
				$("#addWebsiteContainer").after(tContent);
			} else {
				if ((totalTabs - 1) + repoSize >= maximumTabsOpen ) {
					if (tabsOverFlowed) {
						$('iframe[sid="'+$(".overflowTabs").find('li').first().find('.viewCurrentSite').attr('sid')+'"]').remove();
						$(".overflowTabs").find('li').first().remove();
					}else {
						$(tabHTML[totalTabs-1]).remove();							
					}
				} 
				openAdminAddTabsIntoRepo(tContent);
			}
			if(currentUserAccessLevel != 'admin' && restrictToggleOpenAdminFlag != 'yes'){
				$('.toolbar_sites_cont').css('margin-left', '50px');
			}
		} else {
			$(viewCurrentSite).fadeOut('slow');
			$(viewCurrentSite).fadeIn('slow');
			$(viewCurrentSite).closest('.toolbar_sites_cont').attr('style', 'opacity: 1 !important');


		}
	$('.toolbar_sites_cont[sid='+sid+']').attr('style', 'opacity: 1 !important');


	$("html").css({ overflow: 'hidden' })
	bottomToolBarHide();

	if(toolTipData.adminPopup!="true")
	$('.showFooterSelector').qtip('show');

}

function closeOpenHereTab(closeTabID, makeCurrentOpen){
	$('iframe[sid="'+closeTabID+'"]').remove();

	$('.toolbar_sites_cont').css('opacity','0.5');
	if (makeCurrentOpen) {
		$('.toolbar_sites_cont[sid="'+makeCurrentOpen+'"]').attr('style', 'opacity: 1 !important');

	} else {
		$("#modalDiv").hide();
		$('.ui-widget-overlay').hide();
		$("#pageContent").show();
		bottomToolBarShow(); 
		$("html").css({ overflow: 'auto' });
	}

	if ($('iframe').length == 0){
		$("#modalDiv").dialog("destroy");
		$("#modalDiv").dialog("close");
	}
}
function openAdminTabSpaceCheker(totalTabs){
	var widthVal = $(window).width();
	var staticContents = 330;
	var tabWidth = 214;
	screenWidthAvailable = widthVal-330;
	totalTabWidth = totalTabs * 214 ;
	return (screenWidthAvailable-totalTabWidth)>tabWidth ? true : false;

}

function openAdminAddTabsIntoRepo(tContent){
	var totalTabs = $('.toolbar_sites_cont').length;
	var tabHTML = $('.toolbar_sites_cont');
	var overflowTabsContent = '<div class="toolbar_sites_cont overflowTabs" sid="6" style="opacity: 0.5;color: black;"><div class="site rep_sprite" style="width: 57px;"><div class="favicon_cont"><img src="images/wp_icon.png" width="16" height="16"></div><div class="dropdown_cont " style="height: 0px;"><div class="dropdown_btn open" id="selectProfileBtn_IC" style="margin-left: -1px;margin-top: 0px;width: 0px;height: 7px;opacity: 1;background-position: 0 -791px;line-height: 0.6em;border-color:#E9E9E9;"><span id="profileDropName" class="dropdown_btn_val" dropopt="1"></span></div><ul id="siteTabs" class="dropdownToggle open_admin_tab_repo"></ul></div></div> </div>';

	if (!$(".overflowTabs").length) {
		$(tabHTML[totalTabs-1]).after(overflowTabsContent);
		totalTabs = $('.toolbar_sites_cont').length;
		tabHTML = $('.toolbar_sites_cont');
	}
	var moveTabID = $(tabHTML[totalTabs-2]).attr('sid');
	$(tabHTML[totalTabs-2]).remove();
	var dropdownTab = '<li><a class="viewCurrentSite" position="tabRepo" sid='+moveTabID+'><span id="tabSiteName">'+site[moveTabID].name+'</span><div class="rep_sprite remove_bg"><span class="rep_sprite_backup del delTabRepo" sid='+moveTabID+'></span></div> </a></li>';
	$("#addWebsiteContainer").after(tContent);
	if (!$("#siteTabs").find("li").length) {
		$("#siteTabs").html(dropdownTab);
	} else {
		$("#siteTabs li").last().after(dropdownTab);
	}
	
	var repoTabCount = $("#siteTabs").find("li").length;
	if (repoTabCount) {
		$("#profileDropName").html(repoTabCount+'<div class="arrow-up"></div>');
	}
}

function openAdminLoadRepoTabAfterDeletedTab(object){
	var closeID = $(object).attr('sid');
	if ($("#siteTabs").find("li").length) {
		$(object).closest('li').remove();
		$('iframe[sid="'+closeID+'"]').remove();
	}
	if($("#siteTabs").find("li").length == 0 || $("#siteTabs").find("li").length === false){
		$(".overflowTabs").remove();
	} else {
		$('.overflowTabs').find('.dropdown_btn_val').html( $("#siteTabs").find("li").length +' <div class="arrow-up"></div>');
	}
}
function loadRemoveSite(sid)
{	
	var msg  = "";
	if (typeof site[sid] != 'undefined' && site[sid].parent == '1') {
		msg = "Looks like you are removing the Network site which will remove all subsites associated with this network.  Are you sure to remove this site from your panel?"
	}else{
		msg = "Are you sure you want to remove this website?";
	}
	var content='<div class="dialog_cont remove_site"> <div class="th rep_sprite"> <div class="title droid700">REMOVE WEBSITE</div> <a class="cancel rep_sprite_backup">cancel</a></div> <div style="padding:20px;"><div style="text-align:center;line-height: 1.5;" id="removeSiteCont">'+msg+'<div class="site">'+site[sid].URL+'</div><span>InfiniteWP Client Plugin will be deactivated.</span></div></div> <div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;" id="removeSiteButtons"><div class="warning rep_sprite_backup">This action cannot be undone.</div> <div class="btn_action float-right"><a class="rep_sprite" id="removeSiteConfirm">Remove</a></div> <span class="float-right cancel" id="dontRemoveSite">Don\'t remove</span> </div> </div>';
	$("#modalDiv").dialog("close");
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	$("#removeSiteConfirm").attr('sid',sid);
	
}

function showIWPCronInviteDialog(){
	var content = '<div class="dialog_cont" style="width: 500px;"> <div class="th rep_sprite"> <div class="title droid700">NEW CRON SERVICE FROM INFINITEWP - IWP CRON</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">We have created a new cron (task scheduler) service to make the app even more reliable. This will improve all your scheduled tasks like update notification email, scheduled backups etc. You can try it out now.</div><table style="width: 450px; margin:20px auto;"><tbody><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite closeUpdateChangeNotification cancel" style="color: #6C7277;">No! I\'m good.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue closeUpdateChangeNotification confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go to Settings to switch it on.</a></div></td></tr></tbody></table></div> <div class="clear-both"></div></div>';
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

function loadSettingsPage(data,page)
{
	settingsData = data;
	data=data.data.getSettingsAll;
	if(typeof page == 'undefined') return false;
		
		if(page == 'App'){
		// Slider code
		if($("#slider-range01").length){
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
			$( "#amount01" ).val( $( "#slider-range01" ).slider( "values", 1 ) );
		}
		if($("#slider-range02").length){
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
			$( "#amount02" ).val( $( "#slider-range02" ).slider( "values", 1 ) );		
		}
		if($("#slider-range03").length){
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
			$( "#amount03" ).val( $( "#slider-range03" ).slider( "values", 1 ) );
		}
		$('.settings_main_content').find("#amount01").val(data.settings.general.MAX_SIMULTANEOUS_REQUEST_PER_IP);
		$('.settings_main_content').find("#amount02").val(data.settings.general.MAX_SIMULTANEOUS_REQUEST);
		$('.settings_main_content').find("#amount03").val(data.settings.general.TIME_DELAY_BETWEEN_REQUEST_PER_IP);
		$('.settings_main_content').find( "#slider-range01" ).slider( "value",data.settings.general.MAX_SIMULTANEOUS_REQUEST_PER_IP);
		$('.settings_main_content').find( "#slider-range02" ).slider( "value", data.settings.general.MAX_SIMULTANEOUS_REQUEST);
		$('.settings_main_content').find( "#slider-range03" ).slider( "value", data.settings.general.TIME_DELAY_BETWEEN_REQUEST_PER_IP);

		if(data.settings.general.sendAnonymous!='' && data.settings.general.sendAnonymous!=0){
			$("#sendAnonymous").addClass('active');
		} else{
			$("#sendAnonymous").removeClass('active');
		}
		if(data.settings.general.CONSIDER_3PART_IP_ON_SAME_SERVER!='' && data.settings.general.CONSIDER_3PART_IP_ON_SAME_SERVER!=0){
			$("#ipRangeSame").addClass('active');
		} else{
			$("#ipRangeSame").removeClass('active');
		}
		if(data.settings.general.executeUsingBrowser!=undefined && data.settings.general.executeUsingBrowser!='' && data.settings.general.executeUsingBrowser!=0){
			$("#executeUsingBrowser").addClass('active');
			check_fsock = 'set';
		} else{
			$("#executeUsingBrowser").removeClass('active');
			check_fsock = '';
		}
		if(data.settings.general.autoSelectConnectionMethod!=undefined && data.settings.general.autoSelectConnectionMethod!='' && data.settings.general.autoSelectConnectionMethod!=0){
			$("#autoSelectConnectionMethod").addClass('active');
			$("#executeUsingBrowser").addClass('disabled').removeClass('active');
		} else{
			$("#autoSelectConnectionMethod").removeClass('active');
			$("#executeUsingBrowser").removeClass('disabled');
		}
		if(data.settings.general.enableReloadDataPageLoad!=undefined && data.settings.general.enableReloadDataPageLoad!='' && data.settings.general.enableReloadDataPageLoad!=0){
			$("#enableReloadDataPageLoad").addClass('active');
		} else{
			$("#enableReloadDataPageLoad").removeClass('active');
		}
		$("#currentPassword").val('Current Password');
		$("#newPassword").val('New Password');
		$("#newPasswordCheck").val('New Password Again');
		$(".change_pass_cont").hide();
		$(".passwords").blur();

		$('#timeZoneSelector').select2({'placeholder':'Not set/Unavaliable in the list','width':'50%'});
		if($('#timeZoneSelector').val() == ''){
			if(typeof settingsData['data']['getSettingsAll']['settings']['general']['TIMEZONE']!='undefined' && settingsData['data']['getSettingsAll']['settings']['general']['TIMEZONE'] !=''){
				$('#timeZoneSelector').parent().append('<div>Default Timezone('+settingsData['data']['getSettingsAll']['settings']['general']['TIMEZONE']+') has been set. We recommend you to set it manually above.</div>');
			}else{
				$('#timeZoneSelector').parent().append('<div>Timezone has not been set. We recommend you to set it manually above.</div>');				
			}
		}

		if(data.settings.general.autoDeleteLog!=undefined && data.settings.general.autoDeleteLog!='' && data.settings.general.autoDeleteLog!=0){
			$("#clearLogSchedule").addClass('active');
			$("#cls_times,#cls_times .cls_time").removeClass('disabled');
			$("#cls_times .cls_time[older='"+data.settings.general.autoDeleteLog+"']").addClass('active');
		}else{
			$("#clearLogSchedule").removeClass('active');
			$("#cls_times,#cls_times .cls_time").addClass('disabled').removeClass('active');
		}



		
	}else if(page == 'Security'){
		var iContent = '';
		if( getPropertyCount(data.settings.allowedLoginIPs)>0)
		{
			$.each(data.settings.allowedLoginIPs, function (IP)
			{
				iContent=iContent+'<div class="ip_cont"><span class="droid700 IPData">'+IP+'</span><span class="remove removeIP ">remove</span></div>';
			});
		}
		else iContent = "<div id='noIP'>No IPs added.</div>";
		$("#noIP").remove();
		$(".ip_cont").remove();
		$("#IPContent .right").after(iContent);
		
				if(typeof data.settings.loginAuthType == 'undefined' || (data.settings.loginAuthType !="authBasic" && data.settings.loginAuthType !="authDuo" )) {
					data.settings.loginAuthType = "authNone";
				}

				loginAuthType = data.settings.loginAuthType;
				$(".loginAuthType").removeClass('active');
				$('#'+loginAuthType).addClass('active').click();
				
		if(data.settings.general != false)
		{																						                                                            //darkComment
			if(data.settings.general.httpAuth!=undefined && data.settings.general.httpAuth.username!=undefined)
				$("#authUsername").val(data.settings.general.httpAuth.username);
			if(data.settings.general.httpAuth!=undefined && data.settings.general.httpAuth.password!=undefined)
				$("#authPassword").val(data.settings.general.httpAuth.password);
		}
		if(data.settings.general.enableHTTPS == '1'){
			$("#enableHTTPS").addClass('active');
		}else{
			$("#enableHTTPS").removeClass('active');
		}
		if(data.settings.general.enableSSLVerify == '1'){
			$("#enableSSLVerify").addClass('active');
		}else{
			$("#enableSSLVerify").removeClass('active');
		}
		if(isHTTPSDefined){
			$("#HTTPSConfig").addClass('disabled').css({'opacity':'0.5'});
			$(".HTTPSConfigCSS").css({'top':'72px'});
		}else{
			$(".HTTPSConfig").hide();
		}
		if(isDisabled2FA){
			$("#2FAConfig").addClass('disabled').css({'opacity':'0.5'});
			$('#2FAConfig').find('.notes').css({'opacity':'0.5'});
			$('.disableClass').find('.label').css({'opacity':'0.5'});
			$('.settings_main_content').find('#duo_ikey').attr('disabled',true);
			$('.settings_main_content').find('#duo_skey').attr('disabled',true);
			$('.settings_main_content').find('#duo_api_host').attr('disabled',true);
		}else{
			$(".2FAConfig").hide();
		}

	}else if(page == 'Account'){
		$("#email").val(data.accountSettings.email).removeClass('focus');
		if(data.settings.notifications != false)																	 //darkComment
		{   var one,two,three,four,five=0;
			$(".onlyVulnsChecked").hide();
			$(".vulnsAllChecked").hide();
			if(data.settings.notifications.updatesNotificationMail.coreUpdates==1){
				$("#notifyWordpress").addClass('active'); one = 1;
			}
			if(data.settings.notifications.updatesNotificationMail.pluginUpdates==1){
				$("#notifyPlugins").addClass('active'); two = 1;
			}
			if(data.settings.notifications.updatesNotificationMail.themeUpdates==1){
				$("#notifyThemes").addClass('active'); three = 1;
			}
			if(data.settings.notifications.updatesNotificationMail.translationUpdates==1){
				$("#notifyTranslations").addClass('active'); four = 1;
			}
			if(data.settings.notifications.updatesNotificationMail.WPVulnsUpdates==1){
				$("#notifyVulns").addClass('active'); five = 1;
			}
			if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
				WPVulnsSettingNotesUpdate();
			} 
			$("#email"+data.settings.notifications.updatesNotificationMail.frequency.toTitleCase()).addClass('active');
		}
	}else if(page == 'Google'){
		if(typeof data.settings.google != 'undefined' && data.settings.google != null){
			$('.settings_main_content').find('#clientID').val(data.settings.google.clientID);
			$('.settings_main_content').find('#clientSecretKey').val(data.settings.google.clientSecretKey);
			if(grantRevokeGPSData == 'grant'){
				$('.gps_grant').hide();		$('.gps_revoke').show();
			}else{
				$('.gps_grant').show();		$('.gps_revoke').hide();
			}
			if(grantRevokeWMTData == 'grant'){
				$('.gwmt_grant').hide();	$('.gwmt_revoke').show();
			}else{
				$('.gwmt_grant').show();	$('.gwmt_revoke').hide();
			}
		}
		if (typeof googleSafeBrowsingAPI != 'undefined') {
			$(".settings_main_content").find('.googleAPIKey').val(googleSafeBrowsingAPI);
		}
	}else if(page == 'Cron'){
		if(typeof getTokenFromDB != 'undefined' && getTokenFromDB != null){
			$('.settings_main_content').find("#EasyCronApiToken").val(getTokenFromDB);
		}
	}else if(page == 'Client Plugin Branding'){
		if(typeof clientPluginBrandingSettings!='undefined' && clientPluginBrandingSettings!=null && typeof loadNewClientPluginBranding!='undefined' && loadNewClientPluginBranding!=null ){
			loadNewClientPluginBranding(clientPluginBrandingSettings);
		}
	}
	else if(page == 'Staging'){
		$('#loadingDiv').show();
		$('#stage_this_site').addClass('disabled');
		$('#saveSettingsBtn').addClass('disabled');
		$('.staging_path').remove();
		stagingInitiateFillingMainStagingFtpSettings();
	}
	else if(page == 'Uptime Monitor'){
		if(typeof data.settings.uptimeRobot != 'undefined' && data.settings.uptimeRobot != null && typeof loadUptimeMonitorKey!='undefined' && loadUptimeMonitorKey!=null ){
			loadUptimeMonitorKey(data.settings.uptimeRobot);
		}		
	} else if(page === 'Schedule Backups'){
		if(typeof scheduledBackupEmailSetting != 'undefined' && scheduledBackupEmailSetting == 1){
			$('#scheduledBackupEmailSetting').addClass('active');
		}
	} else if(page == 'App Update'){
		$('.updates_folder').append('<div class="checking notes">Checking temp directory permission...</div>');
		$('.uploads_folder').append('<div class="checking notes">Checking update directory permission...</div>');
		$('.FTP_form').prepend('<div class="checking notes">Checking FTP details...</div>');
		$('.ftpconfig_e').hide();
		$('.ftpconfig').hide();
		if (isFSMethodDefined){
			$('.fs_method').find('.c_radio ').css({'opacity':'0.5'});
			$('.fs_method').find('.notes ').css({'opacity':'0.5'});
			$('.fs_method').find('.FTP_form_con ').css({'opacity':'0.5'});
			$('.settings_main_content').find('.fs_method').addClass('disabled');
			$('.settings_main_content').find('.FTP_form').addClass('disabled');
			$('.settings_main_content').find('#FTPHost').attr('disabled',true);
			$('.settings_main_content').find('#FTPPort').attr('disabled',true);
			$('.settings_main_content').find('#FTPBase').attr('disabled',true);
			$('.settings_main_content').find('#FTPUser').attr('disabled',true);
			$('.settings_main_content').find('#FTPPass').attr('disabled',true);
		}else{
			$(".fs_config").hide();
		}
		if (isDirectFS == 'Y') {
			$(".directMethod").addClass("active");
			$(".app_update_cont .FTP_form").hide();
			$(".direct_texts").show();
			$(".FTPtexts").hide();
			$(".test_conn_cont").hide();
		}
		else{
			$(".ftpMethod").addClass("active");
			$(".direct_texts").hide();
		}	
		if(typeof data.settings.FTP != 'undefined' && data.settings.FTP != null){
			$('.settings_main_content').find('#FTPHost').val(data.settings.FTP.HOST);
			if(data.settings.FTP.PORT == null){
				$('.settings_main_content').find('#FTPPort').val('21');
			}
			else{
				$('.settings_main_content').find('#FTPPort').val(data.settings.FTP.PORT);
			}
			$('.settings_main_content').find('#FTPBase').val(data.settings.FTP.BASE);
			$('.settings_main_content').find('#FTPUser').val(data.settings.FTP.USER);
			$('.settings_main_content').find('#FTPPass').val(data.settings.FTP.PASS);

			if (typeof data.settings.FTP.ftp_key != 'undefined') {
				$('.settings_main_content').find('#ftp_key').val(data.settings.FTP.ftp_key);
			}
			if(parseInt(data.settings.FTP.SSL)){
				$('.settings_main_content').find('#enableFTPSSL').addClass('active');
			}else if(parseInt(data.settings.FTP.SFTP)){
				$('.settings_main_content').find('#enableSFTP').addClass('active');
				$('.ftp_form_key').show();
			}else{
				$('.settings_main_content').find('#enableFTP').addClass('active');
			}
			if (parseInt(data.settings.FTP.PASV) || data.settings.FTP.PASV == null) {
				$('#FTPPasv').addClass('active');
			}
		}	
	}else if(page == 'Email Settings'){
		if(typeof data.settings.emailSettings != 'undefined' && data.settings.emailSettings != null){
			var this_data_arr = data.settings.emailSettings;
			
			//check if use smtp is enabled
			$('.email_settings #useSmtp').removeClass("active");
			
			if(this_data_arr.fromEmail != null){
				$('.email_settings').find('#fromEmail').val(this_data_arr.fromEmail);
			}
			$('.email_settings').find('#fromName').val(this_data_arr.fromName);
			
			$('.email_settings').find('#smtpHost').val(this_data_arr.smtpSettings.smtpHost);
			$('.email_settings').find('#smtpPort').val(this_data_arr.smtpSettings.smtpPort);
			$('.email_settings').find('#smtpAuthUsername').val(this_data_arr.smtpSettings.smtpAuthUsername);
			$('.email_settings').find('#smtpAuthPassword').val(this_data_arr.smtpSettings.smtpAuthPassword);
			
			$('.emailSet').removeClass("active");
			
			if(this_data_arr.smtpSettings.smtpEncryption == 'tls'){
				$('.email_settings').find('#tlsEncryption').addClass('active');
			}else if(this_data_arr.smtpSettings.smtpEncryption == 'ssl'){
				$('.email_settings').find('#sslEncryption').addClass('active');
			}else{
				$('.email_settings').find('#noEncryption').addClass('active');
			}
			
			if(this_data_arr.smtpSettings.smtpAuth === '1'){
				$('.email_settings').find('#yesSmtpAuth').addClass('active');
			}
			else{
				$('.email_settings').find('#noSmtpAuth').addClass('active');
			}
			
			if(this_data_arr.smtpSettings.useSmtp == '1'){
				$('.email_settings #useSmtp').addClass("active");
				$(".email_settings .ftp_details_wrapper").show();
				$(".test_send_mail_smtp").show();
			}
			else{
				$(".email_settings .ftp_details_wrapper").hide();
				$(".test_send_mail_smtp").hide();
				$(".email_settings .ftp_Username_details").hide();
				$(".email_settings .ftp_details_wrapper.smtp_from_email").show();
				$(".email_settings .ftp_details_wrapper.smtp_from_name").show();
			}
		}
		else{
			$(".email_settings .ftp_details_wrapper").hide();
			$(".test_send_mail_smtp").hide();
			$(".email_settings .ftp_Username_details").hide();
			$(".email_settings .ftp_details_wrapper.smtp_from_email").show();
			$(".email_settings .ftp_details_wrapper.smtp_from_name").show();
		}
	}else if(page == 'Connection Method'){
            if(typeof settingsData.data.getSettingsAll.connectionMethodData!='undefined'){
                var connectionData = settingsData.data.getSettingsAll.connectionMethodData;
                $('.connectionMethodRadio').removeClass('active');
                $('.connectionModeRadio').removeClass('active');
                $('.connectionRunnerRadio').removeClass('active');
                $('#'+connectionData.connectionMethod).addClass('active').trigger('click');
                if(connectionData.connectionMethod=='manual'){
                    $('#'+connectionData.connectionMode).addClass('active').trigger('click');
                    $('.connectionRunnerRadio').removeClass('active');
                    $('#'+connectionData.connectionRunner).addClass('active');
                }
                disableConnectionMethod(settingsData.data.getSettingsAll.connectionMethodCompatibility);
                
            }
        }
}
function loadReport(data,extra) // For sending general feedback.
{
	var extraContent,ifAnyContent,actionType,reportHeader;
	if(extra==undefined || extra == '')
	{
		extraContent='<div class="tl">REPORT CONTENT</div> <div class="td"> <textarea name="textarea" id="panelHistoryContent" cols="45" rows="5" disabled="disabled" class="disabled">'+data.data.getReportIssueData.report+'</textarea><input type="hidden" id="panelHistoryActionID" value="'+data.data.getReportIssueData.actionID+'"></div>';
		ifAnyContent='COMMENTS<br /><span style="text-transform:none; font-size:11px; font-style:italic; font-weight:400">required</span>';
		actionType = 'historyIssue';
		preContent='<div class="preReport"><div style="text-align:center; line-height: 30px; padding-top: 10px;" id="removeSiteCont">Have you tried viewing the site response from your WP site?<br>Are you sure this is not an issue on the WordPress site\'s side?<br>Have you tried searching through our <a href="https://support.infinitewp.com/support/home?utm_source=application&utm_medium=userapp&utm_campaign=check-support-before-report" target="_blank">Help Desk</a> for a solution?</div><table style="width:350px; margin:20px auto;"><tr><td><div class="btn_action float-right"><a class="rep_sprite btn_blue yesReport" style="display: block; cursor:pointer; ">Yes, let me report this issue.</a></div></td><td><div class="btn_action float-right"><a class="rep_sprite cancel" style="color: #6C7277;">No, I\'ll try them first.</a></div></td></tr><tr><td></td><td align="center"></td></tr></table></div>';
		preDisplay="display:none;";
		reportHeader='REPORT ISSUE';
	}
	else
	{
		extraContent='';
		ifAnyContent='ISSUE<br /><span style="text-transform:none; font-size:11px; font-style:italic; font-weight:400">required</span>';
		actionType = 'userIssue';
		reportHeader='REPORT ISSUE';
		preDisplay="";
		preContent='';
	}
	
	var content='<div class="dialog_cont send_report" style="width: 542px;"> <div class="th rep_sprite"> <div class="title droid700">'+reportHeader+'</div> <a class="cancel rep_sprite_backup">cancel</a></div> <div class=" form_cont " style="border:0;"><div class="issue_content" style="'+preDisplay+'"> <!--<div class="th rep_sprite">Add New Website</div>--> <div class="tr"> <div class="tl">SEND FROM</div> <div class="td"> <input name="" type="text" id="emailToReport" value="'+settingsData.data.getSettingsAll.accountSettings.email+'" /> </div> <div class="clear-both"></div> </div> <div class="tr"> <div class="tl two_liner">'+ifAnyContent+'</div> <div class="td"> <textarea name="textarea" id="customerComments" cols="45" rows="5"></textarea> </div>'+extraContent+' </div> <div class="clear-both"></div> </div>'+preContent+' <div class="tr">  <div class="clear-both"></div> </div> </div> <div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height: 35px;"><div class="btn_action float-right"><a class="rep_sprite" id="sendReportBtn" actiontype="'+actionType+'" style="'+preDisplay+'">Send Issue Report</a></div></div> </div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}
function loadMoreInfo(data)
{
	var finalData=data.data.getResponseMoreInfo;
	if(finalData=='' || finalData==null)
	finalData='No additional website response available.';
	var content='<div class="dialog_cont send_report" style="width: 500px;"> <div class="th rep_sprite"> <div class="title droid700">WEBSITE RESPONSE</div> <a class="cancel rep_sprite_backup">cancel</a></div> <div class=" form_cont" style="border:0;"><div class="td" id="iframeInfoDiv"></div></div></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	$('<iframe id="someIdFrame" width="480" height="300"/>').appendTo('#iframeInfoDiv').ready(function(){
		setTimeout(function(){
			
			$('#someIdFrame').contents().find('body').append(finalData);
		},500);
	});
	$('#modalDiv').dialog({position: 'center'});

}
function loadCurlVerbose(data)
{
	var finalData=data.data.getResponseCurlVerbose;
	if(finalData=='' || finalData==null)
	finalData='Curl verbose not running';
	var content='<div class="dialog_cont send_report" style="width: 500px;"> <div class="th rep_sprite"> <div class="title droid700">WEBSITE RESPONSE</div> <a class="cancel rep_sprite_backup">cancel</a></div> <div class=" form_cont" style="border:0;"><div class="td" id="iframeInfoDiv"></div></div></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	$('<iframe id="someIdFrame" width="480" height="300"/>').appendTo('#iframeInfoDiv').ready(function(){
		setTimeout(function(){
			$('#someIdFrame').contents().find('body').append('<pre>'+finalData+'/<pre>');
		},500);
	});
	$('#modalDiv').dialog({position: 'center'});

}
function loadPanelUpdate(data)
{
	var newVersion=data.newVersion;
	var lastUpdate = '';
	if (lastPanelUpdated != false) {
		lastUpdate = '<div style="text-align: center;margin-top: 20px;font-size: 11px;"> Last updated on '+lastPanelUpdated+'</div>';
	}
	$(".updatePanelData").remove();
	var content='<div class="padding updatePanelData"> <div class="updates_logo_icon_cont"> '+lastUpdate+' <div class="version_cont float-left"> <div class="updates_logo_icon"></div> <div class="updates_version_number">v'+appVersion+'</div> </div> <div class="updates_arrow float-left"></div> <div class="version_cont updates float-left"> <div class="updates_logo_icon"></div> <div class="updates_version_number">v'+newVersion+'</div> <a class="change_log" href="'+data.updateDetails[newVersion].changeLogLink+'" target="_blank">View change log</a> </div> <div class="clear-both"></div> </div> <div class="updates_descr">'+data.updateDetails[newVersion].breifDescr+'</div> </div>';
	content = content + '<div class="checkbox optionSelectOne participateInBetaCheck" style="border-top: 1px solid #E0E0E0;">Participate in Beta program</div>';
	$(".participateInBetaCheck").remove();
	$("#updates_centre_cont").prepend(content).css({'width':'341px','margin-top':'34px'});
	$(".updateExtraBtn").remove();
	$(".updateActionBtn").text('Check Now').attr('btnaction','check').removeClass('disabled');
	$(".updateActionBtn").closest('.btn_action').before('<div class="btn_action float-right"><a class="rep_sprite updateActionBtn updateExtraBtn needConfirm" btnaction="update" version="'+newVersion+'">Update Now</a></div>');
	loadUpdateNotify();
	$("#updateCenterCount").show();
	if(settingsData.data.getSettingsAll.settings.general.participateBeta == 1)
	$('.participateInBetaCheck').addClass('active');
	else																					//TO check the participate in beta checkbox on clicking dialog box options
	$('.participateInBetaCheck').removeClass('active');	
	
}
function loadPanelUpdateDefault()
{
	$(".updatePanelData").remove();
	var lastUpdate = '';
	if (lastPanelUpdated != false) {
		lastUpdate = '<div style="text-align: center;margin-top: 20px;font-size: 11px;"> Last updated on '+lastPanelUpdated+'</div>';
	}
	var content='<div class="padding updatePanelData"> <div class="updates_logo_icon_cont"> '+lastUpdate+' <div class="version_cont float-left" style="margin-left: 76px; margin-bottom: 0px;"> <div class="updates_logo_icon"></div> <div class="updates_version_number" id="currentVersionNumber">v'+appVersion+'</div> </div><div class="clear-both"></div> </div>  </div>';
	content = content + '<div class="checkbox optionSelectOne participateInBetaCheck" style="border-top: 1px solid #E0E0E0;">Participate in Beta program</div>';
	$("#updates_centre_cont").prepend(content).css({'width':'314px','margin-top':'33px'});
	$(".updateActionBtn").text('Check Now').attr('btnaction','check');
	$("#updateCenterCount").hide();
	if(settingsData.data.getSettingsAll.settings.general.participateBeta == 1)
	$('.participateInBetaCheck').addClass('active');
	else																					//TO check the participate in beta checkbox on clicking dialog box options
	$('.participateInBetaCheck').removeClass('active');															
}
function loadPanelUpdateV3(data){
	$('#updateCentreBtnV3').show();
	var newVersion=data.newVersion;
		$(".updatePanelDataV3").remove();
		var content='<div class="padding updatePanelDataV3"> <div class="updates_logo_icon_cont"> <div class="version_cont float-left"> <div class="updates_logo_icon"></div> <div class="updates_version_number">v'+appVersion+'</div> </div> <div class="updates_arrow float-left"></div> <div class="version_cont updates float-left"> <div class="updates_logo_icon"></div> <div class="updates_version_number">v'+newVersion+'</div> <a class="change_log" href="'+data.updateDetails[newVersion].changeLogLink+'" target="_blank">View change log</a> </div> <div class="clear-both"></div> </div> <div class="updates_descr">'+data.updateDetails[newVersion].breifDescr+'</div> </div>';

		$("#updates_centre_cont_V3").prepend(content).css({'width':'341px','margin-top':'34px'});
		$(".updateExtraBtnV3").remove();
		$(".updateActionBtnV3").closest('.btn_action').html('<div class="btn_action float-right"><a class="rep_sprite updateActionBtnV3 updateExtraBtnV3 needConfirm" btnaction="update" version="'+newVersion+'">Update Now</a></div>');
		$("#updateCenterCountV3").show();
	
}
function loadUpdateNotify()
{
	if(updateAvailableNotify==false)
	{
		$("#updates_centre_notif").remove();
		var content='<div id="updates_centre_notif">An update to the admin panel is available.<span id="updateNotifyClose" version="'+updateAvailable.newVersion+'">x</span></div>';
		$("#updates_centre_cont").before(content);
	}
}
function loadFeatureTour()
{
	var  heightVal=($(window).height());
	var widthVal=$(window).width();
	var content = '<iframe src="./demo/demo.html" height="'+heightVal+'px" width="'+widthVal+'px" id="demoTour"></iframe>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center', dialogClass: 'padding_fix' ,resizable: false,zIndex: 1020 , create: function(event, ui) {  $("html").css({ overflow: 'hidden' }); bottomToolBarHide();},close: function(event, ui) { $("html").css({ overflow: 'auto' }); bottomToolBarShow();}});
	
	
}

function loadFeatureTourPopup()
{
	var content='<div class="dialog_cont take_tour" style="width:400px;"> <div class="th rep_sprite"> <div class="title droid700">TAKE A TOUR</div></div> <div style="padding:20px;"><div style="text-align:center;" id="removeSiteCont">Would you like us to show you around the app?</div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"><a class="rep_sprite btn_blue takeTour closeTour" style="display: block;font-size:11px ">Sure, Take a Tour.</a></div></td><td><div class="btn_action float-right"><a class="rep_sprite closeTour cancel" style="color: #6C7277;font-size:11px">No thanks. I ll find my way.</a></div></td></tr><tr><td></td><td align="center"><span style="color:#96999b; text-align:centre;">You can take the tour anytime <br>from the top navigation.</span></td></tr></table></div> <div class="clear-both"></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function loadBasicSettingsPopup()
{
	var isEnabledHTTPS = settingsData.data.getSettingsAll.settings.general.enableHTTPS;
	var addHTTPSClass = '';
	if (isEnabledHTTPS) {
		addHTTPSClass = 'active';
	}
	var httpsSystemURL = systemURL.replace('http://', "https://");
	var firstStep = '<li class="initial_setup_links"><a class="rep_sprite_backup basic_options current" refClass="initialSetupSecurityTab" >Security</a></li>';
	var firstStepContent='<div class="basic_options_details initialSetupSecurityTab" style="display:block"><div class="padding"> <div class="label" style=""><span style="text-transform:uppercase">Security set up</span> <span><a href="https://infinitewp.com/docs/how-to-secure-the-infinitewp-admin-panel/" style="margin-left: 20px; font-weight: normal;font-size: 12px;" target="_blank">See how to secure your admin panel</a></span></div><div class="" style="line-height: 22px;padding: 8px 0px;margin-bottom:15px">lnfiniteWP has been built with a strong focus on security and privacy of your data. You can take a few important steps to fortify your panel.</div><div class="label" style="margin-bottom: 10px;">HTTPS</div><div class="checkbox '+addHTTPSClass+'" id="enableHTTPSInitialSetup" style="margin-top:-10px;margin-bottom: 30px; color: #000 !important;    position: relative;">Enable</div><div class="HTTPSConfigCSS" style="left: 282px; position: absolute;top: 186px;">(Make sure this link - <a href="'+httpsSystemURL+'" target="_blank" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; max-width: 299px;margin-bottom: -2px;">'+httpsSystemURL+'</a> works before enabling HTTPS)</div><div style="position: absolute;top: 208px; left: 230px;">(Note: Currently, we do not support Proxy SSL)</div> <div class="label" style="text-transform:uppercase">IP restriction</div><div class="" style="line-height: 22px;padding: 10px;">You can restrict access to the admin panel to visitors from a list of specified IPs. Use this only if this server has a static IP.</div><input name="" type="text" style="width: 210px; margin-left: 10px; height: 100%; padding: 7px 9px 8px 7px;" id="initialSetupIPRestriction" placeholder="Enter IP address"><div class="checkbox " style="margin-top: -47px;margin-left: 258px; color:#000 !important" id="initialSetupIPRestrictionCheckBox">Use my current IP: '+IP+'</div><div style="margin-top: 10px;margin-left: 10px;line-height: 18px;">You can use a wild card IPs. Eg. 192.168.1.*<br>You can also specify an IP range. Eg. 192.168.1.1 - 192.168.1.10<br>Enter comma separated IPs to add multiple IPs or IP ranges</div><div style="margin-top: 20px;margin-bottom: 15px;font-size: 11px;">You can change this later under Settings -&gt; Security.</div></div></div>';
	var firstStepBtn = '<a class="rep_sprite basic_options_button initialSetupSecurityTab"  style="display:block" refClass="initialSetupUsageStats" id="initialSetupSecurityTabBtn" style="">Continue</a>';

	var secondStep = '<li class="initial_setup_links"><a class="linkDisabled" refClass="initialSetupUsageStats" >Usage stats</a></li>';
	var secondStepContent='<div class="basic_options_details initialSetupUsageStats" style="display: none;"><div class="padding"> <div class="label" style="text-transform:uppercase">Usage data</div><div class="" style="line-height: 22px;padding: 8px 0px;">The usage statistics that you can choose to send will play a very important role In shaping the future of InfiniteWP. The data will not be personally ldentl???able and we will not misuse the data against your interest. We promise.</div><div class="checkbox " style="margin-bottom: 5px;margin-left: -9px;margin-top: -5px;color: #000 !important;position :relative;" id="initialSetupSendAnonymous">Send anonymous usage statistics</div> <div id="initialSetupThankYouMsg" style="margin-top: 0px;margin-left: 22px; display:none">Thank you. We treasure your trust. :)</div><div style="margin-top: 20px;margin-bottom: 15px;font-size: 11px;">You can change this later under Settings -&gt; App.</div></div></div>';
	var secondStepBtn = '<a class="rep_sprite basic_options_button initialSetupUsageStats" style="display:none" refClass="close_pop_up" id="initialSetupUsageStatsBtn" style="">Done. Open the app.</a>';

	var content='<div class="ui-dialog-titlebar ui-widget-header ui-corner-all ui-helper-clearfix"><span class="ui-dialog-title" id="ui-dialog-title-modalDiv">&nbsp;</span><a class="ui-dialog-titlebar-close ui-corner-all" role="button"><span class="ui-icon ui-icon-closethick">close</span></a></div><div id="modalDiv" class="ui-dialog-content ui-widget-content" scrolltop="0" scrollleft="0" style="width: auto; min-height: 104.44px; height: auto;"><div class="dialog_cont create_backup create_backup_sitewise"> <div class="th rep_sprite"> <div class="title droid700">Welcome to ln???niteWP. Let\'s set up a few things to get started.</div> </div>   <div id="addUserOptions" class="backupTab add_user_form" style=""><div class="float-left left" style="padding: 2px 0px 0; width: 19%;margin-left: 0px;">  <div class="th_sub rep_sprite links_bottom_bar"><ul style="margin-left: 0px !important;">'+firstStep+secondStep+'</ul></div> </div> <div class="float-left right" style="padding: 15px 15px 0; width: 76%;">'+firstStepContent+secondStepContent+'</div> <div class="clear-both"></div> </div> <div class="th rep_sprite" style="border-top:1px solid #c6c9ca; height: 35px;"><div class="btn_next_step float-right rep_sprite backupTab next" id="enterUserDetails" style="display: none;">Enter Details<div class="taper"></div></div><div class="btn_action float-right">'+firstStepBtn+secondStepBtn+'</div></div></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',closeOnEscape: false, resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
	
}

function loadUpdateNotificationPopup()
{
	loadUpdateNotificationPopupShown = 1;
	var content='<div class="dialog_cont take_tour" style="width:600px;"> <div class="th rep_sprite"> <div class="title droid700">WELCOME TO THE INFINITEWP BETA PROGRAM</div></div> <div style="padding:20px;"><div style="text-align:center;line-height: 20px;" id="removeSiteCont">Hi. Thanks for being part of our Beta program. In v2.3.0Beta, we introduce the multi-call backup method. This is our attempt to create the world???s most reliable backing up solution on a self-hosted platform. <a style="font-weight: normal; font-size: 12px;" href="'+supportURL+'support/solutions/articles/212262-backup-methods/'+GAHelpTracking+'" target="_blank">See More.</a> We hope to achieve it with your support. So keep the issue reports coming :) <br> <br> We strongly recommend that you update the client plugin on all your WP sites. You will be prompted to update it on your next Reload data.</div><table style="width:320px; margin:20px auto;"><tbody><tr><td><div class="btn_action float-right"><a class="rep_sprite btn_blue closeUpdateNotification cancel" style="color: #6C7277;margin-right: 115px;">Okay. I got it.</a></div></td></tr><tr><td></td><td align="center"></td></tr></tbody></table><table></table></div> <div class="clear-both"></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function loadAddonsPage(data)
{
	if(data==undefined || data=='')
	{
		var tempArray={};
		tempArray['requiredData']={};
		//tempArray['requiredData']['forceCheckUpdate']=1;
		// tempArray['requiredData']['checkIsAddonSuiteMiniLimitExceeded']=1;
		// tempArray['requiredData']['getAddonSuiteMiniLimit']=1;
		// tempArray['requiredData']['getAddonSuiteMiniActivity']=1;
		// tempArray['requiredData']['isAddonSuiteMiniCancelMessage']=1;
		tempArray['requiredData']['checkIsAddonPlanLimitExceeded']=1;
		tempArray['requiredData']['getAddonPlanSiteLimit']=1;
		tempArray['requiredData']['getAddonSuitePlanActivity']=1;
		tempArray['requiredData']['isAddonSuitePlanCancelMessage']=1;
		tempArray['requiredData']["getAddonsPageHTML"]=1;
		tempArray['requiredData']["getAddonsPageHTML"]=1;
		doCall(ajaxCallPath,tempArray,"loadAddonsPage");
	}
	else
	{
		isAddonPlanLimitExceeded = data.data.checkIsAddonPlanLimitExceeded;
		addonPlanSiteLimit = data.data.getAddonPlanSiteLimit;
		addonSuitePlanActivity = data.data.getAddonSuitePlanActivity;
		isAddonSuiteMiniCancelMessage(data.data.isAddonSuitePlanCancelMessage);
		$("#pageContent").html(data.data.getAddonsPageHTML);
	}
	if (iwpTrailPanel) {
		$('.row_valid_till').hide();
		$('.hide_valid_till').hide();
	}
}

function loadIWPPopup(object)
{
	var content = '<div class="dialog_cont steps_container" style="width:402px;"> <div class="th rep_sprite"> <div class="title droid700">LOGIN TO INFINITEWP.COM</div> <a class="cancel rep_sprite_backup">cancel</a></div><div class="dialog connect"><div class="dialog_content inner_cont" style="margin:20px 20px 10px;"><div class="label">USERNAME</div><input name="username" id="username" type="text" class="onEnter" onenterbtn=".loginIWP"><div style="position: relative;"><div class="label">PASSWORD</div><a class="show_password" style="position: absolute;right: 7px;top: 24px;">Show</a><input class="onEnter passwords" onenterbtn=".loginIWP" name="password" id="password" type="password" style="padding: 5px 41px 5px 5px; width: 317px;"><div class="loginError"></div></div></div><div class="th rep_sprite bottom_bar" style="border-bottom: 0;border-top: 1px solid #D2D5D7;height: 35px;"><a class="float-left" style="padding: 10px;" href="'+IWPSiteURL+'lost-your-password/?utm_source=application&utm_medium=userapp&utm_campaign=forgotPassword" target="_blank">Forgot Username or Password?</a><div class="float-right btn_action "><a class="rep_sprite loginIWP" actionvar="'+$(object).attr('actionvar')+'" style="margin:5px; color: #6C7277;">Login</a></div></div></div>	</div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function loadIWPPopupInitalLogin()
{
	var content = '<div class="dialog_cont steps_container" style="width:462px;"> <div class="th rep_sprite"> <div class="title droid700">CONNECTING TO YOUR INFINITEWP.COM ACCOUNT</div> <a class="cancel rep_sprite_backup">cancel</a></div><div class="dialog connect"><div class="dialog_content inner_cont" style="margin:20px 20px 10px;"><div class="label">USERNAME</div><input name="username" id="username" value="'+infinitewpAccountCredentials.siteEmail+'"  type="text" class="onEnter" onenterbtn=".loginIWPAtInitial"><div style="position: relative;"><div class="label">PASSWORD</div><a class="show_password" style="position: absolute;right: 7px;top: 24px;">Show</a><input class="onEnter passwords" onenterbtn=".loginIWPAtInitial" name="password" id="password" value="'+infinitewpAccountCredentials.sitePassword+'" type="password" style="padding: 5px 41px 5px 5px; width: 375px;"><div class="loginError"></div></div></div><div class="th rep_sprite bottom_bar" style="border-bottom: 0;border-top: 1px solid #D2D5D7;height: 35px;"><a class="float-left" style="padding: 10px;" href="'+IWPSiteURL+'lost-your-password/?utm_source=application&utm_medium=userapp&utm_campaign=forgotPassword" target="_blank">Forgot Username or Password?</a><div class="float-right btn_action "><a class="rep_sprite loginIWPAtInitial" actionvar="register" style="margin:5px; color: #6C7277;">Login</a></div></div></div>	</div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:false, position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}


function loadAddToFavourites()
{
	var content = '<div class="dialog_cont steps_container" style="width:402px;"> <div class="th rep_sprite"> <div class="title droid700">ADD TO MY FAVORITES</div> <a class="cancel rep_sprite_backup">cancel</a></div><div class="th_sub rep_sprite"><ul class="th_sub_nav" style="margin-left: 126px;"><li><a class="rep_sprite active" id="uploadZipFavorites">UPLOAD ZIP</a></li><li><a class="rep_sprite" id="uploadURLFavorites">URL</a></li></ul></div><div  class="dialog connect"><div class="dialog_content inner_cont addFavoriteZipDisplaySpace" style="margin:20px 20px 20px;"><div class="label">'+activeItem.slice(0,-1)+' NAME</div><input name="iname" id="iname" type="text" class="onEnter" onenterbtn=".addToFavouritesBtn"><div id="favAlreadyExist" style="display:none; line-height: 18px; color: rgb(169, 42, 42);padding: 3px 5px; border-radius: 3px; border: 1px solid rgb(197, 136, 136); background: rgb(239, 222, 222);margin: -15px -2px 8px 0px;">Plugin already exist. Try something else. </div><div id ="uploadZipFavoritesContent" ><div class="label"> UPLOAD .ZIP FILE</div><div id="uploadFavouriteThemesAndPlugins"></div><div id="uploadZipRequiredError" style="display:none; line-height: 18px; color: rgb(169, 42, 42); padding: 3px 5px; border-radius: 3px; border: 1px solid rgb(197, 136, 136); background: rgb(239, 222, 222);">Please upload a zip file</div></div>      <div id ="uploadURLFavoritesContent" style="display:none"><div class="label">'+activeItem.slice(0,-1)+' URL</div><input class="onEnter" onenterbtn=".addToFavouritesBtn" name="dlink" id="dlink" type="text"></div>    </div><div class="th rep_sprite bottom_bar" style="border-bottom: 0;border-top: 1px solid #D2D5D7;height: 35px;"><div class="float-right btn_action "><a class="rep_sprite addToFavouritesBtn" utype="'+activeItem+'" style="margin:5px; color: #6C7277;">Add to My Favorites</a></div></div></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }}); 
	uploadFavouriteThemesAndPlugins();
}

function loadAddFavoritesGroup(){
	var content = '<div class="dialog_cont steps_container" style="width:402px;"> <div class="th rep_sprite"> <div class="title droid700">CREATE MY FAVORITES GROUP</div> <a class="cancel rep_sprite_backup">cancel</a></div><div  class="dialog connect"><div class="dialog_content inner_cont" style="margin:20px 20px 20px;"><div class="label">GROUP NAME</div><input name="iname" id="gname" type="text" class="onEnter" style="margin-bottom:10px"></div> <div id="favAlreadyExist" style="line-height: 18px; color: rgb(169, 42, 42); padding: 3px 5px;width: 353px; position: absolute; margin: -33px 0px 0px 20px; border-radius: 3px; border: 1px solid rgb(197, 136, 136); background: rgb(239, 222, 222); display:none">Group already exist.Try something else.</div> </div><div class="th rep_sprite bottom_bar" style="border-bottom: 0;border-top: 1px solid #D2D5D7;height: 35px;"><div class="float-right btn_action "><a class="rep_sprite addToFavouritesGroupBtn"  style="margin:5px; color: #6C7277;">Create Group</a></div></div></div></div>';
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center',resizable: false, open: function(event, ui) {bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }}); 
}

function loadConfirmationPopup(object) {
	var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	tempConfirmObject = object;
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(e).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
	
}

function loadConfirmationJsonNotifyPopup(object) {
	var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="background-color: #EFDEDE;border-left: 2px solid #8A1010;padding: 10px; margin: 10px;line-height: 18px;">Note: The iwp client plugin on this site is the latest version. If you restore it to older than 1.6.2, then your client plugin connection could break. If it does, please try reconnecting it.</div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	tempConfirmObject = object;
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(e).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
	
}

function loadUpdateConfirmationPopup(object, view) {
	if (view  == 'sites') {
		var e = '<div class="dialog_cont take_tour" style="width: 435px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_wp_all" ></div><div class="icon_plugin_all" style="position: absolute;margin: -23px 0px 0px 77px;"></div><div class="icon_theme_all" style="position: absolute;margin: -23px 0px 0px 159px;"></div><div class="icon_translation_all" style="position: absolute;margin: -23px 0px 0px 233px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;width: 40px;height: 10px;float: left;text-align: center;">'+totalCoreUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;">'+totalPluginUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 50px;height: 10px;float: left;text-align: center;">'+totalThemeUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 128px;height: 10px;float: left;text-align: center;">'+totalTranslationUpdateCount+'</span></div><table style="width:360px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	}else if(view == 'plugins'){
		var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_plugin_all" style="margin: 20px 0px 0px 77px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;margin-left: 38px;">'+pluginsUpdateCount+'</span></div><table style="width:320px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';

	}else if(view == 'core'){
		var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_wp_all" style="margin: 20px 0px 0px 77px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;margin-left: 44px;">'+WPUpdateCount+'</span></div><table style="width:320px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';

	}else if(view == 'themes'){
		var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_theme_all" style="margin: 20px 0px 0px 77px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;margin-left: 38px;">'+themesUpdateCount+'</span></div><table style="width:320px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';

	}else if(view == 'translations'){
		var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_translation_all" style="margin: 20px 0px 0px 77px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;position: relative;width: 128px;height: 10px;float: left;text-align: center;margin-left: 38px;">'+transUpdateCount+'</span></div><table style="width:320px; margin:122px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';

	}else if (view  == 'WPVulns') {
		var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_wp_all" ></div><div class="icon_plugin_all" style="position: absolute;margin: -23px 0px 0px 77px;"></div><div class="icon_theme_all" style="position: absolute;margin: -23px 0px 0px 159px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;width: 40px;height: 10px;float: left;text-align: center;">'+WPVulnsWPUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;">'+WPVulnsPluginsUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 50px;height: 10px;float: left;text-align: center;">'+WPVulnsThemesUpdateCount+'</span></div><table style="width:320px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	}else if (view  == 'hiddenUpdates') {
		var e = '<div class="dialog_cont take_tour" style="width: 435px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to proceed with this?</div><div style="margin: 20px 0px 0px 68px;"><div class="icon_wp_all" ></div><div class="icon_plugin_all" style="position: absolute;margin: -23px 0px 0px 77px;"></div><div class="icon_theme_all" style="position: absolute;margin: -23px 0px 0px 159px;"></div><div class="icon_translation_all" style="position: absolute;margin: -23px 0px 0px 233px;"></div></div><div style="margin: 20px 0px 0px 59px;"><span style="font-size: 24px;width: 40px;height: 10px;float: left;text-align: center;">'+hiddenWPUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 108px;height: 10px;float: left;text-align: center;">'+hiddenPluginsUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 50px;height: 10px;float: left;text-align: center;">'+hiddenThemesUpdateCount+'</span><span style="font-size: 24px;position: relative;width: 128px;height: 10px;float: left;text-align: center;">'+hiddenTransUpdateCount+'</span></div><table style="width:360px; margin:78px auto 0px 0px;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirmAction" style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	}
	tempConfirmObject = object;
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(e).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
	
}

function installNotInstallCallback(data)
{
	data = data.data.installNotInstalledPlugin;
	var tempArray={};
	tempArray['args']={};
	tempArray['args']['params']={};
	tempArray['args']['params'][activeItem]={};
	tempArray['args']['siteIDs']={};
	var params={};
	tempArray['action']="install"+activeItem.toTitleCase();
	tempArray['args']['siteIDs'][0] = data[1];
	tempArray['args']['params'][activeItem][0] = data[0].download_link;
	
	doHistoryCall(ajaxCallPath,tempArray);
	notInstalledSiteID = 0;

}
/*function processGoogleServicesForm (data) {
	var mainData=data;
	//data=data.data.updateAccountSettings;
	//$('#save_before_grant').click();
	$("#googleSaveSettingsBtn").removeClass('disabled');
	$(".settings_cont .btn_loadingDiv").remove();
	if(data.data.googleServicesSaveAPIKeys == true){
		$("#googleSaveSuccess").show();
		setTimeout(function () {	$("#settings_cont").hide(); $("#googleSaveSuccess").hide();},1000);
		$("#settings_btn").removeClass('active');
	}
}
*/

function loadGoogleServicesAPIKeys(data)
{
	var grantMe = data.data.googleServicesGetAPIKeys ;
	if((grantMe != null)&&(typeof grantMe != 'undefined'))
	{
		settingsData['data']['getSettingsAll']['settings']['google'] = {};
		settingsData['data']['getSettingsAll']['settings']['google']['clientID'] = grantMe.clientID;
		settingsData['data']['getSettingsAll']['settings']['google']['clientSecretKey'] = grantMe.clientSecretKey;
	}
}

function getUpdateOnlyForBeta(data)
{	

	if(data.data.getClientUpdateAvailableSiteIDs != undefined  && data.data.getClientUpdateAvailableSiteIDs.siteIDs != undefined){
		clientUpdatesAvailable =  data.data.getClientUpdateAvailableSiteIDs;
		clientPluginUpdatesNotification(data.data.getClientUpdateAvailableSiteIDs.siteIDs);
	}
	else if (clientUpdatesAvailable != false) {

		clientPluginUpdatesNotification(clientUpdatesAvailable.siteIDs);
	}

	if(data.data.getClientUpdateAvailableSiteIDs!=false && notNowUpdate==false)
	{
		processClientUpdate(data.data.getClientUpdateAvailableSiteIDs);
	} 
	else if (clientUpdatesAvailable != false) {
		processClientUpdate(clientUpdatesAvailable);
	
	}
}

function loadReaddSiteModal(siteID){
	var content='';
	content='<div class="dialog_cont readd_site"> <div class="th rep_sprite"> <div class="title droid700">RE-ADD WEBSITE</div> <a class="cancel rep_sprite_readd">cancel</a></div> <div style="padding:20px;"><div style="text-align:center;" id="readdSiteCont">Are you sure you want to re-add this website?<div class="site">'+site[siteID].URL+'</div><div class="readdAuthKey_wrapper form_cont"><div class="tr"> <div class="tl ">Activation Key</div> <div class="td"> <input name="" type="text" id="readdAuthKey" class="onEnter cp_creds" onenterbtn="#readdSiteConfirm"> </div> <div class="clear-both"></div> </div></div></div></div> <div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;" id="readdSiteButtons"><div class="response rep_sprite_readd"></div>  <div class="btn_action float-right"><a class="rep_sprite" id="readdSiteConfirm" sid="'+siteID+'">Re-add site</a></div> </div></div> </div>';
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center'});
}

function loadMaintenaceModal(siteID){
	var content='',onStatus='',offStatus='';
	var maintenance_html = '<!doctype html>\n<html>\n\t<head>\n\t\t<meta charset="UTF-8">\n\t\t<title>Website under maintenance</title>\n\t\t<link href="http://fonts.googleapis.com/css?family=Vollkorn" rel="stylesheet" type="text/css">\n\t</head>\n\t<body>\n\t\t<div style="font-family: \'Vollkorn\', serif; font-size: 50px; text-shadow: 1px 1px 0 rgba(255, 255, 255, 0.8), 2px 2px 2px rgba(0, 0, 0, 0.15)   ; color: #000; text-align:center; margin:150px 0 10px; text-rendering: optimizeLegibility; letter-spacing: -2px;">We will be back soon.</div>\n\t\t<div style="font-family:Gotham, \'Helvetica Neue\', Helvetica, Arial, sans-serif; font-size: 16px; color: #888; text-align:center;">We are updating more awesome content for you.</div>\n\t</body>\n</html>';
	if(parseInt(site[siteID].connectionStatus) == 2){onStatus='active';}else{offStatus='active';}
	content='<div class="dialog_cont maintenance_site"> <div class="th rep_sprite"> <div class="title droid700">MAINTENANCE MODE</div> <a class="cancel rep_sprite_maintenance">cancel</a></div> <div style="padding:20px;"><div style="text-align:center;" id="maintenanceSiteCont"><div class="site">'+site[siteID].URL+'</div><div class="maintenance_html_wrapper form_cont"><div class="tr"> <div class="tl ">Maintenance Mode</div> <div class="td"> <div class="c_radio maintenanceRadio '+onStatus+'" val="1">On</div><div class="c_radio maintenanceRadio '+offStatus+'" val="0">Off</div> </div> <div class="clear-both"></div> </div><div class="tr"> <div class="tl ">Maintenance HTML</div> <div class="td"> <textarea name="" type="text" id="maintenanceHTML" class="onEnter" onenterbtn="#maintenanceSiteConfirm">'+maintenance_html+'</textarea> </div> <div class="clear-both"></div> </div></div></div></div> <div class="clear-both"></div> <div class="th_sub rep_sprite" style="border-top:1px solid #c6c9ca;" id="maintenanceSiteButtons"><div class="response rep_sprite_maintenance"></div>  <div class="btn_action float-right"><a class="rep_sprite" id="maintenanceSiteConfirm" sid="'+siteID+'">Save changes</a></div> </div></div> </div>';
	
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,position: 'center'});
}

function getSettingsContent(cat){
	var content='';
	if(typeof stagingSettings == 'undefined'){
		stagingSettings = '';
	}
	content += '<div class="settings_wrapper">';
		if(currentUserAccessLevel=='admin') {
			content += '<ul class="settings_nav"><li>Account</li><li>App</li><li>Security</li><li>Email Settings</li><li>Cron</li>'+googleSettings+scheduledBackupSettings+cpBrandingSettings+uptimeMonitoringSettings+stagingSettings+WPTCSettings+'<li>App Update</li><li>Connection Method</li></ul>';
			content += '<div class="settings_cont"><div class="th rep_sprite"><div class="title">App Settings</div></div><div class="settings_main_content app_settings settings">';
		} else {
			content += '<ul class="settings_nav"><li>Account</li></ul>';
			content += '<div class="settings_cont"><div class="th rep_sprite"><div class="title">Account Settings</div></div><div class="settings_main_content app_settings settings">';
		}
	
	content += getSpecificSettingsContent(cat);
	content += '</div></div><div class="clear-both"></div></div>';
	return content;
}

function getSpecificSettingsContent(cat){
	var content = '';
	if(cat == 'App'){
		$('.settings_cont').find('.title').html('App Settings');
		var timeZonesHTML = '<option></option>';
		$.each(timeZones,function(tz,tzVal){
			var selection=''
			if(settingsData['data']['getSettingsAll']['settings']['general']['TIMEZONE'] == tz){selection='selected'}
			timeZonesHTML += '<option value="'+tz+'" '+selection+'>'+tzVal+'</option>'
		});
		timeZonesHTML = '<div class="padding"><div class="label" style="padding-bottom:10px;">TimeZone</div><select id="timeZoneSelector">'+timeZonesHTML+'</select></div>';
		var autoDeleteHTML = '<div class="tr"><div id="cls_times" class="float-right"><div class="checkbox cls_time" older="90">90 days</div><div class="checkbox cls_time" older="60">60 days</div><div class="checkbox cls_time" older="30">30 days</div></div><div id="clearLogSchedule" class="checkbox">Auto-delete log data older than </div><div class="clear-both"></div></div>';

		content = '<div class="tr"><div class="padding"><div class="label"> MAX SIMULTANEOUS READ / WRITE REQUESTS PER IP </div><div class="slider_cont"><input type="text" id="amount01" class="value_display" onfocus="this.blur();" /><div class="slider_stroke"><div id="slider-range01"><div class="slider01_calib_cont"><div class="calib"> 30 </div><div class="calib"> 20 </div><div class="calib" style="width: 140px;"> 10 </div><div class="calib" style="width: 123px;"> 1 </div></div></div></div><div class="clear-both"></div></div></div></div><div class="tr"><div class="padding"><div class="label"> MAX SIMULTANEOUS REQUESTS FROM THIS SERVER <span> (IN WHICH IWP IS INSTALLED) </span></div><div class="slider_cont"><input type="text" id="amount02" class="value_display" onfocus="this.blur();" /><div class="slider_stroke slider02"><div id="slider-range02"><div class="slider02_calib_cont"><div class="calib"> 100 </div><div class="calib" style="width: 193px; margin-left: 19px;"> 50 </div><div class="calib" style="width: 175px;"> 1 </div></div></div></div><div class="clear-both"></div></div></div></div><div class="tr"><div class="padding"><div class="label"> TIME DELAY BETWEEN REQUESTS TO WEBSITES ON THE SAME IP <span> (milli-seconds) </span></div><div class="slider_cont"><input type="text" id="amount03" class="value_display" onfocus="this.blur();" /><div class="slider_stroke slider02"><div id="slider-range03"><div class="slider03_calib_cont"><div class="calib"> 1000 </div><div class="calib"> 500 </div><div class="calib" style="width: 196px;"> 0 </div></div></div></div><div class="clear-both"></div></div></div></div><div class="tr">'+timeZonesHTML+'</div><div class="tr" style="display:none"><div class="checkbox float-right disabled autoConCheckBox" id="executeUsingBrowser" style="width: 134px; border-left: 1px solid #E0E0E0; position:relative"> Do not use fsock </div><div class="checkbox active" id="autoSelectConnectionMethod"> Automatically choose the best connection method </div><div class="clear-both"></div></div><div class="tr"><div class="checkbox" id="enableReloadDataPageLoad"> Reload data on page load. </div></div><div class="tr"><div class="checkbox active" id="sendAnonymous"> Send anonymous usage information to improve IWP. </div></div><div class="tr"><div class="checkbox active" id="ipRangeSame"> Consider that the first 3 octets of IPs are from the same server (xxx.xxx.xxx.*) </div></div>'+autoDeleteHTML+'<div class="th_sub rep_sprite" ><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="appSettingsTab">Save Changes</a></div></div>';
	}else if(cat == 'Account'){
		if(currentUserAccessLevel!='admin') {
			var notificationView = 'style="display:none"';
		} else {
			var notificationView = '';
		}
		var WPVulnsOption = '';
		if(typeof isWpVulnsAlert != 'undefined' && !iwpIsEmpty(isWpVulnsAlert) && isWpVulnsAlert == 1){
			WPVulnsOption = '<div class="tl no_text_transform" style="margin-top:4px;"> Notify security <span> updates to </span> </div><div class="td" style="margin-top:3px;"> <ul class="checkbox_cont" id="notifyVulnsUpdate"> <li> <a class="checkbox generalSelect" id="notifyVulns"> Vulnerability Updates </a> </li>  <div style="position: relative; padding: 0px 13px;line-height: 2;opacity:.5" class="onlyVulnsChecked">You will receive an email only when a pending update contains a vulnerability fix.</div><div style="position: relative; padding: 0px 13px;line-height: 2;opacity:.5" class="vulnsAllChecked">Updates that contain vulnerability fixes will be highlighted in red.</div> </ul> </div>'
		}
		$('.settings_cont').find('.title').html('Account Settings <a style="margin-left: 15px;" page="loginLog" class="l1 navLinks">View login log activity</a>');
		content='<div class="acc_settings" style="border:0; padding:10px;"> <div class="tr no_border"> <div class="tl"> EMAIL </div> <div class="td"> <div class="valid_cont" style="float:left"> <input name="" type="text" id="email" class="hidedit triggerSettingsButton emailEdit" value="samplemail@domain.com" style="width: 440px;"> <div class="valid_error" style="padding-bottom:20px; margin-top:-10px; color:#A92A2A; display:none"> </div> </div> <a class="editEmail">Edit</a> </div> <div class="clear-both"></div> </div> <div class="tr no_border"> <div class="tl"></div> <div class="td"> <a id="change_pass_btn" style="  display: inline-block;  margin-bottom: 10px;"> Change password </a> <div class="change_pass_cont" id="changePassContent" style="display:none"> <div class="clear-both"></div> <div class="valid_cont" style="position: relative;"> <a class="show_password" style="position: absolute;right: 48px;top: 8px;">Show</a><input name="" type="text" class="triggerSettingsButton passwords" id="currentPassword" placeholder="Current Password" style="width: 401px;padding: 3px 43px 3px 5px;" /><div class="valid_error" style="padding-bottom:20px; margin-top:-10px; color:#A92A2A; display:none" > </div> </div> <div class="valid_cont" style="position: relative"><a class="show_password" style="position: absolute;right: 48px;top: 8px;">Show</a> <input name="" type="text" id="newPassword" class="triggerSettingsButton passwords" placeholder="New Password" style="width: 401px; padding: 3px 43px 3px 5px;" />  <div class="valid_error" style="padding-bottom:20px; margin-top:-10px; color:#A92A2A; display:none" > </div> </div>  </div> </div> </div> <div class="clear-both"></div> </div> </div> <div class="tr acc_settings '+ notificationView +'"> <div class="padding"> <div class="label" style="margin-bottom: 10px;">EMAIL NOTIFICATIONS<a class="test_mail rep_sprite_backup" id="sendTestEmail"> Send test email </a> </div> <div class="tl no_text_transform" style="margin-top:3px;"> Email me every </div> <div class="td"> <ul class="btn_radio_slelect float-left" style="margin-left:10px;"> <li> <a class="rep_sprite optionSelect emailFrequency" id="emailDaily" def="daily"> Day </a> </li> <li> <a class="rep_sprite optionSelect emailFrequency" id="emailWeekly" def="weekly"> Week </a> </li> <li> <a class="rep_sprite optionSelect emailFrequency" id="emailNever" def="never"> Never </a> </li> </ul> </div>  <div class="tl no_text_transform" style="margin-top:11px;"> Notify about <span> updates to </span> </div> <div class="td" style="margin-top:11px;"> <ul class="checkbox_cont" id="notifyUpdates"> <li> <a class="checkbox generalSelect" id="notifyPlugins"> Plugins </a> </li> <li> <a class="checkbox generalSelect" id="notifyThemes"> Themes </a> </li> <li> <a class="checkbox generalSelect" id="notifyWordpress"> WordPress </a> </li><li> <a class="checkbox generalSelect" id="notifyTranslations"> Translations </a> </li> </ul> </div> <div class="clear-both"></div>'+ WPVulnsOption +' <div class="clear-both"></div><div class="tl no_text_transform" style="width:475px;margin-top:12px;"> <div class="rep_sprite_backup info_icon"> You have to set a cron job for this to work. (suggested timing: every 20 min) </div> <div class="clear-both"></div> <div style="text-align:left; line-height: 20px;"> <span class="droid700" style="white-space: pre; word-wrap: break-word; width: 480px; display: none;"> <input type="text" class="selectOnText" style="width:466px;" readonly="true" value=" '+ APP_PHP_CRON_CMD+' '+ APP_ROOT+'/cron.php &gt;/dev/null 2&gt;&amp;1" /> </span> </div> </div> <div class="clear-both"></div> </div> </div><div class="th_sub rep_sprite" ><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="settingsTab">Save Changes</a></div></div>';
	}else if(cat == 'Cron'){
		var v3CronContent = '';
				if(typeof CRON_FREQUENCYV3 != 'undefined' ){
					v3CronContent = '<div style="color: #737987; font-size: 12px; font-weight: normal; ">'+CRON_FREQUENCYV3+' </div>';
				}
		$('.settings_cont').find('.title').html('Cron Settings');
		 var iwpCronEnabledHTML = '<div class="tr"><div class="rep_sprite_backup info_icon">Set anyone of the following cron jobs</div> </div><div class="tr iwp-cron-opts"> <div class="padding"> <div class="label" >IWP CRON (Recommended) <a id="testIWPCronBtn" style="padding:0; float:right; display:inline; text-transform:none;display:none" target="_blank"> Check status </a> </div> <div class="checkbox iwp-cron-chkbox" style="position:relative;">Enable IWP Cron</div> <div style="color: #737987; font-size: 12px; font-weight: normal; margin-top: 10px;"> You can whitelist IWP Cron Server\'s IP 52.11.79.10 to make sure calls are not restricted. </div> </div></div><div class="tr my-cron-opts"> <div class="padding"> <div class="label"> SET YOUR OWN CRON JOB </div><div class="tl no_text_transform"> <div class="clear-both"></div> <div style="text-align:left; margin-bottom:-10px; position: relative;"> <span class="droid700" style="word-wrap: break-word; width: 480px;"> <div style="position:relative"><div class="form_label notes">Use this cron job command to set at cpanel</div><a id="copyToClipboard" data-clipboard-target=".cron_commend" style="position: absolute; right: 188px; top: 39px;background-color: #ddd;padding: 7px 10px;text-decoration: none;">Copy</a><input type="text" class="selectOnText cron_commend disabled" style="margin-bottom:0;padding: 3px 36px 3px 5px;width: 470px;" value="'+APP_PHP_CRON_CMD+' '+APP_ROOT+'/cron.php &gt;/dev/null 2&gt;&amp;1"><span class="copy_message" style="right: 127px; top: 45px;color: rgb(0, 128, 0); position: absolute;display: none;">Copied :)</span> </div><div style="color: #737987; font-size: 12px; font-weight: normal; margin-top: 10px;">Set the cron to run every 20 min. For scheduled multi-call backups, set the cron to run every 5 min. </div> <div style="color: #737987; font-size: 12px; font-weight: normal; ">'+CRON_FREQUENCY+' </div> </span> </div> </div> <div class="clear-both"></div> </div></div><div class="tr ez-cron-opts"> <div class="padding" style="font-size:12px;"> <div class="label"> USE EASYCRON SERVICE <a href="'+supportURL+'support/solutions/articles/213797'+GAHelpTracking+'" style="padding:0; float:right; display:inline; text-transform:none;" target="_blank"> See how it works </a> </div> <div id="easycronNote" class="notes" ></div> <div class="tl no_text_transform cron_message_area" style="width:475px;"> <div> <div class="form_label"> CONNECT YOUR EASYCRON ACCOUNT  <a href="'+supportURL+'support/solutions/articles/212264-how-to-connect-your-easycron-com-account/'+GAHelpTracking+'" style="padding:0;  display:inline; text-transform:none;margin-left: 10px;" target="_blank"> See instructions </a> </div> <input type="text" class="cronApiToken formVal" id="EasyCronApiToken" style="width: 238px; float:left;"> <div class="btn_action float-left cron_activate"> <a style="margin: 0px 0px 0px 10px;" class="rep_sprite btn_blue" id="cron_activate_btn" href=""> Activate </a> </div> <div class="btn_action float-left cron_deactivate"> <a style="margin: 0px 0px 0px 10px; display: none;" class="rep_sprite btn_blue" id="cron_deactivate_btn" href=""> Deactivate </a> </div> </div> <div id="easycronStat"></div> <div class="clear-both"></div> </div> <div class="clear-both"></div> </div></div>';
		 var nonIWPCronHTML = '<div class="tr"><div class="rep_sprite_backup info_icon">Set anyone of the following cron jobs</div> </div><div class="tr my-cron-opts"> <div class="padding"> <div class="label"> SET YOUR OWN CRON JOB (Recommended) </div><div class="tl no_text_transform"> <div class="clear-both"></div> <div style="text-align:left; margin-bottom:-10px; position: relative;"> <span class="droid700" style="word-wrap: break-word; width: 480px;"> <div style="position:relative"><div class="form_label notes">Use this cron job command to set at cpanel<a href="'+supportURL+'a/solutions/articles/244985/'+GAHelpTracking+'" style="padding:0;  display:inline; text-transform:none;margin-left: 10px;" target="_blank"> See instructions </a></div><a id="copyToClipboard" data-clipboard-target=".cron_commend" style="position: absolute; right: 188px; top: 39px;background-color: #ddd;padding: 7px 10px;text-decoration: none;">Copy</a><input type="text" class="selectOnText cron_commend disabled" style="margin-bottom:0;padding: 3px 36px 3px 5px;width: 470px;" value="'+APP_PHP_CRON_CMD+' '+APP_ROOT+'/cron.php &gt;/dev/null 2&gt;&amp;1"><span class="copy_message" style="right: 127px; top: 45px;color: rgb(0, 128, 0); position: absolute;display: none;">Copied :)</span> </div><div style="color: #737987; font-size: 12px; font-weight: normal; margin-top: 10px;">Set the cron to run every 20 min. For scheduled multi-call backups, set the cron to run every 5 min. </div> <div style="color: #737987; font-size: 12px; font-weight: normal; ">'+CRON_FREQUENCY+' </div>'+v3CronContent+'</span><div class="info_icon" style="left: -10px;top: -10px;font-style: italic;">You should only set the cronjob for the admin panel you are using as the default one.</div> </div> </div> <div class="clear-both"></div> </div> </div> <div class="tr  ez-cron-opts"> <div class="padding" style="font-size:12px;"> <div class="label"> USE EASYCRON SERVICE <a href="'+supportURL+'support/solutions/articles/213797'+GAHelpTracking+'" style="padding:0; float:right; display:inline; text-transform:none;" target="_blank"> See how it works </a> </div> <div id="easycronNote" class="notes" ></div> <div class="tl no_text_transform cron_message_area" style="width:475px;"> <div> <div class="form_label"> CONNECT YOUR EASYCRON ACCOUNT  <a href="'+supportURL+'support/solutions/articles/212264-how-to-connect-your-easycron-com-account/'+GAHelpTracking+'" style="padding:0;  display:inline; text-transform:none;margin-left: 10px;" target="_blank"> See instructions </a> </div> <input type="text" class="cronApiToken formVal" id="EasyCronApiToken" style="width: 238px; float:left;"> <div class="btn_action float-left cron_activate"> <a style="margin: 0px 0px 0px 10px;" class="rep_sprite btn_blue" id="cron_activate_btn" href=""> Activate </a> </div> <div class="btn_action float-left cron_deactivate"> <a style="margin: 0px 0px 0px 10px; display: none;" class="rep_sprite btn_blue" id="cron_deactivate_btn" href=""> Deactivate </a> </div> </div> <div id="easycronStat"></div> <div class="clear-both"></div> </div> <div class="clear-both"></div> </div> </div>';
		  		
		if(iwpCronInvitied){
				content = iwpCronEnabledHTML;	
		}else{
				content = nonIWPCronHTML;
		}
	}else if(cat == 'Google'){
		$('.settings_cont').find('.title').html('Google Settings');
		content = '<div id="clientCreds" class="tr" style="padding: 10px;"><div style="float:left; width:49%; margin-right:2%;"><div class="form_label"> Client ID </div><input name="" type="text" class="half formVal required" id="clientID"/></div><div style="float:left; width:49%"><div class="form_label"> Client Secret </div><input name="" type="text" class="half formVal" id="clientSecretKey"/></div><div class="th_sub" style="box-shadow: 0 0 0;border-bottom: 0;border-top:0"><div class="success rep_sprite_backup float-left" id="googleSaveSuccess" style="display:none"> Saved successfully! </div><div class="btn_action float-right"><a class="rep_sprite btn_blue" id="googleSaveSettingsBtn" page="googleTab"> Save Changes </a></div></div><div class="clear-both"></div></div>'+googleAnalyticsAccess+googleWebMastersAccess+googlePageSpeedAccess+googleSafeBrowsingAccess;
	}else if(cat == 'Client Plugin Branding'){
		$('.settings_cont').find('.title').html('Client Plugin Branding Settings <a style="margin-left: 15px;" href="'+supportURL+'support/solutions/articles/212305-how-will-my-wp-dashboard-look-after-setting-up-the-client-plugin-branding-addon/'+GAHelpTracking+'" target="_blank">See how this works</a>');
		if(typeof client_plugin_branding_content != 'undefined'){
			content = client_plugin_branding_content;
		}else{
			content = '';
		}
				

	}
	else if(cat == 'Staging'){
		$('.settings_cont').find('.title').html('Staging Ftp and DB Settings');
		if(typeof staging_settings_content != 'undefined'){
			content = staging_settings_content;
		}else{
			content = '';
		}
	}
	else if(cat == 'Uptime Monitor'){
		$('.settings_cont').find('.title').html('Uptime Monitor Settings');
		if(typeof uptime_monitor_content != 'undefined'){
			content = uptime_monitor_content;
		}else{
			content = '';
		}

	}else if(cat == 'Schedule Backups'){
		$('.settings_cont').find('.title').html('Backups Settings');
		if(typeof scheduledBackupSettingsContent != 'undefined'){
			content = scheduledBackupSettingsContent;
		}else{
			content = '';
		}

	}else if(cat == 'App Update'){
		$('.settings_cont').find('.title').html('App Update Settings');
		content = '<div id="completeForm" class="settings settingsItem ftp" id="FTPTab" style="border: 0px; padding: 0px; position:relative; "> <div class="inner_cont"> <div class="app_update_cont"><div class="tr"><div class="padding" style="font-size:12px;padding-bottom:0px;"><div class="label">TEMP DIRECTORY PERMISSION</div><div class="notes">This directory will be used as a temporary directory while updating the panel and installing/updating the addons.</div><div class="updates_folder"></div></div></div><div class="tr"><div class="padding" style="font-size:12px;padding-bottom:0px;"><div class="label">UPLOADS DIRECTORY PERMISSION</div><div class="notes">This directory will be used to store uploaded files and some files created by the panel.</div><div class="uploads_folder"></div></div></div><div class="tr"> <div class="padding fs_method" style="font-size:12px;padding-bottom:0px;"><div class="label" style="margin-bottom:10px;">FILESYSTEM TYPE</div><div class="fs_config" style="line-height: 22px;background-color: #e6ecef;padding: 5px 10px;margin: 10px 0;border-radius: 5px;font-weight: 700">Remove FS_METHOD constant manually from config file to access file system type.</div><div class="c_radio app_update_radio_select directMethod " style="">Direct (Default)</div><div class="c_radio app_update_radio_select ftpMethod" style="">FTP</div> <div class="clear-both"></div><div class="direct_texts textForHideAppUpdate notes">If this method is chosen, it will try to update using direct filesystem which is the default method of updating.</div><div class="clear-both"></div><div class="FTPtexts textForHideAppUpdate notes" >Your IWP Panel FTP credentials will be used for upgrades when the files can not be accessed directly</div><div class="FTP_form"><div class="ftpconfig" style="line-height: 22px;background-color: #e6ecef;padding: 5px 10px;margin: 10px 0;border-radius: 5px;font-weight: 700">We have pre-filled the FTP data from the config.php file. Once you hit Save Changes here, we will remove the data from the config.php file and then you will be able to edit it here.</div> <div class="ftpconfig_e" style="line-height: 22px;background-color: #e6ecef;padding: 5px 10px;margin: 10px 0;border-radius: 5px;font-weight: 700">We have pre-filled the FTP data from the config.php file. Once you hit Save Changes, we will save it here. But we are not able to remove the FTP details from the config.php. Please remove it manually. This is important since these data in the config file will over-ride the details saved here.</div><div class = "FTP_form_con" style="    margin-bottom: 35px;""><div class="tl no_text_transform"><div class="ftp_details_wrapper"><div class="form_label">FTP HOST</div><input type="text" class="required formVal" id="FTPHost" ></div><div class="ftp_details_wrapper"><div class="form_label">FTP PORT</div><input type="text" class="required formVal" id="FTPPort" value="21"></div><div class="ftp_details_wrapper"><div class="form_label">FTP BASE</div><input type="text" class="required formVal"  id="FTPBase" ></div><div class="ftp_details_wrapper"><div class="form_label">FTP USER</div><input type="text" class="required formVal"  id="FTPUser" ></div><div class="ftp_details_wrapper" style="position: relative;"><div class="form_label">FTP PASS</div><a class="show_password" style="position: absolute;right: 25px;top: 39px;">Show</a><input type="password" class="formVal passwords"  id="FTPPass" style="padding: 3px 41px 3px 5px;width: 166px;" ></div> <div class="ftp_details_wrapper"><div class="form_label">Connection Type</div><div class="c_radio FTPConnectionType" id="enableFTP" style="margin-left:-10px;"> FTP </div><div class="c_radio FTPConnectionType" id="enableFTPSSL"> FTP SSL </div><div class="c_radio FTPConnectionType" id="enableSFTP"> SFTP</div></div><div class=" ftp_details_wrapper ftp_form_key" style="width: 103%;display:none;"><div class="form_label">KEY</div><textarea type="text" id="ftp_key" class="formVal fileTreeVal required" style="width: 432px;height: 100px;"></textarea></div></div><div class="ftp_details_wrapper" style="position:  absolute;float:  none;left: 471px;top: 216px;"> <div class="checkbox generalSelect label " id="FTPPasv" style="margin-left: -10px;margin-bottom:20px;"><div class="form_label" style="margin: -6px; margin-top:-14px;">Use passive mode</div></div></div><div class="clear-both"></div> </div></div> </div> <div> </div></div> <div class="th_sub rep_sprite"><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="appUpdateTab">Save Changes</a></div><div class="test_conn_cont float-right">	 <div class="test_conn" id="testFTPConnection">Test connection</div></div></div>';	
	}else if(cat == 'WP Time Capsule'){
		$('.settings_cont').find('.title').html('WP Time Capsule Settings');
		if(typeof WPTCSettingsContent != 'undefined'){
			content = WPTCSettingsContent;
		}else{
			content = '';
		}
	}
	else if(cat == 'Security'){
		$('.settings_cont').find('.title').html('Security Settings');
		var httpsSystemURL = systemURL.replace('http://', "https://");
		var securityHTML = '<div class="tr"><div class="padding ip"><div class="left" id="IPContent"><div class="label"> ALLOW ACCOUNT ACCESS FROM THESE IPs ONLY </div><div class="IPNotes notes">You can either add a specific IP or a range of IP addresses ( e.g <span style="font-weight: bolder;">203.0.113.*</span> and <span style="font-weight: bolder;">203.0.113.10 - 203.0.113.30</span> )</div><div class="right">Your current IP is <span class="droid700"> '+IP+' </span><input name="" type="text" class="add_ip float-left" placeholder="xxx.xxx.xxx.xxx" id="tempIP"  style="color:#ccc;" ><div class="btn_add_ip rep_sprite float-left user_select_no" id="addIP"> Add IP </div></div></div><div class="clear-both"></div></div></div>';
		securityHTML+='<div class="tr"><div class="padding"><div class="label"> HTTP AUTHENTICATION / FOLDER PROTECTION <a href="'+supportURL+'support/solutions/articles/213799'+GAHelpTracking+'" style="padding:0; float:right; display:inline; text-transform:none;" target="_blank">Setup instructions</a></div><div class="notes">Enter your pre-configured Folder Protection credentials <a href="'+supportURL+'solution/articles/213798'+GAHelpTracking+'" style="padding:0;  margin-left: 10px;display:inline; text-transform:none;position: absolute;" target="_blank">Why is this needed?</a></div><table border="0" class="http_auth"><tr><td align="left"><input name="" id="authUsername" type="text"   placeholder="username" /></td><td align="right" style="position:relative"><a class="show_password" style="position: absolute;right: 6px;top: 6px;">Show</a><input name="" class="passwords" style="padding: 3px 41px 3px 5px;width: 187px;" id="authPassword"  autocomplete="new-password" type="password"   placeholder="password" /></td></tr></table></div></div>';
		securityHTML+='<div class="tr disableClass"> <div class="padding"> <div class="label" style="margin-bottom: 10px"> Two-factor authentication </div><div class="2FAConfig" style="line-height: 22px;background-color: #e6ecef;padding: 5px 10px;margin: 10px;border-radius: 5px;font-weight: 700;">Remove DISABLE_2FA constant manually from config file to regain access.</div> <div id= "2FAConfig"> <div id="authNone" class="c_radio loginAuthType active" style="margin-left: -10px">Off</div> <div id="authBasic" class="c_radio loginAuthType">Email Authentication</div>';
		if(typeof duoSecurityAddonFlag!='undefined' && duoSecurityAddonFlag==1){
			securityHTML+=duoSecurityRadio;
		}
				securityHTML+='<div style="line-height:17px" id="loingTypeContent"></div></div></div> </div>';
				
		securityHTML+='<div class="tr" style="position:relative"><div class="HTTPSConfig" style="line-height: 22px;background-color: #e6ecef;padding: 5px 10px;margin: 15px;border-radius: 5px;font-weight: 700;">Remove APP_HTTPS constant manually from config file to regain access.</div><div id="HTTPSConfig"> <div class="checkbox" id="enableHTTPS" style="width: 78px">Enable HTTPS </div><div class="HTTPSConfigCSS" style="left: 120px; position: absolute; top: 10px;">(Make sure this link - <a href="'+httpsSystemURL+'" target="_blank" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: inline-block; max-width: 299px;margin-bottom: -2px;">'+httpsSystemURL+'</a> works before enabling HTTPS)</div></div></div><div class="tr"> <div class="checkbox" id="enableSSLVerify" style="/* width: 78px */">Enable SSL Verification </div></div>';
		content = '<div class="settings timeZoneItem security" id="securityTab" style="border: 0px; padding: 0px; "><div class="securityConfiguringItem " >'+securityHTML+'</div> <div class="th_sub rep_sprite" ><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="securitySettingsTab">Save Changes</a></div></div>';
	}

	if(cat == 'Email Settings'){
		$('.settings_cont').find('.title').html('Email Settings');
		var content = '<div class="settings settingsItem email_settings" id="emailTab" style="border: 0px; padding: 0px; "><div class="tr"><div class="padding" style="font-size:12px;padding-bottom:0px;"><div class="tl no_text_transform"><div class="valid_cont ftp_details_wrapper smtp_from_email"><div class="form_label">FROM EMAIL</div><div style="font-size: 11px; line-height: 1.5em; margin-bottom: 8px; padding-right: 5px;"><span style="word-break: break-all;">Default: <strong>'+fromEmailDetails+'</strong>.</span> You can change it below. </div><input class="formVal required" name="" type="text" id="fromEmail" placeholder="samplemail@example.com"><div style="font-size: 11px; line-height: 1.5em; margin-bottom: 8px; padding-right: 5px;">To use the default From email, empty this field and Save Changes.</div><div class="valid_error" style="top: 16px; height: 14px; right: 37px; display:none;"><div class="padding"></div></div></div><div class="valid_cont ftp_details_wrapper smtp_from_name"><div class="form_label">FROM NAME</div><div style="font-size: 11px; line-height: 1.5em; margin-bottom: 8px; padding-right: 5px;">Default: <strong>InfiniteWP</strong>. You can change it below. </div><input name="" type="text" class="formVal required" id="fromName" placeholder="Name"> <div class="valid_error" style="top: 16px; height: 14px; right: 37px;display:none;"></div></div></div><div class="clear-both"></div></div></div><div class="tr"><div class="padding"><div class="label">SMTP OPTIONS</div><div class="notes">You can send your emails via SMTP instead of relying on the server mail function.</div><div class="checkbox" id="useSmtp" style="position:relative;">Use SMTP to send email</div><div class="ftp_details_wrapper"><br><a href="'+supportURL+'support/solutions/articles/211292-how-to-set-up-smtp-for-gmail/'+GAHelpTracking+'" style="padding:0; float: left; display:inline; text-transform:none;" target="_blank">See instructions</a><br></div><div class="clear-both"></div><div class="ftp_Username_details" style="line-height: 22px;padding: 10px 0px 0;clear: both;">For Gmail - The account email will be used instead of the specified From email.<br>For Yahoo Mail - You have to add the account email as the From email. Only then will it work.</div><div id="smtpProcess" ><div class="valid_cont ftp_details_wrapper"><div class="form_label">SMTP Host</div><input class="formVal required" name="" type="text" id="smtpHost" placeholder="Hostname"> <div class="valid_error" style="top: 16px; height: 14px; right: 37px;display:none;"><div class="padding"></div></div></div><div class="ftp_details_wrapper"><div class="form_label">SMTP Port</div><input name="" class="formVal required" type="text" id="smtpPort" placeholder="25" value="25"><div class="valid_error" style="display:none;top: 16px; height: 14px; right: 37px;"><div class="padding"></div> </div></div><div class="ftp_details_wrapper"><div class="form_label">Encryption</div><div class="c_radio emailSet encryptionType" id="noEncryption" style="margin-left:-10px;">No</div><div class="c_radio emailSet encryptionType" id="sslEncryption">SSL</div><div class="c_radio emailSet active encryptionType" id="tlsEncryption">TLS</div></div><div class="clear-both"></div><div class="ftp_details_wrapper"><div class="form_label">Username/Email</div><input class="formVal required" name="" type="text" id="smtpAuthUsername" placeholder="Username/Email"> <div class="valid_error" style="display:none;top: 16px; height: 14px; right: 37px;"><div class="padding"></div> </div></div><div class="ftp_details_wrapper" style="position: relative;"><div class="form_label">Password</div><a class="show_password" style="position: absolute;right: 25px;top: 38px;">Show</a><input class="formVal required passwords" name="" type="password" id="smtpAuthPassword"  autocomplete="new-password" placeholder="Password" style="padding: 3px 45px 3px 5px;width: 160px;"> <div class="valid_error" style="display:none;top: 16px; height: 14px; right: 37px;"><div class="padding"></div></div></div><div class="ftp_details_wrapper"><div class="form_label">Authentication</div><div class="c_radio emailSet smtpAuth" id="noSmtpAuth" def="no" style="margin-left: -10px;">No</div><div class="c_radio active emailSet smtpAuth" id="yesSmtpAuth" def="yes">Yes</div></div></div></div><div class="btn_action float-left test_send_mail_smtp"><a  class="rep_sprite btn_blue" id="" href="">Send Test Email</a> </div><div class="clear-both"></div></div></div></div><div class="th_sub rep_sprite"><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="mailSettingsTab">Save Changes</a></div></div>';
	}
        
        if(cat == 'Connection Method'){
            $('.settings_cont').find('.title').html('Connection Method');
            var chooseConnMethod = '<div class="tr"><div class="label" style="float: left;padding: 10px;border-right: 1px solid #E0E0E0;width: 200px;"> Choose connection method </div><div id="connectMethod"><div id="auto" class="c_radio connectionMethod connectionMethodRadio active">Auto</div><div id="manual" class="c_radio connectionMethod connectionMethodRadio">Manual</div></div><div class="clear-both"></div></div>';
            var manualContent = '<div class="tr" id="manualContent" style="display:none"><div class="label" style="float: left;padding: 10px;border-right: 1px solid #E0E0E0;width: 200px;"> Choose mode </div><div id="connectMode"><div id="commandMode" class="c_radio connectionMode connectionModeRadio active">Command mode</div><div id="socketMode" class="c_radio connectionMode connectionModeRadio">Socket mode</div><div id="curlMode" class="c_radio connectionMode connectionModeRadio">Curl mode</div></div><div class="clear-both"></div></div>';
            var cmdRunnerContent = '<div class="tr" id="cmdRunnerContent" style="display:none"><div class="label" style="float: left;padding: 10px;border-right: 1px solid #E0E0E0;width: 200px;"> Choose Runner </div><div id="cmdRunner"><div id="cmdRunnerAuto" class="c_radio connectionRunner connectionRunnerRadio active">Auto</div><div id="cmdRunnerExec" class="c_radio connectionRunner connectionRunnerRadio">Exec</div><div id="cmdRunnerPassthru" class="c_radio connectionRunner connectionRunnerRadio">Passthru</div><div id="cmdRunnerShellexec" class="c_radio connectionRunner connectionRunnerRadio">ShellExec</div><div id="cmdRunnerSystem" class="c_radio connectionRunner connectionRunnerRadio">System</div></div><div class="clear-both"></div></div>';
            var sockRunnerContent = '<div class="tr" id="sockRunnerContent" style="display:none"><div class="label" style="float: left;padding: 10px;border-right: 1px solid #E0E0E0;width: 200px;"> Choose Runner </div><div id="sockRunner"><div id="sockRunnerAuto" class="c_radio connectionRunner connectionRunnerRadio active">Auto</div><div id="sockRunnerStream" class="c_radio connectionRunner connectionRunnerRadio">Stream</div><div id="sockRunnerFsock" class="c_radio connectionRunner connectionRunnerRadio">Fsock</div><div id="sockRunnerSocket" class="c_radio connectionRunner connectionRunnerRadio">Socket connect</div></div><div class="clear-both"></div></div>';
            var saveChanges = '<div class="th_sub rep_sprite" ><div class="success rep_sprite_backup float-left" id="saveSuccess" style="display:none">Saved successfully!</div><div class="btn_action float-right"><a class="rep_sprite" id="saveSettingsBtn" page="connectionMethod">Save Changes</a></div><a id="connectionHelp" style="margin-left: 15px;float:right;margin-top: 11px;margin-right: 25px;display: none;" href="'+supportURL+'solution/articles/226079-connection-methods" target="_blank" class="">Why are some of my options disabled?</a>'
            content = '<div class="settings" style="border: 0px; padding: 0px; "><div class="inner_cont"><div class="connection_method_cont">'+chooseConnMethod+manualContent+cmdRunnerContent+sockRunnerContent+saveChanges+'</div></div></div>';
        }
	
	return content;
}

function removeLogConfirmationPopup(clearWhat,actionID) {
	var actionIDvar = '';
	var confMsg = '';
	if(clearWhat == 'uncomplete'){
		confMsg = 'Are you sure you want to kill the uncompleted tasks?<br>Note: Already-initiated tasks will be marked as "Killed by user" although any response will be updated. We\'ll kill the rest.';
	}else if(clearWhat == 'searchList' || clearWhat == 'settingClearLog'){
		if(typeof isClientReport!='undefined' && isClientReport){
			confMsg = 'Clearing these logs will affect your client reports. <br>Do you still want to clear them?';
		}else{
			confMsg = 'Are you sure you want to clear the logs?';
		}
	}else if(clearWhat == 'singleAct'){
		if(typeof actionID !='undefined'){
			if(typeof isClientReport!='undefined' && isClientReport){
				confMsg = 'Clearing these logs will affect your client reports. <br>Do you still want to clear them?';
			}else{
				confMsg = 'Are you sure you want to clear this activity?';
			}
			actionIDvar = 'actionid='+actionID;
		}
	}
	var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div><a class="cancel rep_sprite_clear_task"></a></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;">'+confMsg+'</div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel_clear_log" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirm_clear_log" what="'+clearWhat+'" '+actionIDvar+' style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	// tempConfirmObject = object;
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(e).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
	
}

function setIWPCronActivate(){

}

function killTaskConfirmationPopup(clearWhat,taskID,multicall){
	if(typeof multicall == 'undefined') multicall = 0;
	if(multicall) {
		multicall = ' multicall';
	}else{
		multicall = '';
	}
	var confMsg = '';
	confMsg = 'Are you sure you want to kill these tasks?<br>Note: Already-initiated tasks will be marked as "Killed by user" although any response will be updated. We\'ll kill the rest.';
	if(clearWhat == 'action'){// multiple action to delete
		confMsg = 'Are you sure you want to kill these  tasks?<br>Note: Already-initiated tasks will be marked as "Killed by user" although any response will be updated. We\'ll kill the rest.';
	}else if(clearWhat == 'history'){// single action to delete
		confMsg = 'Are you sure you want to kill this task?';
	}
	var taskIDvar = 'taskID = '+taskID;
	var e = '<div class="dialog_cont take_tour" style="width: 360px;"> <div class="th rep_sprite"> <div class="title droid700">ARE YOU SURE?</div><a class="cancel rep_sprite_clear_task"></a></div> <div style="padding:20px;"><div style="text-align:center; line-height: 22px;">'+confMsg+'</div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel_kill_task" style="color: #6C7277;">No! Don\'t.</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue confirm_kill_task'+multicall+'" what="'+clearWhat+'" '+taskIDvar+' style="color: #6C7277;  cursor:pointer;">Yes! Go ahead.</a></div></td></tr></table></div> <div class="clear-both"></div></div>';
	// tempConfirmObject = object;
	$("#modalDiv").dialog("destroy");
	$("#modalDiv").html(e).dialog({ width: "auto",modal: true,position: "center",resizable: false,open: function (e, t) {bottomToolBarHide()},close: function (e, t) {bottomToolBarShow()} }); 
	
}

function doCheckDoCallAndHistoryCallForMini(request,data) {
	if(request!=undefined && request.data!=undefined) {
		if(request.data.isAddonSuiteLimitExceededAttempt!=undefined && request.data.isAddonSuiteLimitExceededAttempt && isAddonSuiteLimitExceededAttempt==0 && globalMessageFlagForMini!=undefined && globalMessageFlagForMini==0 && data.action!=undefined && (data.action=='addSite' || data.action=='installCloneCommonNewSite')) {
			isAddonSuiteLimitExceededAttempt=1;
			//addonSuiteMiniLimitExceeded('');	
		}
		if(request.data.checkIsMiniExpired!=undefined) isMiniExpired=request.data.checkIsMiniExpired;
		if(request.data.getCurrentTimestamp!=undefined) currentTimestamp=request.data.getCurrentTimestamp;
	}
}
function processReSchedulePopup(){
	$("#modalDiv").dialog("close");
	isShowReSchedulePopup = 0;
}

function validateEmail(email) {
	var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	return re.test(email);
}
function getHiddenPropertyCount(obj){
	var count = 0;
	$.each(obj, function(k, v){
		if (v.hiddenItem == true) {
			count ++;
		}
	});
	return count;


}
function updateViewDropDown(){
	$(".update_view_dropdown").select2("destroy");
	$(".update_view_dropdown").select2({  
		minimumResultsForSearch: -1,
		formatResult: format,
		formatSelection: format,
		dropdownCssClass : 'no-search',
		customClass : 'select2-drop-active',
		escapeMarkup : function(text){
			textCopy = text.split("~");
			if (textCopy[1]!=undefined) { 
				if (textCopy[0] == 'Only Hidden Updates') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="float: right;" class="countDrop">'+hiddenUpdateCount+'</span><br/>';
				}
				if (textCopy[0].trim() == 'by Plugins') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="padding-left:7.1em;"></span><span style="float: right;" class="countDrop">'+pluginsUpdateCount+'</span><br/>';
				}
				if (textCopy[0].trim() == 'by WP') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="padding-left:9em;"><span style="float: right;" class="countDrop">'+WPUpdateCount+'</span><br/>';
				}
				if (textCopy[0].trim() == 'by Themes') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="padding-left:6.8em;"><span style="float: right;" class="countDrop">'+themesUpdateCount+'</span><br/>';
				}
				if (textCopy[0].trim() == 'Only Translations') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="float: right;" class="countDrop">'+transUpdateCount+'</span><br/>';
				}
				if (!iwpIsEmpty(isWpVulnsAlert) && textCopy[0] == 'Only Vulnerability Updates') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="float: right;" class="countDrop">'+securityUpdateCount+'</span><br/>';
				}
				if (textCopy[0] == 'by Websites') {
					return '<span style="float: left;margin-bottom: 2px;">'+textCopy[0]+'</span><span style="float: right;" class="countDrop">'+textCopy[1]+'</span><br/>';
				}

			}
			else{
				return text;
			}
		}
	});
}

function format(obj) {
	if (!obj.id){
		return obj.text; // optgroup
	}
	else if (obj.id == 'dot'){
		return "<hr class='dotDrop'></hr>" + obj.text;
	}
	else{
		return obj.text;
   }
  }

function showConversionNeededTableNames(data){
	var tableNames = data.data.getConversionNeededTableNames;
	var liElement = '';
	$.each(tableNames , function(k, v) {
		liElement = liElement + '<li>'+v+'</li>';
	});
	var content = '<div class="dialog_cont take_tour" style="width: 486px;"> <div class="th rep_sprite"> <div class="title droid700">UPGRADE DATABASE ENGINE</div></div> <div style="padding:25px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Are you sure you want to upgrade the database engine to InnoDB from MyISAM to optimise performance?</div><div style="border: #f1f2f2 solid 1px;border-radius: 3px;width: 205px;margin-left: 112px;max-height: 200px;overflow: auto;margin-top: 20px;"><ul style="text-align: left;line-height: 2;padding: 16px 0px 16px 27px;">'+liElement+'</ul></div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 40px;"><a class="rep_sprite cancel" style="color: #6C7277;">No! Not Now</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue" style="color: #6C7277;  cursor:pointer;" href="'+systemURL+'updateTables.php?action=InnoDBConversion" id="convertInnoDB">Yes! Go ahead.</a></div></td></tr></table><div style="padding: 15px;text-align: center;line-height: 1.5;"><span style="color: #f44336;"><i class="fa fa-exclamation-triangle" style="font-size: 13px; position: static;padding: 5px;"></i>Important:</span> Before proceeding, we suggest you backup the IWP database, just in case something goes wonky.</div></div> <div class="clear-both"></div></div>'; 
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function processUpdateInPage(data){
	var updateArray = {};
	var updateArray = data.data.updateInPage;
	if (updateArray == false) {
		return false;
	}
	var tempArray={}
	tempArray['action']='updateAll';
	tempArray['args']={};
	tempArray['args']['params']=updateArray;
	doCall(ajaxCallPath,tempArray,'formArrayUpdateAll','json',"none");
}

function processUpdateOverall(data){
	var updateArray = {};
	var updateArray = data.data.updateOverall;
	if (updateArray == false) {
		return false;
	}
	var tempArray={}
	tempArray['action']='updateAll';
	tempArray['args']={};
	tempArray['args']['params']=updateArray;
	doCall(ajaxCallPath,tempArray,'formArrayUpdateAll','json',"none");
}

function disableConnectionMethod(compatibility){
    var command = {
        exec: 'cmdRunnerExec',
        systemexec: 'cmdRunnerSystem',
        passthru: 'cmdRunnerPassthru',
        shellexec: 'cmdRunnerShellexec'
    };
    var socket = {
        stream: 'sockRunnerStream',
        fsock: 'sockRunnerFsock',
        socketconnect: 'sockRunnerSocket'
    };
    var enabledConnections = [];
    var disabledConnections = [];
    var enabledCommands = [];
    var enabledSockets = [];
    $.each(compatibility, function(index, value){
      //Disable manual 
      if(value){
        enabledConnections.push(index);
      }
      
      if(command[index]){
        if(value){
            enabledCommands.push(index);
        }else{
            disableRunner('#'+command[index]);
        }
      }
      
      if(socket[index]){
        if(value){
            enabledSockets.push(index);
        }else{
            disableRunner('#'+socket[index]);
        }
      }
    });
    
    //command not available
    if(enabledCommands.length==0 && enabledSockets.length!=0){
        $('#commandMode').addClass('disabled').removeClass('active');
        addToolTipForConnectionMethod('#commandMode', 'Mode');
    }
    
    //socket not available
    if(enabledSockets.length==0 && enabledCommands.length!=0){
        $('#socketMode').addClass('disabled').removeClass('active');
        addToolTipForConnectionMethod('#socketMode', 'Mode');
    }

     //command and socket not available
    if(enabledConnections.length==0){
        $('#socketMode').addClass('disabled').removeClass('active');
        addToolTipForConnectionMethod('#commandMode', 'Mode')
        $('#commandMode').addClass('disabled').removeClass('active');
        addToolTipForConnectionMethod('#socketMode', 'Mode');
        $('#curlMode').addClass('active').trigger('click');
    }
}

function disableRunner(disabledId){
    $(disabledId).removeClass('active').addClass('disabled');
    addToolTipForConnectionMethod(disabledId, 'Runner');
}

function addToolTipForConnectionMethod(disabledId, textCont){
    $('#connectionHelp').show();
    $(disabledId).qtip({content: { text: 'This '+textCont+' is not available on your server' }, position: { my: 'top center', at: 'bottom center' }, show: { delay: 200 }, hide: { event: 'click mouseleave',inactive: 1000}, style: { classes: 'ui-tooltip-shadow ui-tooltip-tipsy', tip: { corner: true, width: 9, height:4 } } });
}

function getSaveFtpReqVar(siteID){
	var thisReqVar = {};
	
	var checkForm = validateForm("stagingFtpForm");
	if(checkForm != false){
		thisReqVar = checkForm;
	} else {
		return false;
	}
	
	if($('#stagingFtpForm #hostPassive').hasClass("active"))
		var hostPassive = 1;
	else
		var hostPassive = '';
	if($('#stagingFtpForm #hostSSL').hasClass("active"))
		var hostSSL = 1;
	else
		var hostSSL = '';
	if($('#stagingFtpForm #use_ftp').hasClass("active"))
		var use_ftp = 1;
	else
		var use_ftp = '';
	if($('#stagingFtpForm #use_sftp').hasClass("active"))
		var use_sftp = 1;
	else
		var use_sftp = '';
	if($('#stagingFtpForm #update_in_staging_switch').hasClass("active"))
		var update_in_staging_switch = 1;
	else
		var update_in_staging_switch = '';
		
	thisReqVar['hostPassive']=hostPassive;
	thisReqVar['hostSSL']=hostSSL;
	thisReqVar['use_ftp']=use_ftp;
	thisReqVar['use_sftp']=use_sftp;
	thisReqVar['update_in_staging_switch']=update_in_staging_switch;
	
	thisReqVar['siteID'] = siteID;
	thisReqVar['sourceSiteID'] = siteID;
	thisReqVar['toIWP'] = 1;
	
	return thisReqVar;
}

function loadNetworkSiteListsModal(data){
	var sites = data.sites;
	var parentSiteID = data.parentSiteID;
	var liElement = '';
	$.each(sites , function(k, v) {
		liElement = liElement + '<li class ="checkbox parent_site_checkbox active" url="'+v+'"><img class="" data-src="images/custom_wp_favicon.ico" src="images/custom_wp_favicon.ico" width="12" height="12" style="padding-right: 6px;">'+v+'</li>';
	});
	var content = '<div class="dialog_cont take_tour" style="width: 486px;"> <div class="th rep_sprite"> <div class="title droid700">SUBSITE LISTS</div><a class="cancel rep_sprite_backup notNowUpdate">cancel</a></div> <div style="padding:25px;"><div style="text-align:center; line-height: 22px;" id="removeSiteCont">Seems you have added a Network site. Here is the list of subsites which you can add to your admin panel automatically in a click.</div><div style="border: #f1f2f2 solid 1px;border-radius: 3px;width: 405px;margin-left: 12px;max-height: 200px;overflow: auto;margin-top: 20px;"><ul style="text-align: left;line-height: 2;padding: 16px 0px 16px 0px;">'+liElement+'</ul></div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td><td><div class="btn_action float-right" style="margin-right: 65px;"><a class="rep_sprite cancel" style="color: #6C7277;">Later</a></div><div class="btn_action float-right" style="margin-right: 30px; cursor:pointer;"><a class="rep_sprite btn_blue" style="color: #6C7277;  cursor:pointer;" id="addAllNetwork" parentsiteID ='+parentSiteID+'>Add now</a></div><div class="float-right" style="margin-right: 70px;"><a style="font-weight: normal;margin-left: 10px;font-size: 12px;" href="'+supportURL+'support/solutions/articles/195272-how-to-add-a-wordpress-multisite/'+GAHelpTracking+'" target="_blank">For help?</a></div></td></tr></table></div> <div class="clear-both"></div></div>'; 
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});	
}

function doReturnSelectedMultiSites() {
	var siteIDs = new Array();
	$('.parent_site_checkbox.active').each(function(){
		siteIDs.push($(this).attr('url'));
	});
	return(siteIDs);
}

function processAddNetworkSite(data){
	resetVars(data);
	refreshStats(data,1);
	$("#bottom_sites_cont").show();
	$(".showFooterSelector").addClass("pressed");
	var lastKey=getLastKey(data.data.getSites);
	
	//for tweeting
	var this_count = getPropertyCount(data.data.getSites);
	if(this_count >= 3 && threeSitesTweetStatus != "done"){
		showTweetDialog("three_sites");
	}
	
	$("#bottomToolBarSelector #bottom_left .nano").nanoScroller({ scroll: 'bottom' });
	$("#bottomToolBarSelector #bottom_left .bottomSites#s"+lastKey).effect("highlight", {}, 3000);
	if(typeof data.data.getRecentPluginsStatus != 'undefined')
		pluginsStatus = data.data.getRecentPluginsStatus; 
	if(typeof data.data.getRecentThemesStatus != 'undefined')
		themesStatus = data.data.getRecentThemesStatus; 
	if(typeof data.data.stagingGetStagingBaseIDs != 'undefined')
		stagingSites = data.data.stagingGetStagingBaseIDs; 
	clientUpdateNotificationPush(data,1);
}
function checkAndShowWooDBUpdatesCount(){
	var site = 'site';
	if (wooDBUpdateCount > 1) {
		site = 'sites';
	}
	var html = '<div class="wooDbUpdateNotification" style="border-left: 2px solid #AAAAAA; padding: 10px; background-color: #e7e9eb;">WooCommerce Database Update is available on <a id="showWooDbUpdates">  '+wooDBUpdateCount+' '+site+'.</a></div>';

	$(".wooDbUpdateNotification").remove();
	if (wooDBUpdateCount >= 1) {
		$("#pageContent").prepend(html);
	}
}
function showWooDBUpdateSites(data){
	var tableNames = data.data.getWooDBUpdateSites;
	var liElement = '';
	$.each(tableNames , function(k, v) {
		liElement = liElement + '<li><img class="" data-src="images/custom_wp_favicon.ico" src="images/custom_wp_favicon.ico" width="12" height="12" style="padding-right: 6px;"><a class="adminPopout" sid='+k+'>'+v+'</a></li>';
	});
	var content = '<div class="dialog_cont take_tour" style="width: 486px;"> <div class="th rep_sprite"> <div class="title droid700">WooCommerce Database updates</div><a class="cancel rep_sprite_backup notNowUpdate">cancel</a></div> <div style="padding:25px;"><div style="text-align:center; line-height: 22px; font-style: italic;" id="removeSiteCont">*click on the site name to open the wp-admin page to perform DB update.</div><div style="border: #f1f2f2 solid 1px;border-radius: 3px;width: 405px;margin-left: 12px;max-height: 200px;overflow: auto;margin-top: 20px;"><ul style="text-align: left;line-height: 2;padding: 16px 0px 16px 27px;">'+liElement+'</ul></div><table style="width:320px; margin:20px auto;"><tr><td><div class="btn_action float-right"></div></td></tr></table></div> <div class="clear-both"></div></div>'; 
	$("#modalDiv").dialog("destroy");
	$('#modalDiv').html(content).dialog({width:'auto',modal:true,closeOnEscape:true,position: 'center',resizable: false, open: function(event, ui) { bottomToolBarHide(); },close: function(event, ui) {bottomToolBarShow(); }});
}

function removeLastDirectoryPartOf(the_url)
{
    the_url = the_url.trim('');
    the_url = the_url.replace(/\/$/, '');
    return the_url.substring(0, the_url.lastIndexOf('/'))+'/';
}