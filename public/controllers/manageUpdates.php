<?php
/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
class manageUpdates {
	public static function getSiteUpdateStats(&$siteStatsData, $filter = false){
		$arrayKeys = array('upgradable_themes'=> 'updateThemeCounts', 'upgradable_plugins' => 'updatePluginCounts', 'upgradable_translations' => 'isTranslationUpdateAvailable', 'core_updates' => 'isCoreUpdateAvailable', 'premium_updates' => 'updatePluginCounts');
		$stats =  unserialize(base64_decode($siteStatsData['stats']));
		$statsTemp = array();
		foreach ($arrayKeys as $key => $columnName) {
			if (!empty($stats[$key]) && isset($stats[$key])) {
				$filterStats = self::filterHiddenUpdate($siteStatsData['siteID'], $stats[$key], $key);
				if ($key == 'premium_updates') {
					if (!empty($stats['upgradable_plugins']) && is_array($stats['upgradable_plugins']) && !empty($filterStats) && is_array($filterStats)) {
						foreach ($stats['upgradable_plugins'] as $pluginKey => $pluginValue) {
							foreach ($filterStats as $keys => $value) {
								$item =  objectToArray($pluginValue);
								if ($item['file'] == $value['slug']) {
									unset($filterStats[$keys]);
								}
							}
						}
					}
					$siteStatsData[$columnName] += count($filterStats);

				} elseif($key == 'core_updates'){
					if (empty($filterStats)) {
						$siteStatsData[$columnName]  = 0;
					} else{
						$siteStatsData[$columnName]  = 1;
					}
				} elseif ($key == 'upgradable_translations') {
					if ($filterStats == true) {
						$siteStatsData[$columnName]  = 1;
					}else{
						$siteStatsData[$columnName]  = 0;
					}
				} else{
					if (empty($filterStats) || !is_array($filterStats)) {
						$siteStatsData[$columnName]  = 0;
					}else{
						$siteStatsData[$columnName]  = count($filterStats);
					}
				}
				if ($filter) {
					$statsTemp[$key] = $filterStats;
				}
			}
		}
		if ($filter) {
			$siteStatsData['stats'] = $statsTemp;
		}
	}

	public static function getPaginatedSitesStats($args = null, $view, $isRaw = false){
		$getKeyword = " ";
		$where2 = " ";
		$itemsPerPage = 20;
		$keysSearch = ' ';
		if ((!empty($args['searchKey']) && $args['searchKey'] != 1 && $args != '1') && ($view == 'sites' || $view == 'hiddenUpdates')) {
			$keysSearch = " AND S.name LIKE '%".$args['searchKey']."%' ";
		} elseif ($view !='sites' && !empty($args['searchKey']) && $args != '1') {
			$keysSearch = " AND name LIKE '%".$args['searchKey']."%' ";
		}
		$page = (isset($args['page']) && !empty($args['page'])) ? $args['page'] : 1;
		if (function_exists('multiUserGetSitesStatsCount')) {
			$total = multiUserGetSitesStatsCount($view, $args, $keysSearch);
			$limitSQL = paginate($page, $total, $itemsPerPage);
			$sitesStats = multiUserGetPaginatedStats($limitSQL, $view, $keysSearch, $args);
		} else{
			$whereClause = self::generateWhereConditionByView($view);
			$total = DB::getField("?:site_stats", "count(siteID)", $whereClause);
			$limitSQL = paginate($page, $total, $itemsPerPage);
				if ($view = 'sites') {
					$sitesStats = DB::getArray("?:site_stats SS, ?:sites S", "SS.*", "S.siteID = SS.siteID AND S.type = 'normal' AND (".$whereClause.") ".$limitSQL, "siteID");
				} else{
					$sitesStats = DB::getFields("?:site_stats SS, ?:sites S", "SS.siteID", "S.siteID = SS.siteID AND S.type = 'normal' AND (".$whereClause.") ".$limitSQL);
				}
		}

		if ($isRaw) {
			foreach($sitesStats as $siteID => $sitesStat){
				$sitesStats[$siteID]['stats'] = unserialize(base64_decode($sitesStat['stats']));
			}
		}
		
		return $sitesStats;
	}

	public static function getSitesViewUpdatesContent($data){
		$sitesStats = self::getPaginatedSitesStats($data, 'sites');
		$siteViewData = self::prepareSiteViewArray($sitesStats);
		$siteViewHTML = TPL::get('/templates/updates/sitesView.tpl.php', array('siteSummaryData' => $siteViewData, 'view' => 'sites', 'data' => $data));
		$result = array("HTML" => $siteViewHTML, 'data' => $data);
		return $result;
	}

	public static function getPluginsViewUpdatesContent($data){
		$WPVulnsView = array();
		$sitesStats = self::getPaginatedSitesStats($data, 'plugin');
		if (function_exists('WPVulnsGetPTCVulnsUpdate')) {
			$WPVulnsView = WPVulnsGetPTCVulnsUpdate('pluginsView');
		}
		$pluginViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteSummaryData' => $sitesStats, 'view' => 'plugins', 'WPVulns' => $WPVulnsView, 'data' => $data));
		$result = array("HTML" => $pluginViewHTML, 'data' => $data);
		return $result;
	}

	public static function getThemesViewUpdatesContent($data){
		$WPVulnsView = array();
		$sitesStats = self::getPaginatedSitesStats($data, 'theme');
		if (function_exists('WPVulnsGetPTCVulnsUpdate')) {
			$WPVulnsView = WPVulnsGetPTCVulnsUpdate('themesView');
		}
		$themeViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteSummaryData' => $sitesStats, 'view' => 'themes', 'WPVulns' => $WPVulnsView , 'data' => $data));
		$result = array("HTML" => $themeViewHTML, 'data' => $data);
		return $result;
	}

	public static function getWPViewUpdatesContent($data){
		$WPVulnsView = array();
		$sitesStats = self::getPaginatedSitesStats($data, 'core');
		if (function_exists('WPVulnsGetPTCVulnsUpdate')) {
			$WPVulnsView = WPVulnsGetPTCVulnsUpdate('coreView');
		}
		$WPViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteSummaryData' => $sitesStats, 'view' => 'core', 'WPVulns' => $WPVulnsView, 'data' => $data));
		$result = array("HTML" => $WPViewHTML, 'data' => $data);
		return $result;
	}

	public static function getHiddenViewUpdatesContent($data){
		$siteIDs = self::getPaginatedSitesStats($data, 'hiddenUpdates');
		$hiddenUpdates = false;
		if (!empty($siteIDs)) {
			if (function_exists('WPVulnsGetVulnsUpdate')) {
				$sitesStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], '', $siteIDs, true);
			} 
			if(empty($sitesStats)){
				$sitesStats = panelRequestManager::getSitesUpdates('','',$siteIDs);
			}
			if (!empty($sitesStats['siteView'])) {
				$hiddenUpdates = self::prepareHiddenViewArray($sitesStats['siteView']);
			}
		}		
		$siteViewHTML = TPL::get('/templates/updates/sitesView.tpl.php', array('siteSummaryData' => $hiddenUpdates, 'view' => 'hiddenUpdates', 'data' => $data));
		$result = array("HTML" => $siteViewHTML, 'data' => $data);
		return $result;

	}

	public static function getTranslationViewUpdatesContent($data){
		$siteIDs= DB::getFields("?:site_stats", "siteID", 'isTranslationUpdateAvailable != 0'); 
		$sitesStats = panelRequestManager::getSitesUpdates('', '', $siteIDs);
		$translations = array();
		if (!empty($sitesStats) && !empty($sitesStats['translationsView']['translations'])) {
			foreach ($sitesStats['translationsView']['translations'] as $key => $value) {
				foreach ($value as $siteID => $data) {
					if ($data['hiddenItem']) {
						unset($sitesStats['translationsView']['translations'][$siteID]);
					}
				}
			}
			$translations = $sitesStats['translationsView']['translations'];
		}
		$WPViewHTML = TPL::get('/templates/updates/translationsView.tpl.php', array('siteData' => $translations, 'view' => 'WP'));
		$result = array("HTML" => $WPViewHTML, 'data' => $data);
		return $result;
	}

	public static function filterHiddenUpdate($siteID, $stats, $type){
		$arrayKeys = array('upgradable_themes' => 'themes', 'upgradable_plugins'=>'plugins', 'upgradable_translations' => 'translations','core_updates' => 'core', 'premium_updates' => 'plugins');
		$where = array(
		      		'query' =>  "siteID=':siteID' AND type = ':type'",
		      		'params' => array(
		               ':siteID'=>$siteID,
		               ':type'=> $arrayKeys[$type]
       				)
    			 );
		$hideList = DB::getArray("?:hide_list", "*", $where);
		if (empty($hideList) || empty($stats) || empty($type)) {
			return $stats;
		}
		if ($type == 'upgradable_translations' && !empty($hideList)) {
			unset($stats);
			return $stats;
		}
		$statsItem =  objectToArray($stats);
		if ($arrayKeys[$type] == 'core') {
			foreach ($hideList as $key => $WPValue) {
				if (version_compare($WPValue['URL'], $statsItem['version'], '==' )) {
					unset($stats);
					return $stats;
				}
			}
		} elseif ($arrayKeys[$type] != 'core') {
			foreach ($stats as $statsKey => $statsValue) {
				foreach ($hideList as $elementName => $value) {
					$item =  objectToArray($statsValue);
					if (!empty($item) && !empty($value['URL']) && (($value['type'] == 'plugins' && $value['URL'] == $item['file']) || ($value['type'] == 'themes' && ($value['URL'] == $item['theme']) || $value['URL'] == $item['theme_tmp']) || ($value['type'] == 'plugins' && $value['URL'] == $item['slug'])) ) {
						unset($stats[$statsKey]);
					}
				}
			}
		}

		return $stats;
	}

	public static function generateWhereConditionByView($view, $joinCond=''){
		$where = '';
		switch ($view) {
			case 'sites':
				$where = $joinCond."updatePluginCounts !=0 OR ".$joinCond."updateThemeCounts !=0 OR ".$joinCond."isTranslationUpdateAvailable = '1' OR ".$joinCond."isCoreUpdateAvailable = '1'";
				return 	$where;
			case 'plugins':
				$where = $joinCond.'updatePluginCounts IS NOT NULL';
				return $where;
			case 'themes':
				$where = $joinCond.'updateThemeCounts IS NOT NULL';
				return $where;
			case 'core':
				$where = $joinCond.'isCoreUpdateAvailable = 1';
				return $where;
			case 'translations':
				$where = $joinCond."isTranslationUpdateAvailable = '1'";
				return $where;
			default:
				return $where;
		}
	}

	public static function prepareSiteViewArray($sitesStats){
		$WPVulnsSiteIDs = array();
		if (empty($sitesStats)) {
			return $siteStats;
		}
		$siteIDs = array_keys($sitesStats);
		if (function_exists('WPVulnsGetVulnsUpdate')) {
			$WPVulnsStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], true, $siteIDs);
			if (!empty($WPVulnsStats['siteView'])) {
				$WPVulnsSiteIDs = array_keys($WPVulnsStats['siteView']);
			}
		}
		foreach ($sitesStats as $siteID => $siteInfo) {
			$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
							)
					);
			$siteName = DB::getField('?:sites', 'name', $where);
			unset($sitesStats[$siteID]['stats']);
			$sitesStats[$siteID]['name'] = $siteName;
			if (!empty($WPVulnsSiteIDs) && in_array($siteID, $WPVulnsSiteIDs)) {
				$sitesStats[$siteID]['vulnerability'] = 1;
			}
		}
		
		return $sitesStats;
	}

	public static function getSitesRowDetailedUpdates($data){
		if (function_exists('WPVulnsGetVulnsUpdate')) {
			$siteStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], false, array('0' => $data['siteID']));
			$siteStats = $siteStats['siteView'];
		} 
		if(empty($siteStats)){
			$siteStats  = panelRequestManager::getSitesUpdates('','',array('0' => $data['siteID']));
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView']);
		}
		$siteViewHTML = TPL::get('/templates/updates/sitesView.tpl.php', array('siteDetailedData' => $siteStats, 'view' => 'sites', 'parentFlag' => $data['parentFlag']));
		$data = array('siteID' => $data['siteID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function getHiddenUpdatesRowDetailedUpdates($data){
		if (function_exists('WPVulnsGetVulnsUpdate')) {
			$siteStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], false, array('0' => $data['siteID']), true);
		} 
		if(empty($siteStats)){
			$siteStats  = panelRequestManager::getSitesUpdates('','',array('0' => $data['siteID']));			
		}
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView'], true);
		$siteViewHTML = TPL::get('/templates/updates/sitesView.tpl.php', array('siteDetailedData' => $siteStats, 'view' => 'hiddenUpdates', 'parentFlag' => $data['parentFlag']));
		$data = array('siteID' => $data['siteID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function getPluginsRowDetailedUpdates($data){
		$where = array(
			      		'query' =>  "updateInfo LIKE '%,:itemID-P,%'",
			      		'params' => array(
			               ':itemID'=>$data['itemID']
							)
					);
		$siteIDs = DB::getFields('?:site_stats', 'siteID', $where);
		if (function_exists('WPVulnsGetAffectedPTVulnsUpdate')) {
			$pluginStats = WPVulnsGetPTCVulnsUpdate('pluginsView', $siteIDs, false);
			$pluginStats = $pluginStats['plugins'];
		}
		if(empty($pluginStats)){
			$sitesStats  = panelRequestManager::getSitesUpdates('','', $siteIDs);
			$pluginStats = $sitesStats['pluginsView']['plugins'];
		}
		foreach ($pluginStats as $key => $value) {
			if ($key != $data['key']) {
				unset($pluginStats[$key]);
			}else {
				foreach ($value as $siteID => $item) {
					if (!empty($item['hiddenItem'])) {
						unset($pluginStats[$key][$siteID]);
					}
				}
			}
		}
		if (empty($pluginStats)) {
			$pluginStats = array();
		}
		$siteViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteDetailedData' => $pluginStats, 'view' => 'plugins', 'parentFlag' => $data['parentFlag']));
		$data = array('itemID' => $data['itemID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function getThemesRowDetailedUpdates($data){
		$where = array(
			      		'query' =>  "updateInfo LIKE '%,:itemID-T,%'",
			      		'params' => array(
			               ':itemID'=>$data['itemID']
							)
					);
		$siteIDs = DB::getFields('?:site_stats', 'siteID', $where);
		if (function_exists('WPVulnsGetAffectedPTVulnsUpdate')) {
			$themesStats = WPVulnsGetPTCVulnsUpdate('themesView', $siteIDs, false);
			$themesStats = $themesStats['themes'];
		}
		if(empty($themesStats)){
			$sitesStats  = panelRequestManager::getSitesUpdates('','', $siteIDs);
			$themesStats = $sitesStats['themesView']['themes'];
		}
		
		foreach ($themesStats as $key => $value) {
			
			if ($key != $data['key']) {
				unset($themesStats[$key]);
			}else {
				foreach ($value as $siteID => $item) {
					if (!empty($item['hiddenItem'])) {
						unset($themesStats[$key][$siteID]);
					}
				}
			}
		}
		$siteViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteDetailedData' => $themesStats, 'view' => 'themes', 'parentFlag' => $data['parentFlag']));
		$data = array('itemID' => $data['itemID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function getCoreRowDetailedUpdates($data){
		$where = array(
			      		'query' =>  "updateInfo LIKE '%,:itemID-C,%'",
			      		'params' => array(
			               ':itemID'=>$data['itemID']
							)
					);
		$siteIDs = DB::getFields('?:site_stats', 'siteID', $where);
		if (function_exists('WPVulnsGetAffectedPTVulnsUpdate')) {
			$coreStats = WPVulnsGetPTCVulnsUpdate('coreView', $siteIDs, false);
			$coreStats = $coreStats['core'];
		}
		if(empty($coreStats)){
			$sitesStats  = panelRequestManager::getSitesUpdates('','', $siteIDs);
			$coreStats = $sitesStats['coreView']['core'];
		}
		
		foreach ($coreStats as $key => $value) {
			if (version_compare($key, $data['key'],'!=')) {
				unset($coreStats[$key]);
			}else {
				foreach ($value as $siteID => $item) {
					if (!empty($item['hiddenItem'])) {
						unset($coreStats[$key][$siteID]);
					}
				}
			}
		}
		$siteViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteDetailedData' => $coreStats, 'view' => 'wp', 'parentFlag' => $data['parentFlag']));
		$data = array('itemID' => $data['itemID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function getTranslationsRowDetailedUpdates($data){
		$where = array(
			      		'query' =>  "updateInfo LIKE '%,:itemID-R,%'",
			      		'params' => array(
			               ':itemID'=>$data['itemID']
							)
					);
		$siteIDs = DB::getFields('?:site_stats', 'siteID', $where);
		$sitesStats  = panelRequestManager::getSitesUpdates('','', $siteIDs);
		$translationsStats = $sitesStats['translationsView']['translations'];
		foreach ($translationsStats as $key => $value) {
			if ($key != $data['key']) {
				unset($translationsStats[$key]);
			}
		}
		$siteViewHTML = TPL::get('/templates/updates/PTTCView.tpl.php', array('siteDetailedData' => $translationsStats, 'view' => 'translations'));
		$data = array('itemID' => $data['itemID'], 'HTML' => $siteViewHTML);
		return $data;
	}

	public static function filterHiddenUpdateBySite($siteStats, $filterOut = false){
		if (empty($siteStats)) {
			return array();
		}
		foreach ($siteStats as $key => $site) {
			foreach ($site as $siteKey => $details) {
				// if ($siteKey == 'core' && (($details['hiddenItem'] && !$filterOut) || (!$details['hiddenItem'] && $filterOut))) {
				// 	unset($siteStats[$key][$siteKey]);
				// }else{
					if (!empty($details)) {
						foreach ($details as $innerKey => $data) {
							if (($data['hiddenItem'] && !$filterOut) || (!$data['hiddenItem'] && $filterOut)) {
								unset($siteStats[$key][$siteKey][$innerKey]);
							}
							if (empty($siteStats[$key][$siteKey])) {
								unset($siteStats[$key][$siteKey]);
							}
						}
					}
				//}
			}
		}
		return $siteStats;
	}

	public static function getSiteStatsByID($siteID){
		$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
							)
					);
		$siteStats = DB::getArray('?:site_stats', '*', $where, 'siteID');
		$siteName = DB::getField('?:sites', 'name', $where);
		$siteStats[$siteID]['name'] = $siteName;
		self::getSiteUpdateStats($siteStats[$siteID], 1);
	}

	public static function formatPattern($data){
		if (empty($data)) {
			return array();
		}
		$items = array_filter(explode(',', $data));

		$map = array();
		if (empty($items)) {
			return $map;
		}
		foreach($items as $item) {
			$exploded = explode('-', $item);
			$number = $exploded[0];
			$letter = $exploded[1];
			if (!array_key_exists($letter, $map)) {
				$map[$letter] = array();
			}
			$map[$letter][] = $number;
		}
		if (empty($map)) {
			return array();
		}
		return $map;
	}

	public static function processSiteStatsUpdateInfo($rawSiteStats){	
		$arrayKeys = array('upgradable_themes' => 'theme', 'upgradable_plugins'=>'plugin', 'premium_updates'=> 'plugin', 'upgradable_translations' => 'translation','core_updates' => 'core');
		
		$newUpdateInfo = '';
		$newStatsUpdateInfo = array();
		$rawStats = $rawSiteStats['stats'];
		$siteUpdateInfo = DB::getField('?:site_stats','updateInfo','siteID ='.DB::esc($rawSiteStats['siteID']));

		if (!empty($siteUpdateInfo)) {
			$updateInfo = self::generateImploadeID($siteUpdateInfo);
		} 
		if(!empty($rawStats)){
			foreach ($rawStats as $updateItem => $itemValue) {
				if (empty($itemValue) || !is_array($itemValue)) {
					continue;
				}
				if ($arrayKeys[$updateItem] == 'core') {
					$itemValue = objectToArray($itemValue);
					$newStatsUpdateInfo [] = $itemValue['version'];

					$newUpdateInfo = $newUpdateInfo.self::updateUpdateWPStats($itemValue, $updateInfo);
				}
				if (empty($itemValue) || !is_array($itemValue)) {
					continue;
				}
				foreach ($itemValue as $innerKey => $innerValue) {
					$innerValue = objectToArray($innerValue);
					if ($updateItem == 'premium_updates') {
						$innerValue['plugin'] = $innerValue['slug'];
						$innerValue['name'] = $innerValue['Name'];
					}
					if ($arrayKeys[$updateItem] == 'plugin' /*&& (!isset($innerValue['plugin']) && empty($innerValue['plugin']))*/) {
						$innerValue['plugin'] = $innerValue['file'];
					}
					if ($arrayKeys[$updateItem] == 'theme' /*&& (!isset($innerValue['theme']) && empty($innerValue['theme']))*/) {
						$innerValue['theme'] = $innerValue['theme_tmp'];
					}
					if (!empty($arrayKeys[$updateItem]) && $arrayKeys[$updateItem] != 'core') {
						$newStatsUpdateInfo [] = $innerValue[$arrayKeys[$updateItem]];
						$newUpdateInfo = $newUpdateInfo.self::updateUpdateStats($innerValue, $updateInfo, $updateItem, $arrayKeys);
					} 
				}
			}
			self::updateUpdatedStats($updateInfo, $newStatsUpdateInfo);
		}elseif(!empty($updateInfo) && empty($newStatsUpdateInfo)) {
			self::updateUpdatedStats($updateInfo, $newStatsUpdateInfo);
		}
		return $newUpdateInfo.',';	
	}

	public static function updateUpdateStats($innerValue, $updateInfo, $updateItem, $arrayKeys){
		$updateInfoKey = array('theme' => 'T', 'plugin' => 'P', 'core' => 'C', 'translation' => 'R');
		$newUpdateInfo = ',';
		$updateKey = $arrayKeys[$updateItem];
		$UK = $updateInfoKey[$updateKey];

		if (!empty($arrayKeys[$updateItem]) && $arrayKeys[$updateItem] != 'core') {
			
			$updateStats = DB::getFields('?:update_stats', 'URL, ID', "type = '".DB::esc($updateKey)."'", 'ID');

			if (empty($updateStats) || (!empty($updateStats) && !in_array($innerValue[$updateKey], $updateStats))) {

				$ID = DB::insert('?:update_stats',array('type' => $arrayKeys[$updateItem], 'URL' =>$innerValue[$updateKey], 'name' => $innerValue['name'], 'updatePresentCount' => 1));
				$newUpdateInfo = $newUpdateInfo.$ID.'-'.$UK;

			} elseif (in_array($innerValue[$updateKey], $updateStats) && (empty($updateInfo) || (!empty($updateInfo) && !in_array($innerValue[$updateKey], $updateInfo)))) {

				$ID = DB::update('?:update_stats','updatePresentCount = updatePresentCount + 1', "URL='".DB::esc($innerValue[$updateKey])."'");
				$newUpdateInfo = $newUpdateInfo.array_search($innerValue[$updateKey], $updateStats).'-'.$UK;

			} elseif (in_array($innerValue[$updateKey], $updateStats) && in_array($innerValue[$updateKey], $updateInfo)) {
				$newUpdateInfo = $newUpdateInfo.array_search($innerValue[$updateKey], $updateInfo).'-'.$UK;
			}
		}
		return $newUpdateInfo;
	}

	public static function updateUpdateWPStats($innerValue, $updateInfo){
		$newUpdateInfo = ',';
		$updateStats = DB::getFields('?:update_stats', 'URL, ID', "type = 'core'", 'ID');
		if (empty($updateStats) || (!empty($updateStats) && !in_array($innerValue['version'], $updateStats))) {

			$ID = DB::insert('?:update_stats',array('type' => 'core', 'URL' =>$innerValue['version'], 'name' => $innerValue['version'], 'updatePresentCount' => 1));
			$newUpdateInfo = $newUpdateInfo.$ID.'-C';

		} elseif (in_array($innerValue['version'], $updateStats) && (empty($updateInfo) || (!empty($updateInfo) && !in_array($innerValue['version'], $updateInfo)))) {

			$ID = DB::update('?:update_stats','updatePresentCount = updatePresentCount + 1', "URL='".DB::esc($innerValue['version'])."'");
			$newUpdateInfo = $newUpdateInfo.array_search($innerValue['version'], $updateStats).'-C';

		} elseif (in_array($innerValue['version'], $updateStats) && in_array($innerValue['version'], $updateInfo)) {
			
			$newUpdateInfo = $newUpdateInfo.array_search($innerValue['version'], $updateInfo).'-C';

		}
		return $newUpdateInfo;
	}

	public static function updateUpdatedStats($oldUpdateInfo, $newUpdateInfo){
		if (!empty($oldUpdateInfo)) {
			foreach ($oldUpdateInfo as $ID => $value) {
				if (!in_array($value, $newUpdateInfo)) {
					DB::update('?:update_stats','updatePresentCount = updatePresentCount - 1', "URL='".DB::esc($value)."'");
					//DB::delete('?:update_stats','updatePresentCount = 0');
				}elseif (empty($newUpdateInfo) && !empty($oldUpdateInfo)) {
					DB::update('?:update_stats','updatePresentCount = updatePresentCount - 1', "URL='".DB::esc($value)."' AND updatePresentCount !=0 ");
				}
			}
		}
	}

	public static function generateImploadeID($siteUpdateInfo){
		$siteUpdateInfo = self::formatPattern($siteUpdateInfo);
		if (empty($siteUpdateInfo) || !is_array($siteUpdateInfo)) {
		 	return '';
		} 
		$IDs = ""; $i=0;
		foreach ($siteUpdateInfo as $key => $item) {
			$IDs = $IDs.implode(", ",$item);
			if($i!=count($siteUpdateInfo)-1){
				$IDs=$IDs.", ";
			}
			$i++;	
		}
		if (empty($IDs)) {
			return '';
		}
		$updateInfo = DB::getFields('?:update_stats','URL, ID',"ID IN(".DB::esc($IDs).")",'ID');
		return $updateInfo;
	}

	public static function prepareHiddenViewArray($itemView){
		$hiddenUpdates = array();
		if (empty($itemView)) {
			return $hiddenUpdates;
		}
		foreach ($itemView as $siteID => $item) {
			foreach ($item as $slug => $value) {
				foreach ($value as $key => $data) {
					if ($data['hiddenItem'] == false) {
						unset($itemView[$siteID][$slug][$key]);
					} 
					if ($data['hiddenItem'] && $data['vulnerability']) {
						$hiddenUpdates[$siteID]['vulnerability'] = 1;
					}
				}
			}
		}
		foreach ($itemView as $siteID => $value) {
			if (!empty($value['error'])) {
				continue;
			}
			if (empty($value['plugins']) && empty($value['themes']) && empty($value['core']) && empty($value['translations'])) {
				continue;
			}
			$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
							)
					);
			$siteName = DB::getField('?:sites', 'name', $where);
			$hiddenUpdates[$siteID]['name'] = $siteName;
			$hiddenUpdates[$siteID]['siteID'] = $siteID;
			if (!empty($value['plugins'])) {
				$hiddenUpdates[$siteID]['updatePluginCounts'] = count($value['plugins']);
			} 
			if (!empty($value['themes'])) {
				$hiddenUpdates[$siteID]['updateThemeCounts'] = count($value['themes']);
			}
			if (!empty($value['core'])) {
				$hiddenUpdates[$siteID]['isCoreUpdateAvailable'] = count($value['core']);
			} 
			if (!empty($value['translations'])) {
				$hiddenUpdates[$siteID]['isTranslationUpdateAvailable'] = count($value['translations']);
			}
		}
		return $hiddenUpdates;	
	}

	public static function updateInPage($data){
		$updateArray = array();
		$selector = $data['selector'];
		$view = $data['view'];
		if (!empty($data['allUpdates']['siteIDs']) && $view == 'sites') {
			$siteStats = panelRequestManager::getSitesUpdates('', '', $data['allUpdates']['siteIDs'] );
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView']);
			$updateArray = self::formatUpdateArray($siteStats);
		} elseif(!empty($data['allUpdates']['itemIDs']) && ($selector == 'plugins' || $selector == 'themes' || $selector == 'core' || $selector == 'translations')){
			$updateArray = self::formatPTTCUpdateArray($data['allUpdates']['itemIDs'], $data['selector'], $data);
		} elseif ($view == 'hiddenUpdates') {
			$siteStats = panelRequestManager::getSitesUpdates('', '', $data['allUpdates']['siteIDs'] );
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView'], true);
			$updateArray = self::formatUpdateArray($siteStats);
		} elseif ($view == 'WPVulns' && function_exists('WPVulnsGetVulnsUpdate')) {
			$siteStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], true, $data['allUpdates']['siteIDs']);
			$updateArray = self::formatUpdateArray($siteStats['siteView'], $view);
		}
		if (!empty($data['selectedUpdates'])) {
			$temp = $data['selectedUpdates'][key($data['selectedUpdates'])];
			foreach ($temp as $siteID => $value) {
				$updateArray[$siteID]=$value;
			}
		}
		if (empty($updateArray)) {
			return false;
		}
		return $updateArray;
	}

	public static function formatUpdateArray($siteStats, $view = NULL){
		$updateArray = array();
		foreach ($siteStats as $siteID => $item) {
			foreach ($item as $key => $data) {
				if ($view == 'WPVulns' && $key == 'translations') {
					continue;
				}
				if ($key == 'core') {
					$updateArray[$siteID][$key] = key($data);
				} else{
					if (!empty($data)) {
						foreach ($data as $keyItem => $value) {
							$updateArray[$siteID][$key][]=$keyItem;
						}
					}
				}
			}

		}
		return $updateArray;
	}

	public static function formatPTTCUpdateArray($itemIDs, $view, $data = array()){
		$updateInfoKey = array('themes' => 'T', 'plugins' => 'P', 'core' => 'C', 'translations' => 'R');
		$infoKey = $updateInfoKey[$view];
		$updateArray = array();
		foreach ($itemIDs as $key => $itemID) {
			if (userStatus() != "admin") {
				$where = array(
				      		'query' =>  "UA.siteID=SS.siteID AND SS.siteID=S.siteID AND S.type='normal' AND SS.updateInfo LIKE '%,:itemID-".$infoKey.",%' ",
				      		'params' => array(
				               ':itemID'=>$itemID,
				               'userID'=>$GLOBALS['userID']
								)
						);
				$siteIDs = DB::getFields('?:site_stats SS, ?:sites S, ?:user_access UA', 'UA.siteID', $where);
			} else{
				$where = array(
				      		'query' =>  "SS.siteID=S.siteID AND S.type='normal' AND SS.updateInfo LIKE '%,:itemID-".$infoKey.",%' ",
				      		'params' => array(
				               ':itemID'=>$itemID
								)
						);
				$siteIDs = DB::getFields('?:site_stats SS, ?:sites S', 'SS.siteID', $where);
			}
			$where2 = array(
					'query' =>  "ID = ':itemID'",
					'params' => array(
								':itemID'=>$itemID
								)
				);
			$URL = DB::getField('?:update_stats', 'URL', $where2);
			foreach ($siteIDs as $siteID) {
				if ($view == 'core') {
					$updateArray[$siteID][$view]= $URL;  
				}else{
					$updateArray[$siteID][$view][]= $URL;  
				}
			}
		}

		return $updateArray;

	}

	public static function getUpdateCounts($data){
		$updateCounts = array();
		$vulnerableSiteIDs = array();
		$siteStats = panelRequestManager::getSitesUpdates();
		$pluginsView = self::filterHiddenUpdateBySite($siteStats['pluginsView']);
		$themesView = self::filterHiddenUpdateBySite($siteStats['themesView']);
		$coreView = self::filterHiddenUpdateBySite($siteStats['coreView']);
		$translationsView = self::filterHiddenUpdateBySite($siteStats['translationsView']);
		$updateCounts['pluginsCount'] = 0;
		$updateCounts['themesCount'] = 0;
		$updateCounts['core'] = 0;
		if (!empty($pluginsView['plugins'])) {
			$updateCounts['pluginsCount'] = count($pluginsView['plugins']);
		}
		if (!empty($themesView['themes'])) {
			$updateCounts['themesCount'] = count($themesView['themes']);
		}
		if (!empty($coreView['core'])) {
			$updateCounts['core'] = count($coreView['core']);
		}
		$updateCounts['translationsCount'] = 0;
		$updateCounts['wooDBUpdateCount'] = 0;
		if (!empty($translationsView['translations'])) {
			$updateCounts['translationsCount'] = count($translationsView['translations'][key($translationsView['translations'])]);
		}
		$updateCounts['hiddenUpdateCounts'] = $siteStats['hiddenUpdateCount'];
		if (!empty($siteStats['wooDBUpdateCount'])) {
			$updateCounts['wooDBUpdateCount'] = $siteStats['wooDBUpdateCount'];
		}
		$updateCounts['siteErrorCount'] = 0;
		$updateCounts['totalUpdateCount'] = $siteStats['totalUpdateCount'];
		$WPVulnsCount = 0;
		$WPVulnsHiddenCount = array();
		$affectedCount = 0;
		if (function_exists('WPVulnsGetVulnsUpdate')) {
			$WPVulnsStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], true,'', true);
			if (!empty($WPVulnsStats)) {
				foreach ($WPVulnsStats['siteView'] as $siteID => $value) {
					if (!empty($value['error'])) {
						continue;
					}
					foreach ($value as $key => $item) { 
						if ($key == 'translations') {
							continue;
						}
						foreach ($item as $itemKey => $data) {
							if ($data['hiddenItem']) {
								if (!in_array($siteID, $WPVulnsHiddenCount)) {
									$WPVulnsHiddenCount[] = $siteID;
								}
							}else{
							 	$WPVulnsCount ++;
							}
						}
					}
					$vulnerableSiteIDs[] = $siteID;
				}
			}
			$updateCounts['affectedCount'] = $WPVulnsStats['totalVulnsCount'];

		}
		$updateCounts['vulnerableSiteIDs'] = $vulnerableSiteIDs;
		$updateCounts['WPVulnsCount'] = $WPVulnsCount;
		$updateCounts['WPVulnsHiddenCount'] = $WPVulnsHiddenCount;
		$updateCounts['totalPluginUpdateCount'] = $siteStats['pluginUpdateCount'];
		$updateCounts['totalThemeUpdateCount'] = $siteStats['themeUpdateCount'];
		$updateCounts['totalCoreUpdateCount'] = $siteStats['coreUpdateCount'];
		$updateCounts['totalTranslationUpdateCount'] = $siteStats['translationUpdateCount'];
		return $updateCounts;
	}

	public static function updateOverall($data){
		$updateArray = array();
		if ($data == 'sites') {
			$siteStats = panelRequestManager::getSitesUpdates();
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView']);
			$updateArray = self::formatUpdateArray($siteStats);
		}elseif ($data == 'hiddenUpdates') {
			$siteStats = panelRequestManager::getSitesUpdates();
			$siteStats = self::filterHiddenUpdateBySite($siteStats['siteView'],true);
			$updateArray = self::formatUpdateArray($siteStats);
		}elseif($data == 'WPVulns' && function_exists('WPVulnsGetVulnsUpdate')){
			$siteStats = WPVulnsGetVulnsUpdate($GLOBALS['userID'], true);
			$updateArray = self::formatUpdateArray($siteStats['siteView'], $data);

		}elseif($data == 'plugins') {
			$itemIDs = multiUserGetPTTCIDs('plugin','');
			$updateArray = self::formatPTTCUpdateArray($itemIDs, 'plugins');
		}elseif($data == 'themes') {
			$itemIDs = multiUserGetPTTCIDs('theme','');
			$updateArray = self::formatPTTCUpdateArray($itemIDs, 'themes');
		}elseif($data == 'core') {
			$itemIDs = multiUserGetPTTCIDs('core','');
			$updateArray = self::formatPTTCUpdateArray($itemIDs, 'core');
		}elseif($data == 'translations') {
			$itemIDs = multiUserGetPTTCIDs('translation','');
			$updateArray = self::formatPTTCUpdateArray($itemIDs, 'translations');
		}
		return $updateArray;
	}

	public static function getSitesErrorViewContent($data){
		$sitesStats = panelRequestManager::getSitesUpdates();
		$siteView = $sitesStats['siteView'];
		if (!empty($siteView)) {
			foreach ($siteView as $key => $value) {
				if (empty($value['error'])) {
					unset($siteView[$key]);
				}
			}
		}
		$siteViewHTML = TPL::get('/templates/updates/errorView.tpl.php', array('siteData' => $siteView));
		return $siteViewHTML;
	}

	public static function getOnlyHiddenUpdateCounts(){
		$updateCounts = array();
		$siteStats = panelRequestManager::getSitesUpdates();
		$pluginsView = self::filterHiddenUpdateBySite($siteStats['pluginsView'], true);
		$themesView = self::filterHiddenUpdateBySite($siteStats['themesView'], true);
		$coreView = self::filterHiddenUpdateBySite($siteStats['coreView'], true);
		$translationsView = self::filterHiddenUpdateBySite($siteStats['translationsView'], true);
		$updateCounts['hiddenPluginsCount'] = count($pluginsView['plugins']);
		$updateCounts['hiddenThemesCount'] = count($themesView['themes']);
		$updateCounts['hiddenCore'] = count($coreView['core']);
		$updateCounts['hiddenTranslationsCount'] = count($translationsView['translations'][key($translationsView['translations'])]);
		return $updateCounts;
	}
}

panelRequestManager::addClassToPanelRequest('manageUpdates');