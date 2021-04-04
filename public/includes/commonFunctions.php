<?php

/************************************************************
 * InfiniteWP Admin panel									*
 * Copyright (c) 2012 Revmakx								*
 * www.revmakx.com											*
 *															*
 ************************************************************/
require APP_ROOT.'/lib/non-blocking-php/vendor/autoload.php';
use NonBlockingPHP\Execute;
use NonBlockingPHP\Socket\Socket;

function paginate($page, $total, $itemsPerPage, $paginationName='pagination'){
	
	if(empty($page) || !is_numeric($page)) $page = 1;
	
	$totalPage = ceil($total / $itemsPerPage);
	
	$prevPage = $page > 1 ? ($page - 1) : '';
	$nextPage = $page < $totalPage ? ($page + 1) : '';
	
	$pagination = array('page'		=> $page,
						'prevPage'	=> $prevPage,
						'nextPage'	=> $nextPage,
						'total'		=> $total,
						'itemPerPage'	=> $itemsPerPage,
						'totalPage'	=> $totalPage,						
						);
					
	Reg::tplSet($paginationName, $pagination);
						
	return 'LIMIT '.(($page - 1)  * $itemsPerPage).', '.$itemsPerPage;
}

function repoDoCall($URL, $data){
	
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_URL, $URL);
	//curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/plain')); 
	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
	$return=curl_exec($ch);
	
	return $return;
}


function doCall($URL, $data, $timeout=DEFAULT_MAX_CLIENT_REQUEST_TIMEOUT, $options=array()) //Needs a timeout handler
{	
	$SSLVerify = false;
	$URL = trim($URL);
	//if(stripos($URL, 'https://') !== false){ $SSLVerify = true; }
	$settings = Reg::get('settings');
	if (!empty($settings['enableSSLVerify']) && isset($settings['enableSSLVerify'])) {
		$SSLVerify = true;
	}
	$HTTPCustomHeaders = array();
	
	$userAgentAppend = '';	
	if(!defined('IWP_HEADERS') || (defined('IWP_HEADERS') && IWP_HEADERS) ){
		$userAgentAppend = ' InfiniteWP';
		$HTTPCustomHeaders[] = 'X-Requested-From: InfiniteWP';
	}

	if (defined('CUSTOM_USERAGENT')) {
		$userAgentAppend = CUSTOM_USERAGENT;
	}else{
		$userAgentAppend = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36'.$userAgentAppend;
	}
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_URL, $URL);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
	curl_setopt($ch, CURLOPT_USERAGENT, $userAgentAppend);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($SSLVerify === true) ? 2 : false );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSLVerify);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_HEADER, true);

	if (!empty($options) && !empty($options['websiteIP'])) {
		$resolver = getIPResolver($URL, $options['websiteIP']);
		if ($resolver) {
			curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);
			curl_setopt($ch, CURLOPT_RESOLVE, array($resolver));
		}
	}

	if (!empty($options['HTTPVersion']) && isset($options['HTTPVersion']) && $options['HTTPVersion'] != 'auto') {
		$HTTPVersion = $options['HTTPVersion'];
		if ($HTTPVersion == 'CURL_HTTP_VERSION_1_0') {
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		}elseif ($HTTPVersion == 'CURL_HTTP_VERSION_1_1') {
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		}elseif ($HTTPVersion == 'CURL_HTTP_VERSION_2_0') {
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
		}elseif ($HTTPVersion == 'CURL_HTTP_VERSION_2TLS') {
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2TLS);
		}
	}

	if (isIWPDebugModeEabled()) {
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		if (defined('IWP_DEBUG_LOG_FILE_MODE')) {
			$fileMode = IWP_DEBUG_LOG_FILE_MODE;
		}else{
			$fileMode = 'a+';
		}
		$verbose = fopen(APP_ROOT.'/debuglog.txt', $fileMode);
		curl_setopt($ch, CURLOPT_STDERR, $verbose);
		
	}
	if(!empty($data['curlVerboseFromPanel'])){
		$uploadDirectory = APP_ROOT.'/uploads/';
		$file_name = $uploadDirectory.'/iwp_verbose.txt';
		if (!is_writable($uploadDirectory)){
		    return $uploadDirectory.'Not Writeable';
		}
		$verbose = fopen($file_name, 'w+');
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_STDERR, $verbose);
	}

	if((!defined('REFERER_OPT') || (defined('REFERER_OPT') && REFERER_OPT === TRUE)) &&  (!empty($options['referrerURL']) && isset($options['referrerURL'])) ){
		curl_setopt($ch, CURLOPT_REFERER, $options['referrerURL']);
	}elseif ((!defined('REFERER_OPT') || (defined('REFERER_OPT') && REFERER_OPT === TRUE)) &&  !empty($options['referrerURL'])) {
		curl_setopt($ch, CURLOPT_REFERER, $URL);
	}
        
	if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	}
	
	$contentType = 'application/x-www-form-urlencoded';
	if(!empty($options['contentType'])){
		$contentType = $options['contentType'];
	}
	$HTTPCustomHeaders[] = 'Content-Type: '.trim($contentType);//before array('Content-Type: text/plain') //multipart/form-data
	$HTTPCustomHeaders[] = 'Expect:';

	if (defined('ENABLE_TRANSFER_ENCODING') && ENABLE_TRANSFER_ENCODING) {
		$HTTPCustomHeaders[] = 'Transfer-Encoding: chunked';
	}
	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $HTTPCustomHeaders);
	if (!empty($options['SSLVersion']) && isset($options['SSLVersion'])) {
		$SSLVersion = (int) $options['SSLVersion'];
		curl_setopt($ch, CURLOPT_SSLVERSION, $SSLVersion);
	}
	if(!empty($options['httpAuth'])){
		curl_setopt($ch, CURLOPT_USERPWD, $options['httpAuth']['username'].':'.$options['httpAuth']['password']);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	}	
	
	if(!empty($options['useCookie'])){
		if(!empty($options['cookie'])){
			curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
		}
	}
	
	if (!ini_get('safe_mode') && !ini_get('open_basedir')){
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		@curl_setopt($ch, CURLOPT_POSTREDIR, 1);
	}
	
	if($options['file'] == 'download' && !empty($options['filePath'])){
		$fp = fopen($options['filePath'], "w");
    	curl_setopt($ch, CURLOPT_FILE, $fp);	
	}
	if (empty($data['params']['skipPostData'])) {
		if (!empty($data) && !empty($data['skipJsonCommunication'])) {
			$requestData = base64_encode(serialize($data));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
		}elseif(!empty($data) && !empty($data['params']['cloneCommunication'])){
			$requestData = base64_encode(jsonEncoder($data));
			$requestData = '_IWP_JSON_CLONE_PREFIX_'.$requestData;
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
		}elseif(!empty($data)) {
			$requestData = base64_encode(jsonEncoder($data));
			$requestData = '_IWP_JSON_PREFIX_'.$requestData;
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $requestData);
		}
	}
	
	$microtimeStarted 	= microtime(true);
	$rawResponse 			= curl_exec($ch);
	$microtimeEnded 	= microtime(true);
	
	$curlInfo = array();
	$curlInfo['info'] = curl_getinfo($ch);
	if(curl_errno($ch)){
		$curlInfo['errorNo'] = curl_errno($ch);
		$curlInfo['error'] = curl_error($ch);
	}
	if(!empty($data['curlVerboseFromPanel'])){
		if(!empty($curlInfo['error']) || $curlInfo['errorNo']){
			unlink($file_name);
			return 'cURL error code : '.$curlInfo['errorNo'].'<br /><br />'.'Response :'.$curlInfo['error'];
		}
		$file_path = str_replace( '\\', '/', $file_name );
		$curl_response = file_get_contents($file_path);
		unlink($file_name);
		return $curl_response;
	}

	$redirectedUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); 
	curl_close($ch);

	if($options['file'] == 'download' && !empty($options['filePath'])){
		fclose($fp);
	}
	list($responseHeader, $responseBody) = bifurcateResponse($rawResponse, $curlInfo);
	
	return array($responseBody, $microtimeStarted, $microtimeEnded, $curlInfo, $responseHeader, $redirectedUrl);
}

function bifurcateResponse($rawResponse, $curlInfo){  
	$header;
	$body = $rawResponse;//safety
    if(isset($curlInfo["info"]["header_size"])) { 
        $header_size = $curlInfo["info"]["header_size"];  
        $header = substr($rawResponse, 0, $header_size);   
        $body = substr($rawResponse, $header_size);
    }
    return array($header, $body); 
}

function unserializeArray($strBetArray){
	if(empty($strBetArray) || !is_array($strBetArray)){ return false; }
	$newArray = array();
	foreach($strBetArray as $key => $value){
		$newArray[$key] = unserialize($value);
	}
	return $newArray;
}

function getStrBetAll($string, $startString, $endString)
{
	$betArray = array();
	while($string){
		list($strBet, $string) = getStrBet($string, $startString, $endString);
		if(!$strBet) break;
		$betArray[] = $strBet;
	}
	return $betArray;
}

function getStrBet($string, $startString, $endString)//note endstring must be after the start string
{
	if(!$startString) { $startPos = 0; }
	else{
		$startPos = strpos($string, $startString);
		if($startPos === false) { return false; }
		$startPos = $startPos + strlen($startString);
	}
	
	if(!$endString)
	{
		$strBet = substr($string, $startPos);
		return array($strBet, substr($string, strpos($string, $strBet)));
	}
	
	$endPos = strpos($string, $endString, $startPos);
	if(!$endPos) return false;
	
	$strBet = substr($string, $startPos, ($endPos - $startPos));
	return array($strBet, substr($string, $endPos+strlen($endString)));
}


function fixObject (&$object){
  if (!is_object ($object) && gettype ($object) == 'object')
	return ($object = unserialize (serialize ($object)));
  return $object;
}

function objectToArray($o) {
	if (is_object($o)) {
			$o = get_object_vars($o);
	}
	if (is_array($o)) {
		return array_map(__FUNCTION__, $o);
	}
	else {
		// Return array
		return $o;
	}
}


function callURLAsync($url, $params=array()){

    $post_params = array();
	foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);

    $parts = parse_url($url);
	$host = $parts['host'];

	if (($parts['scheme'] == 'ssl' || $parts['scheme'] == 'https') && extension_loaded('openssl')){
		$parts['host'] = "ssl://".$parts['host'];
		$parts['port'] = 443;
		error_reporting(0);
	}
	elseif($parts['port']==''){
		$parts['port'] = 80;
	}	
	  
    $fp = @fsockopen($parts['host'], $parts['port'], $errno, $errstr, 30);
	if(!$fp) return array('status' => false, 'resource' => !empty($fp) ? true : false, 'errorNo' => 'unable_to_intiate_fsock', 'error' => 'Unable to initiate FsockOpen');
	if($errno > 0) return array('status' => false, 'errorNo' => $errno, 'error' => $errno. ':' .$errstr);

	$settings = Reg::get('settings');

    $out = "POST ".$parts['path']." HTTP/1.0\r\n";
    $out.= "Host: ".$host."\r\n";
	if(!empty($settings['httpAuth']['username'])){
		$out.= "Authorization: Basic ".base64_encode($settings['httpAuth']['username'].':'.$settings['httpAuth']['password'])."\r\n";
	}
	$out.= "User-agent: " . "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n"; 
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
    if (isset($post_string)) $out.= $post_string;

    $is_written = fwrite($fp, $out);
	if(!$is_written){
		return array('status' => false, 'writable' => false);
	}
	
	/*if($settings['enableFsockFget'] == 1){
		fgets($fp, 128);
	}*/
	
    fclose($fp);
	return array('status' => true);
}

function nonBlockingBackgroundJob($query=array()){
    $settings = Reg::get('settings');
    $auth = array();
    if(!empty($settings['httpAuth']['username'])){
        $auth = $settings['httpAuth'];
    }
    
    $params = array(
    'url' => APP_URL.EXECUTE_FILE,
    'command' => 'php '.APP_ROOT.'/'.EXECUTE_FILE,
    'auth' => $auth,
    'args' => $query
    );
    
    $connectionMethod = getOption('connectionMethod');
    $connectionMode = getOption('connectionMode');
    $connectionRunner = getOption('connectionRunner');
    $upperConnectionLevel = getOption('upperConnectionLevel');
    
    $modeSetting = array('autoMode'=>true);
    if($connectionMethod=='auto'){
        $modeSetting['upperConnectionLevel'] = ($upperConnectionLevel!='')?$upperConnectionLevel:'commandMode'; 
    }
    if($connectionMode!=''){
        $modeSetting['autoMode'] = false;
        $modeSetting['strictMode'] = ($connectionMode=='commandMode')?'command':'socket';
    }
    
    if($connectionRunner!=''){
        $command = array(
            'cmdRunnerAuto' => 'all',
            'cmdRunnerExec' => 'exec',
            'cmdRunnerSystem' => 'systemexec',
            'cmdRunnerPassthru' => 'passthru',
            'cmdRunnerShellexec' => 'shellexec'
        );
        $socket = array(
            'sockRunnerAuto' => 'all',
            'sockRunnerStream' => 'stream',
            'sockRunnerFsock' => 'fsock',
            'sockRunnerSocket' => 'socketconnect' 
        );
        if($connectionMode=='commandMode'){
            $modeSetting['strictRunner'] = $command[$connectionRunner]; 
        }
        if($connectionMode=='socketMode'){
            $modeSetting['strictRunner'] = $socket[$connectionRunner]; 
        }
    }
    $execute = new Execute($modeSetting);
    $result = $execute->run($params);
    $modeData = $execute->getModeData();
    if($result){
        return array('status' => true, 'modeData'=>$modeData);
    } else {
        return array('status' => false, 'writable' => false, 'errorNo' => 'unable_to_verify', 'error' => 'Unable to verify content(method using '.$modeSetting['strictMode'].')');
    }
}

function connectionMethodSameURLCheck(){
    $connectionMethodDBValue =  uniqid('connectMethod_', true);
    $params=array('check' =>  'sameURLUsingDB', 'connectionMethodDBValue' => $connectionMethodDBValue);
    nonBlockingBackgroundJob($params);
    sleep(7);//due to fsock non-blocking mode we have to wait
    if(!empty($connectionMethodDBValue) && $connectionMethodDBValue == getOption('connectionMethodDBValue')){
            return array('status' => true);
    }
    else{
            return array('status' => false, 'errorNo' => 'unable_to_verify', 'error' => 'Unable to verify content(method using DB)');
    }
}

function fsockSameURLConnectCheck($url, $fget=true){
	
	if($fget){
		$params=array('check' =>  'sameURL');	
	}
	else{
		$fsockSameURLCheckUsingDBValue =  uniqid('fsock_', true);
		$params=array('check' =>  'sameURLUsingDB', 'fsockSameURLCheckUsingDBValue' => $fsockSameURLCheckUsingDBValue);
	}
	
	$post_params = array();
	foreach ($params as $key => &$val) {
      if (is_array($val)) $val = implode(',', $val);
        $post_params[] = $key.'='.urlencode($val);
    }
    $post_string = implode('&', $post_params);
	
	$parts = parse_url($url);
	$host = $parts['host'];

	if (($parts['scheme'] == 'ssl' || $parts['scheme'] == 'https') && extension_loaded('openssl')){
		$parts['host'] = "ssl://".$parts['host'];
		$parts['port'] = 443;
		error_reporting(0);
	}
	elseif($parts['port']==''){
		$parts['port'] = 80;
	}
	  
    $fp = @fsockopen($parts['host'], $parts['port'], $errno, $errstr, 30);
	if(!$fp) return array('status' => false, 'resource' => !empty($fp) ? true : false, 'errorNo' => 'unable_to_intiate_fsock', 'error' => 'Unable to initiate FsockOpen');
	if($errno > 0) return array('status' => false, 'errorNo' => $errno, 'error' => $errno. ':' .$errstr);

	$settings = Reg::get('settings');
	
    $out = "POST ".$parts['path']." HTTP/1.0\r\n";
    $out.= "Host: ".$host."\r\n";
	if(!empty($settings['httpAuth']['username'])){
		$out.= "Authorization: Basic ".base64_encode($settings['httpAuth']['username'].':'.$settings['httpAuth']['password'])."\r\n";
	}
	$out.= "User-agent: " . "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
    $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
    $out.= "Content-Length: ".strlen($post_string)."\r\n";
    $out.= "Connection: Close\r\n\r\n";
	
    if (isset($post_string)) $out.= $post_string;
	
    $is_written = fwrite($fp, $out);
	if(!$is_written){
		return array('status' => false, 'writable' => false, 'errorNo' => 'unable_to_write_request', 'error' => 'Unable to write request');
	}
	
	$temp = '';
	if($fget){		
		 while (!feof($fp)) {
			$temp .= fgets($fp, 128);
		}
	}
	
	fclose($fp);
	
	if($fget){
		if(strpos($temp, 'WWW-Authenticate:') !== false){
			return array('status' => false, 'errorNo' => 'authentication_required', 'error' => 'Your IWP Admin Panel has folder protection.<br><a onclick="$(\'#settings_btn\').click();$(\'#authUsername\').focus();">Set the credentials</a> in settings -> Folder protection.');
		}
		else{			
			return fsockSameURLConnectCheck($url, false);
		}
	}
	else{
		sleep(7);//due to fsock non-blocking mode we have to wait
		if(!empty($fsockSameURLCheckUsingDBValue) && $fsockSameURLCheckUsingDBValue == getOption('fsockSameURLCheckUsingDBValue')){
			return array('status' => true);
		}
		else{
			return array('status' => false, 'errorNo' => 'unable_to_verify', 'error' => 'Unable to verify content(method using DB)');
		}
	}
   
}

function filterParameters($array, $DBEscapeString=true){
  
    if(is_array($array)){
        foreach($array as $key => $value){
            $array[$key] = filterParameters($array[$key]);
        }
    }elseif(is_string($array)){
        if($DBEscapeString){
            $array = DB::realEscapeString($array);
        }
    }
    return $array;
    
}

function IPInRange($IP, $range) {

	if (strpos($range, '*') !==false) { // a.b.*.* format
	  // Just convert to A-B format by setting * to 0 for A and 255 for B
	  $lower = str_replace('*', '0', $range);
	  $upper = str_replace('*', '255', $range);
	  $range = "$lower-$upper";
	}
	
	if (strpos($range, '-')!==false) { // A-B format
	  list($lower, $upper) = explode('-', $range, 2);
	  $lowerDec = (float)sprintf("%u", ip2long($lower));
	  $upperDec = (float)sprintf("%u", ip2long($upper));
	  $IPDec = (float)sprintf("%u", ip2long($IP));
	  return ( ($IPDec>=$lowerDec) && ($IPDec<=$upperDec) );
	}
	if($IP == $range) return true;
	return false;
}

function ksortTree( &$array, $sortMaxLevel=-1, $currentLevel=0 )
{
  if((int)$sortMaxLevel > -1 && $sortMaxLevel <= $currentLevel){ return false;}
  
  if (!is_array($array)) {
    return false;
  }
 
  ksort($array);
  foreach ($array as $k=>$v) {
	$currentLevel++;
    ksortTree($array[$k], $sortMaxLevel, $currentLevel);
  }
  return true;
}

function trimValue(&$v){
	$v = trim($v);
}

function arrayMergeRecursiveNumericKeyHackFix(&$array){
	if(!is_array($array)){ return; }

	foreach($array as $key => $value){
		$finalKey = $key;
		$numKey = preg_replace("/[^0-9]/", '', $key);
		if($key == '_'.$numKey){
			unset($array[$key]);
			$array[$numKey] = $value;
			$finalKey = $numKey;
		}
		arrayMergeRecursiveNumericKeyHackFix($array[$finalKey]);
	}
	return;

}

function appErrorHandler($errno, $errstr,  $errfile, $errline, $errcontext )
{
   	if(!isset($GLOBALS['appErrorHandlerErrors'])){
		$GLOBALS['appErrorHandlerErrors'] = '';	
	}
	$backTrace = get_backtrace_string();
	if (empty($backTrace)) {
		$backTrace = '';
	}
    $GLOBALS['appErrorHandlerErrors'] .= @date('Y-m-d H:i:s')." ERR: errno:".$errno." (".$errstr.") file:".$errfile.", line:".$errline.".\r\n"."Trace: ".$backTrace.".\r\n";
	return false;
}

function appErrorHandlerWriteFile(){

	if(!empty($GLOBALS['appErrorHandlerErrors'])){
		@file_put_contents(APP_ROOT.'/appErrorLogs.txt', $GLOBALS['appErrorHandlerErrors'], FILE_APPEND);
		unset($GLOBALS['appErrorHandlerErrors']);
	}
}

function get_backtrace_string($limit = 10) {

    $bactrace_arr = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $limit);
    $backtrace_str = '';

    if (!is_array($bactrace_arr)) {
        return false;
    }

    foreach ($bactrace_arr as $k => $v) {
        if ($k == 0) {
            continue;
        }

        $line = empty($v['line']) ? 0 : $v['line'];
        $backtrace_str .= '<-' . $v['function'] . '(line ' . $line . ')';
    }

    return $backtrace_str;
}

if((defined('DEV_UPDATE') && DEV_UPDATE == 'xpanel') || (defined('IWP_DEBUG_MODE') && IWP_DEBUG_MODE == 1)){
	set_error_handler('appErrorHandler', E_ERROR|E_WARNING|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_COMPILE_WARNING|E_DEPRECATED);
}else{
	set_error_handler('appErrorHandler', E_ERROR|E_WARNING|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_COMPILE_WARNING);
}
@register_shutdown_function('appErrorHandlerWriteFile');

if (!function_exists('json_encode'))
{
  function json_encode($a=false)
  {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a))
    {
      if (is_float($a))
      {
        // Always use "." for floats.
        return floatval(str_replace(",", ".", strval($a)));
      }

      if (is_string($a))
      {
        static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
        return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
      }
      else
        return $a;
    }
    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
    {
      if (key($a) !== $i)
      {
        $isList = false;
        break;
      }
    }
    $result = array();
    if ($isList)
    {
      foreach ($a as $v) $result[] = jsonEncoder($v);
      return '[' . join(',', $result) . ']';
    }
    else
    {
      foreach ($a as $k => $v) $result[] = jsonEncoder($k).':'.jsonEncoder($v);
      return '{' . join(',', $result) . '}';
    }
  }
}


function downloadURL($URL, $filePath){
	
	return (curlDownloadURL($URL, $filePath) || fopenDownloadURL($URL, $filePath));

}

function curlDownloadURL($URL, $filePath){
	
	//$options = array('file' => 'download', 'filePath' => $filePath);
	//$callResponse = doCall($URL, '', 60, $options);
	$URL = trim($URL);
	$fp = fopen ($filePath, 'w');
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
	curl_setopt($ch, CURLOPT_TIMEOUT, 180);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	if (defined('PANEL_UPDATE_SSL_VERIFY') && PANEL_UPDATE_SSL_VERIFY) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	}
	if (!ini_get('safe_mode') && !ini_get('open_basedir')){
		@curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	}	
	$callResponse = curl_exec($ch);	
	curl_close($ch);
	fclose($fp);

	if($callResponse == 1){
		return true;
	}
	return false;
	
}

function fopenDownloadURL($URL, $filePath){
	
	 if (function_exists('ini_get') && ini_get('allow_url_fopen') == 1) {
		 $src = @fopen($URL, "r");
		 $dest = @fopen($filePath, 'wb');
		 if($src && $dest){
			 while ($content = @fread($src, 1024 * 1024)) {
				@fwrite($dest, $content);
			 }
    
			@fclose($src);
			@fclose($dest);
			return true;
		 }		
	 }
	 return false;
}


function protocolRedirect(){

	if(APP_HTTPS == 1 && ($_SERVER['HTTPS'] != 'on' && $_SERVER['SERVER_PORT']!='443') && ($_SERVER['HTTP_X_FORWARDED_PROTO'] != 'https' || $_SERVER['HTTP_X_FORWARDED_SSL'] != 'on')){
		header('Location: '.APP_URL);	
	}
	elseif(APP_HTTPS == 0 && ($_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT']=='443')){
		header('Location: '.APP_URL);
	}
}

function initialHTTPSRedirect(){
	if(($_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT']=='443')){
		updateHTTPSSettings();
		return true;
	}
	return false;
}

function updateHTTPSSettings(){
	updateOption('initialHTTPSRedirected', 1);
	$settingsRow = DB::getRow("?:settings", "*", "1");
	$general = @unserialize($settingsRow['general']);
	$general['enableHTTPS'] = 1;
	$settingsRow['general'] = serialize($general);
	DB::update("?:settings", $settingsRow, "1");
}

function checkOpenSSL(){
	if(!function_exists('openssl_verify')){
		return false;
	}
	else{
		$key = @openssl_pkey_new();
		@openssl_pkey_export($key, $privateKey);
		$privateKey	= base64_encode($privateKey);
		$publicKey = @openssl_pkey_get_details($key);
		$publicKey 	= $publicKey["key"];
		
		if(empty($publicKey) || empty($privateKey)){
			return false;
		}
	}
	return true;
}

function httpBuildURLCustom($parts){
	
	if(is_array($parts['query'])){
		$parts['query'] = http_build_query($parts['query'], NULL, '&');
	}
	$URL = $parts['scheme'].'://'
		.($parts['user'] ? $parts['user'].':'.$parts['pass'].'@' : '')
		.$parts['host']
		.((!empty($parts['port']) && $parts['port'] != 80) ? ':'.$parts['port'] : '')
		.($parts['path'] ? $parts['path'] : '')
		.($parts['query'] ? '?'.$parts['query'] : '')
		.($parts['fragment'] ? '#'.$parts['fragment'] : '');
	return $URL;
}

function sendMail($from, $fromName, $to, $toName, $subject, $message, $options=array()){
	
	require_once(APP_ROOT.'/lib/phpmailer.php');
	require_once(APP_ROOT.'/lib/class.smtp.php'); //smtp mail
	
	$mail = new PHPMailer(); // defaults to using php "mail()"
	
	if(!empty($options['emailSettings'])){
		$emailSettings = $options['emailSettings'];
	}
	if(!empty($emailSettings['smtpSettings'])){
		$smtpSettings = $emailSettings['smtpSettings'];
	}
	//if(true){
	if ((empty($smtpSettings) || empty($smtpSettings['useSmtp'])) && defined('IWP_TRIAL_PANEL')) {
		$smtpSettings['smtpHost'] = Reg::get('config.TRIAL_MAIL_HOST');
		$smtpSettings['smtpPort'] = 587;
		$smtpSettings['smtpAuth'] = 1;
		$smtpSettings['useSmtp'] = 1;
		$smtpSettings['smtpEncryption'] = 'tls';
		$smtpSettings['smtpAuthUsername'] = Reg::get('config.TRIAL_MAIL_USERNAME');
		$smtpSettings['smtpAuthPassword'] = Reg::get('config.TRIAL_MAIL_PASSWORD');
	}
	
	if(!empty($smtpSettings) && !empty($smtpSettings['useSmtp'])){
		$mail->IsSMTP();
		$mail->Host       = $smtpSettings['smtpHost']; // sets the SMTP server
		$mail->Port       = $smtpSettings['smtpPort'];
		if($smtpSettings['smtpAuth'] == 1 && !empty($smtpSettings['smtpAuthUsername']) && !empty($smtpSettings['smtpAuthPassword'])){
			$mail->SMTPAuth  = true; //enable SMTP authentication
			$mail->Username  = $smtpSettings['smtpAuthUsername']; // SMTP account username
			$mail->Password  = $smtpSettings['smtpAuthPassword']; 
		}
		
		if(!empty($options['CharSet'])){
			$mail->CharSet = 'utf-8';			
		}
		$mail->SMTPSecure = $smtpSettings['smtpEncryption'];
		$mail->From = $from;
		$mail->FromName = $fromName;
		$mail->AddAddress($to);
		$mail->IsHTML(true);
		$mail->Subject = $subject;
		$mail->MsgHTML($message);
		if(!empty($options['attachment'])){
			$mail->AddAttachment($options['attachment']);
		}
		//$mail->Debugoutput = function($str, $level) { /* place any code for debugging here. */ };
	}
	else{
		$body = $message;
		$mail->SetFrom($from, $fromName);
		$mail->AddAddress($to, $toName);
		$mail->Subject = $subject;
		$mail->MsgHTML($body);
		if(!empty($options['attachment'])){
			$mail->AddAttachment($options['attachment']);
		}
	
	}
		
	if(!$mail->Send()) {
	  addNotification($type='E', $title='Mail Error', $message=$mail->ErrorInfo, $state='U');	  
	  return false;
	} else {
	  //echo "Message sent!";
	  return true;
	}
}

if ( !function_exists('mb_detect_encoding') ) { 
	function mb_detect_encoding ($string, $enc=null, $ret=null) { 

		static $enclist = array( 
		'UTF-8',
		// 'ASCII', 
		// 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 
		// 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 
		// 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16', 
		// 'Windows-1251', 'Windows-1252', 'Windows-1254', 
		);

		$result = false; 

		foreach ($enclist as $item) { 
			$sample = $string;
			if(function_exists('iconv'))
				$sample = iconv($item, $item, $string); 
			if (md5($sample) == md5($string)) { 
				if ($ret === NULL) { $result = $item; } else { $result = true; } 
				break; 
			}
		}

		return $result; 
	}
}

function convertToMinSec($time) {
    $min = intval(($time / 60) % 60);
    $minSec = $min.' minutes';
    if($min==0) {
        $sec = intval($time % 60);
        $sec = str_pad($sec, 2, "0", STR_PAD_LEFT);
        $minSec = $sec.' seconds';
    }
    return $minSec;
}

function autoPrintToKeepAlive($uniqueTask){
	$printEveryXSecs = 5;
	$currentTime = microtime(1);

	if(!$GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START']){
		$GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START'] = $currentTime;	
	}
	
	if(!$GLOBALS['IWP_PROFILING']['LAST_PRINT'] || ($currentTime - $GLOBALS['IWP_PROFILING']['LAST_PRINT']) > $printEveryXSecs){
		$printString = $uniqueTask." TT:".($currentTime - $GLOBALS['IWP_PROFILING']['TASKS'][$uniqueTask]['START']);
		printFlush($printString);
		$GLOBALS['IWP_PROFILING']['LAST_PRINT'] = $currentTime;		
	}
}

function printFlush($printString){
	echo $printString;
	ob_flush();
	flush();
}

function jsonEncoder( $data, $options = 0, $depth = 512 ) {
	if ( version_compare( PHP_VERSION, '5.5', '>=' ) ) {
		$args = array( $data, $options, $depth );
	} elseif ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
		$args = array( $data, $options );
	} else {
		$args = array( $data );
	}
	$json = @call_user_func_array( 'json_encode', $args );
	
	if ( false !== $json && ( version_compare( PHP_VERSION, '5.5', '>=' ) || false === strpos( $json, 'null' ) ) )  {
		return $json;
	}

	$args[0] = jsonCompatibleCheck( $data, $depth );
	return @call_user_func_array( 'json_encode', $args );
}

function jsonCompatibleCheck( $data, $depth ) {
	if ( $depth < 0 ) {
		return false;
	}

	if ( is_array( $data ) ) {
		$output = array();
		foreach ( $data as $key => $value ) {
			if ( is_string( $key ) ) {
				$id = jsonConvertString( $key );
			} else {
				$id = $key;
			}
			if ( is_array( $value ) || is_object( $value ) ) {
				$output[ $id ] = jsonCompatibleCheck( $value, $depth - 1 );
			} elseif ( is_string( $value ) ) {
				$output[ $id ] = jsonConvertString( $value );
			} else {
				$output[ $id ] = $value;
			}
		}
	} elseif ( is_object( $data ) ) {
		$output = new stdClass;
		foreach ( $data as $key => $value ) {
			if ( is_string( $key ) ) {
				$id = jsonConvertString( $key );
			} else {
				$id = $key;
			}

			if ( is_array( $value ) || is_object( $value ) ) {
				$output->$id = jsonCompatibleCheck( $value, $depth - 1 );
			} elseif ( is_string( $value ) ) {
				$output->$id = jsonConvertString( $value );
			} else {
				$output->$id = $value;
			}
		}
	} elseif ( is_string( $data ) ) {
		return jsonConvertString( $data );
	} else {
		return $data;
	}

	return $output;
}

function jsonConvertString( $string ) {
	if ( function_exists( 'mb_convert_encoding' ) ) {
		$encoding = mb_detect_encoding( $string, mb_detect_order(), true );
		if ( $encoding ) {
			return mb_convert_encoding( $string, 'UTF-8', $encoding );
		} else {
			return mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
		}
	} else {
		return checkInvalidUTF8( $string, $true);
	}
}

function checkInvalidUTF8( $string, $strip = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Check for support for utf8 in the installed PCRE library once and store the result in a static
	static $utf8_pcre = null;
	if ( ! isset( $utf8_pcre ) ) {
		$utf8_pcre = @preg_match( '/^./u', 'a' );
	}
	// We can't demand utf8 in the PCRE installation, so just return the string in those cases
	if ( !$utf8_pcre ) {
		return $string;
	}

	// preg_match fails when it encounters invalid UTF8 in $string
	if ( 1 === @preg_match( '/^./us', $string ) ) {
		return $string;
	}

	// Attempt to strip the bad chars if requested (not recommended)
	if ( $strip && function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8', $string );
	}

	return '';
}

function getBrowser() 
{ 
    $u_agent = $_SERVER['HTTP_USER_AGENT']; 
    $bname = 'Unknown';
    $platform = 'Unknown';
    $version= "";

    //First get the platform?
    if (preg_match('/linux/i', $u_agent)) {
        $platform = 'linux';
    }
    elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
        $platform = 'mac';
    }
    elseif (preg_match('/windows|win32/i', $u_agent)) {
        $platform = 'windows';
    }
    
    // Next get the name of the useragent yes seperately and for good reason
    if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) 
    { 
        $bname = 'Internet Explorer'; 
        $ub = "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$u_agent)) 
    { 
        $bname = 'Mozilla Firefox'; 
        $ub = "Firefox"; 
    } 
    elseif(preg_match('/Edge/i',$u_agent)) 
    { 
        $bname = 'Edge'; 
        $ub = "Edge"; 
    } 
    elseif(preg_match('/OPR/i',$u_agent)) 
    { 
        $bname = 'Opera'; 
        $ub = "OPR"; 
    } 
    elseif(preg_match('/Chrome/i',$u_agent)) 
    { 
        $bname = 'Google Chrome'; 
        $ub = "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$u_agent)) 
    { 
        $bname = 'Apple Safari'; 
        $ub = "Safari"; 
    } 
    elseif(preg_match('/Netscape/i',$u_agent)) 
    { 
        $bname = 'Netscape'; 
        $ub = "Netscape"; 
    }else{
    	$bname = 'Unknown'; 
    	$ub = "Unknown";
    }
    
    // finally get the correct version number
    $known = array('Version', $ub, 'other');
    $pattern = '#(?<browser>' . join('|', $known) .
    ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
    if (!preg_match_all($pattern, $u_agent, $matches)) {
        // we have no matching number just continue
    }
    
    // see how many we have
    $i = count($matches['browser']);
    if ($i != 1) {
        //we will have two since we are not using 'other' argument yet
        //see if version is before or after the name
        if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
            $version= $matches['version'][0];
        }
        else {
            $version= $matches['version'][1];
        }
    }
    else {
        $version= $matches['version'][0];
    }
    
    // check if we have a number
    if ($version==null || $version=="") {$version="?";}
    
    return array(
        'userAgent' => $u_agent,
        'name'      => $bname,
        'version'   => $version,
        'platform'  => $platform,
        'pattern'    => $pattern
    );
}
function doSetCharsetToUTF8() {
	$query = "
		SET 
			character_set_results = 'utf8', 
			character_set_client = 'utf8', 
			character_set_connection = 'utf8', 
			character_set_database = 'utf8', 
			character_set_server = 'utf8'
	";
	$result = DB::doQuery($query);		
}

function isUTF8Collation($tableName) {
	$return = showTableLike($tableName);
	if(preg_match('/^utf8/', $return['Collation'])) {
		return true;
	}
	return false;
}

function showTableLike($tableName) {
	$result = DB::getRow("show table status like '".$tableName."'");
	return $result;
}

if(!function_exists('unserializeBase64DecodeArray')){
	function unserializeBase64DecodeArray($strBetArray){
		if(empty($strBetArray) || !is_array($strBetArray)){ return false; }
		$newArray = array();
		foreach($strBetArray as $key => $value){
			$newArray[$key] = unserialize(base64_decode($value));
		}
		return $newArray;
	}

}

if(!function_exists('addProtocolCommon')){
	function addProtocolCommon($URL) {
		$URL = trim($URL);
		return (substr($URL, 0, 7) == 'http://' || substr($URL, 0, 8) == 'https://')
			? $URL
			: 'http://'.$URL;
	}
}

if(!function_exists('getURLPartsCommon')){
	function getURLPartsCommon($URL) {
		$URL = addProtocolCommon($URL);
		return parse_url($URL);
	}
}

if(!function_exists('autoFillInstallCloneCommonCpanel')){
	function autoFillInstallCloneCommonCpanel($params){
		include_once APP_ROOT."/lib/xmlapi.php";
		global $xmlapi, $ID, $DBNames,$DBUserNames,$rootDir,$dbName,$dbUser,$cpUser,$mainDomain,$subDomainFlg,$newCronKey,$lastError;
		$params['cpHost'] = $params['cpHost'];
		$rootDir = 'public_html/';
		$cpUser = trim($params['cpUser']);
		$cpPass = $params['cpPass'];
		$siteInfo = getURLPartsCommon($params['cpHost']);
		$host =  str_replace(array('http://','https://', 'www.','/cpanel/','/cpanel'), '', trim($siteInfo['host'], '/'));
		$hostName = trim($host);
		if(!defined('USERNAME')) { define('USERNAME', $cpUser); }
		
		$prefix = substr(USERNAME, 0, 8)."_clone_";
		$xmlapi = new xmlapi($hostName);
		$xmlapi->set_port( 2083 );
		$xmlapi->set_output( 'json' );
		$xmlapi->password_auth(USERNAME, $cpPass);
		$xmlapi->set_debug(1);

		$primaryHosts = apiGenCommon("DomainLookup", "getmaindomain"); //DomainLookup::getmaindomain
		$primaryHost = $primaryHosts['cpanelresult']['data'][0];
		$mainDomain = $primaryHost['main_domain'];
		$host =  str_replace(array('http://','https://', 'www.'), '', trim($mainDomain, '/'));
		$mainDomain = trim($host);

		$appDomainPath = $mainDomain;
		$destURL = "http://".$mainDomain.'/';
		$path = '/'.$rootDir;
		$stats = apiGenCommon("StatsBar", "stat", array('display' => 'sqldatabases'));
		$sqlInfo = $stats['cpanelresult']['data'][0];
		if($sqlInfo['_max'] == 'unlimited' ||  ($sqlInfo['_max'] - $sqlInfo['_count'] >= 1)){
			for($i=0; $i<=3; $i++){
				//create database
				if(!$DBNames){
					if ($i!=3) {
						$dbName = $prefix.substr( md5(rand()), 0, 5).$i;	
					}else{
						$dbName = USERNAME."_clone_".substr( md5(rand()), 0, 5).$i;
					}
					$addDB = apiGenCommon( "MysqlFE", "createdb", array('db' => $dbName));
					if(!empty($addDB['cpanelresult']['error'])) {
						$DBNames = false;
						$dbName = '';
						$lastError = $addDB['cpanelresult']['error'];
						if(strpos($addDB['cpanelresult']['error'], 'database name already exists') !== false){ } else {continue;}
					} else {
						$DBNames = true;
						break;
					}
				}
			}
			if($DBNames) {
				for($i=0; $i<=3; $i++){
					//create user
					if(!$DBUserNames){
						if ($i != 3) {
							$dbUser = substr(USERNAME, 0, 8).'_'.substr( md5(rand()), 0, 3).$i;
						}else{
							$dbUser = USERNAME.'_'.substr( md5(rand()), 0, 3).$i;
						}
						$dbPass = generateRandomPassword();

						//$addUser = apiGenCommon("Mysql", "adduser", array('username' => $dbUser, 'password' => $dbPass));
						$addUser = apiGenCommon("MysqlFE", "createdbuser", array('dbuser' => $dbUser, 'password' => $dbPass));
						$addUserArray = $addUser;
						if(!empty($addUser['cpanelresult']['error'])) {
							$DBUserNames = false;//exists in the database
							$dbUser = '';
							$lastError = $addUser['cpanelresult']['error'];
							if(strpos($addUser['cpanelresult']['error'], 'exists in the database') !== false){} else {continue;}
						} else {
							$DBUserNames = true;
							break;
						}
					}
				}
			}
		} else{
			echo json_encode(array("error" => 'cPanel error: MySQL database limit reached'));
			exit;
		}
		if(empty($DBNames)) {
			echo json_encode(array("error" => 'cPanel error: Failed to create the database('.$lastError.')'));
			exit;
		}
		if(empty($DBUserNames)) {
			echo json_encode(array("error" => 'cPanel error: Failed to create the database user('.$lastError.')'));
			exit;
		}
		//add user to database
		$linkUserDB = apiGenCommon("MysqlFE", "setdbuserprivileges", array('db' => $dbName, 'dbuser' => $dbUser, 'privileges' => 'all'));
		if(!empty($linkUserDB['cpanelresult']['error'])) {
			echo json_encode(array("error" => 'cPanel error: Failed to link DB and User'));
			exit;
		}
		$URLParts = parse_URL($params['cpHost']);
		if (!isset($URLParts['host'])) {
			$cpHost = $URLParts['path'];
		} else {
			$cpHost = $URLParts['host'];
		}
		if ( strlen(USERNAME) > 8) {
			$userName = trim(USERNAME);
			$shortUserName = substr(USERNAME, 0, 8);
		} else{
			$shortUserName = USERNAME;
		}
		$exportArray = array();
		$exportArray['cpUser'] = $params['cpUser'];
		$exportArray['cpPass'] = $params['cpPass'];
		$exportArray['cpHost'] = $cpHost;
		$exportArray['destURL'] = $destURL;
		$exportArray['path'] = $path;
		$exportArray['dbName'] = $dbName;
		$exportArray['dbUser'] = $dbUser;
		$exportArray['dbPass'] = $dbPass;
		return $exportArray;
	}
}


if(!function_exists('apiGenCommon')){
	function apiGenCommon($mod, $func, $var = array()){
	    global $xmlapi;

	    try{
	        $apiQuery = $xmlapi->api2_query(USERNAME, $mod, $func, $var);
	        $apiQuery = json_decode($apiQuery, true);
	        if($apiQuery['error']){
	            if($func == 'addsubdomain' || $func == 'search' || $func == 'mkdir'){
	                    return false;
	            }
	            $errorMsg = array('mod' => $mod, 'func' => $func);
	            $apiArray = (array)$apiQuery['error'];
	            echo json_encode(array('error' => 'cPanel error: '.$apiQuery['error']));
	            exit;
	        } else {
	            if($func == 'listfiles' || $func == 'listdbs' || $func == 'stat'|| $mod == 'MysqlFE' || $func == 'stat' || $func == 'getmaindomain' || $func == 'search'){ //"DomainLookup", "getmaindomain"
	                return $apiQuery;
	            }
	        }
	    }
	    catch(Exception $e){
	        echo json_encode(array("error" => 'cPanel error: '.$e->getMessage()));
	        exit;
	    }
	    return true;
	}
}

function fileSystemRemoveFiles($filesData){
	if(!is_object($GLOBALS['FileSystemObj'])){
		if(!initFileSystem()){
			appUpdateMsg('Unable to initiate file system.', true);
			return false;
		}
	}

	if (!isset($GLOBALS['updatePanelLocation'])) {
		$GLOBALS['updatePanelLocation'] = $GLOBALS['FileSystemObj']->findFolder(APP_ROOT);
		$GLOBALS['updatePanelLocation'] = removeTrailingSlash($GLOBALS['updatePanelLocation']);
	}
	if ( empty($filesData) || !is_array($filesData)) {
		return false;
	}
	foreach ($filesData as $file => $extra) {
		if($extra['type'] == 'f'){
			$GLOBALS['FileSystemObj']->delete($GLOBALS['updatePanelLocation'].$file, false, 'f');
		} else if($extra['type'] == 'd'){
			$GLOBALS['FileSystemObj']->delete($GLOBALS['updatePanelLocation'].$file, true);
		}
	}
}

if(!function_exists('gzdecode') &&  function_exists('gzinflate')){
   function gzdecode($data) { 
       return @gzinflate(substr($data,10,-8)); 
   } 
}

function checkDBStatus(){
	if(!isDBStatusCheckedToday()){
	//Checking the main three tables 
	$needCheckTables = array('?:history','?:history_raw_details','?:history_additional_data');
	foreach($needCheckTables as $checkingTable){
		$individualResults = DB::getArray('CHECK TABLE '.$checkingTable);
		if(!empty($individualResults) && is_array($individualResults)){
			foreach ($individualResults as $results){
				if(isset($results['Msg_text']) && $results['Msg_text']!='OK' ){
					die('One or more database tables connected to your IWP installation have crashed or been removed. Please repair the crashed tables or get in touch with your hosting provider to get the crashed tables fixed. For missing tables, please get in touch with IWP Support');
				}
			}
		}
	}
	updateOption('isDBStatusCheckedToday', date('Y-m-d'));
	}
}

function isDBStatusCheckedToday(){
	@date_default_timezone_set('GMT'); //gmdate(time());
	$today = date('Y-m-d');
	$lastCheckedDate = getOption('isDBStatusCheckedToday');
	$isCheckedToday  = ($today == $lastCheckedDate)?true:false ;
	return $isCheckedToday;
}

function generateRandomPassword( $length = 8 ) {
    $str_set = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%&*()-=+;:,.?";
    $pass = array(); //remember to declare $pass as an array
    if (defined('IWP_AUTO_FILL_PASSWORD_STRENGTH') && IWP_AUTO_FILL_PASSWORD_STRENGTH > $length) {
    	$length = IWP_AUTO_FILL_PASSWORD_STRENGTH;
    }
    $alphaLength = strlen($str_set) - 1; //put the length -1 in cache
    for ($i = 0; $i < $length; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $str_set[$n];
    }
    return implode($pass);
}

function isJsonCompitable($siteID){
	if (!empty($siteID)) {
		$wherePluginVersion = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
	       				)
	    			);
		$clientVersion = DB::getField("?:sites", "pluginVersion", $wherePluginVersion);
		if (version_compare($clientVersion, '1.6.1.1','>')) {
			return true;
		}	
	}
	return false;
}

function serverConnectionChecking(){
    $execute = new Execute(array());
    $result = $execute->serverRequirement();
    return $result;
}

function downUpperConnectionLevel($mode){
	if ($mode == 'command') {
		$execute = new Socket('all');
		$result = $execute->functionCheck;
		if (!$result) {
			 return 'curlMode';
		}
		return 'socketMode';
	}elseif ($mode == 'socket') {
		return 'curlMode';
	}
	return '';
}

function getHelpLink($actionData = array(), $type = false, $action = false, $error = false, $errorMsg = false ){
	$supportDocs = unserialize(getOption("supportDocs"));
	$isShowSupportDocs = getOption("isShowSupportDocs");
	$isShowSupportSearch = getOption("isShowSupportSearch");
	if (!empty($isShowSupportDocs) && !empty($isShowSupportDocs)) {
		if (!empty($actionData)) {
			$type = $actionData['type'];
			$action = $actionData['action'];
			$error = $actionData['error'];
			$errorMsg = $actionData['errorMsg'];
		}

		if (!empty($supportDocs[$type][$action][$error][$errorMsg])) {
			return $supportDocs[$type][$action][$error][$errorMsg];
		}elseif(!empty($supportDocs[$type][$action][$error])){
			return $supportDocs[$type][$action][$error];
		}elseif(!empty($supportDocs[$type][$action][$errorMsg])){
			return $supportDocs[$type][$action][$errorMsg];
		}elseif(!empty($supportDocs[$action][$error])){
			return $supportDocs[$action][$error];
		}elseif (!empty($supportDocs[$type][$error])) {
			return $supportDocs[$type][$error];
		}elseif (!empty($supportDocs[$type][$errorMsg])) {
			return $supportDocs[$type][$errorMsg];
		}elseif (!empty($supportDocs[$error])) {
			return $supportDocs[$error];
		}elseif (!empty($supportDocs[$errorMsg])) {
			return $supportDocs[$errorMsg];
		}
	}
	return false;
}

function isIWPDebugModeEabled(){
	if (defined('IWP_DEBUG_MODE') && IWP_DEBUG_MODE == true) {
		return true;
	}

	return false;
}
function maybeCompress($value){
	if(!function_exists('gzdeflate') || !function_exists('gzinflate')){
		return $value;
	}

	$value = gzdeflate($value);
	return base64_encode($value);
}

function maybeUnCompress($value){
	if(!function_exists('gzinflate') || !function_exists('gzinflate')){
		return $value;
	}
	$decoded = base64_decode($value);
	if (function_exists('mb_strpos')) {
		$is_gzip = 0 === mb_strpos($decoded, "\x1f" . "\x8b" . "\x08", 0, "US-ASCII");
		if ($is_gzip == 0) {
			return $value;
		}
	}
	$unzip = @gzinflate($decoded);
	if ($unzip == false) {
		return $value;
	}
	return $unzip;
}

function appendScheduleBackupName($actionDetails, &$string){
	if (!function_exists('scheduleBackupGetNextSchedule') || empty($actionDetails)) {
		return false;
	}
	foreach($actionDetails as $detailedAction => $detailedActionStat){
		$where = array(
			      		'query' =>  "scheduleKey=':scheduleKey'",
			      		'params' => array(
			               ':scheduleKey'=>$detailedActionStat['uniqueName']
	       				)
	    			);
		$backupName = DB::getField("?:backup_schedules", "backupName", $where);
		if (!empty($backupName)) {
			$string.=' ('.$backupName.')';
		}
	}
}
function saveParentSiteIDByChild($siteHome, $siteID){
 	$url = trim($siteHome, '/');
 	$urlWithoutLastPart = explode('/', $url);
 	array_pop($urlWithoutLastPart);
 	$urlWithoutLastPart = implode('/', $urlWithoutLastPart); 
 	$urlWithoutprotocal = str_ireplace(array('www.', 'http://', 'https://'), '', $url);
 	 $where = array(
      		'query' =>  "URL=':URL' OR URL=':urlWithoutLastPart' OR formatedURL=':urlWithoutprotocal'",
      		'params' => array(
               ':URL'=>$url,
               ':urlWithoutLastPart' => $urlWithoutLastPart,
               ':urlWithoutprotocal' => $urlWithoutprotocal
				)
		);
 	$parentSiteID = DB::getField("?:multisites", "parentSiteID", $where);

	if (!empty($parentSiteID)) {
		$where = array(
			      		'query' =>  "siteID=':siteID'",
			      		'params' => array(
			               ':siteID'=>$siteID
	       				)
	    			);
		$params['parentSiteID']  = $parentSiteID;
		$result = DB::update("?:sites", $params, $where);
	}

}
function clearUploadsDir(){
	$filesCount = 0;
	$uploadDir = APP_ROOT.'/uploads/';
	$extensions = getUploadsDirResetExtension();
	if (empty($extensions) || !is_array($extensions)) {
		return false;
	}
	foreach (scandir($uploadDir) as $item) {
		foreach ($extensions as $key => $ext) {
			$this_pos = strrpos($item, $ext);
			if($this_pos !== false)
			{
			  if(substr($item, $this_pos) == $ext)
			  {
			  	$filesCount ++;
			  	echo "<br>";
			  	echo $uploadDir.$item;
			  	echo "<br>";
				unlink($uploadDir.$item);
			  }
			}
		}
	}

	return $filesCount;
}

function getUploadsDirResetExtension(){
	$extensions = array('.zip', '.tmp', '.swp');
	if (defined('UPLOADS_DIR_RESET_EXTENSIONS')) {
		$extensions = trim(UPLOADS_DIR_RESET_EXTENSIONS);
		$extensions = explode(',', $extensions);
	}
	if (empty($extensions) || !is_array($extensions)) {
		return false;
	}

	return $extensions;
}

function printDebugLog($oneAction){
	$enableMulticallDebug = getOption('enableMulticallDebug');
	if (empty($enableMulticallDebug)) {
		return false;
	}
	if ($oneAction['detailedAction'] == 'backup' || $oneAction['detailedAction'] == 'multiCallRunTask' || $oneAction['errorMsg'] == 'multiCallWaiting') {
		echo "<a href = '".trim(APP_URL, '/')."/debug-chart/?type=backup&historyID=".$oneAction['historyID']."' target='_blank'>Panel Debug Chart</a>";
		$historyData  = getHistory($oneAction['historyID']);
		$siteData = getSiteData($historyData['siteID']);
		$siteURL = $siteData['URL'];
		echo "<br><a href = '".trim($siteURL, '/')."/wp-content/plugins/iwp-client/debug-chart/?historyID=".$oneAction['historyID']."' target='blank'>Site Debug Chart</a>";
	}
}
function getBackupDefaultExcludeTables(){
	$defaultExcludeTables = 'bwps_log,statpress,slim_stats,redirection_logs,Counterize,Counterize_Referers,Counterize_UserAgents,wbz404_logs,wbz404_redirects,tts_trafficstats,tts_referrer_stats,wponlinebackup_generations,svisitor_stat,simple_feed_stats,itsec_log,relevanssi_log,blc_instances,wysija_email_user_stat,woocommerce_sessions,et_bloom_stats,redirection_404,iwp_file_list,wptc_activity_log,wptc_current_process,wptc_processed_iterator,wptc_processed_restored_files';

	$defaultExcludeTables = explode(',', $defaultExcludeTables);
	return $defaultExcludeTables;
}

function getLastPanelUpdated(){
	$time = getOption('lastPanelUpdated');
	if (empty($time)) {
		return false;
	}
	$date = @date('j M Y h:i:s A', $time);
	return $date;
}

function getSiteURLFromStatsBySiteID($siteID){
	$where = array(
		      		'query' =>  "siteID=':siteID'",
		      		'params' => array(
		               ':siteID'=>$siteID
       				)
    			);
	$stats = DB::getField("?:site_stats", "stats", $where);

	if (empty($stats)) {
		return false;
	}

	$stats = unserialize(base64_decode($stats));

	if (!empty($stats['site_url'])) {
		return $stats['site_url'];
	}

	return false;

}

function isRedirectedURLValid($url, $redirectedUrl){

	if (trim($url, '/') == trim($redirectedUrl, '/')) {
		return false;
	}

	$trimURL = trimProtocolAndQueryParams($url);
	$trimRedirectedUrl = trimProtocolAndQueryParams($redirectedUrl);

	if ($trimURL == $trimRedirectedUrl) {
		return true;
	}

	return false;
}

function trimProtocolAndQueryParams($url){
	$url = str_ireplace(array('www.', 'http://', 'https://', 'wp-load.php'), '', $url);
	$urlParts = parse_url($url);
	$constructedUrl = $urlParts['host'] . $urlParts['path'];
	$constructedUrl = trim($constructedUrl, '/');
	return $constructedUrl;
}

function trimURL($url){
	$url = str_ireplace(array('wp-load.php'), '', $url);
	$urlParts = parse_url($url);
	$constructedUrl = $urlParts['scheme'] . '://' . $urlParts['host'] . $urlParts['path'];
	$constructedUrl = trim($constructedUrl, '/');
	return $constructedUrl.'/';
}

function getIPResolver($url, $ip){
	$ip = trim($ip);
	$split = explode(":",$ip);
	if (!empty($split) && count($split) == 3) {
		return $ip;
	}

	if(!filter_var($ip, FILTER_VALIDATE_IP)) {
	 	return false;
	}
	
	$withoutProtocol = trimProtocolAndQueryParams($url);
	$urlParts = parse_url($url);
	$port = 80;
	if ($urlParts['scheme'] == 'https') {
		$port = 443;
	}

	$resolver = $withoutProtocol.':'.$port.":".$ip;
	return $resolver;

}

function array_next_element( $array, $value, $wrap = false ) {

	$firstElement = current($array);
    while ($element = current($array)) {
        $next = next($array);
        if ($element == $value && $next) {
            return $next;
        }
        $prev = $element;
    }

    if ($wrap && $value == $prev) {
    	return $firstElement;
    }

    return null;
}