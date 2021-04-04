<?php
/************************************************************
* Credits - WordPress
 ************************************************************/

function addTrailingSlash($string) {
	return removeTrailingSlash($string) . '/';
}

function removeTrailingSlash($string) {
	return rtrim($string, '/');
}

function isBinary( $text ) {
	return (bool) preg_match('|[^\x20-\x7E]|', $text); //chr(32)..chr(127)
}

if(!function_exists('appUpdateMsg')){
	function appUpdateMsg($msg, $isError=0){
		echo "\n".'<br>'.$msg;
	}
}

function getTempName($fileName = '', $dir = '') {
	if ( empty($dir) )
		$dir = getTempDir();
	$fileName = basename($fileName);
	if ( empty($fileName) )
		$fileName = time();

	$fileName = preg_replace('|\..*$|', '.tmp', $fileName);
	$fileName = $dir . getUniqueFileName($dir, $fileName);
	touch($fileName);
	return $fileName;
}

function getUniqueFileName( $dir, $fileName) {

	// separate the fileName into a name and extension
	$info = pathinfo($fileName);
	$ext = !empty($info['extension']) ? '.' . $info['extension'] : '';
	$name = basename($fileName, $ext);

	// edge case: if file is named '.ext', treat as an empty name
	if ( $name === $ext )
		$name = '';

	// Increment the file number until we have a unique file to save in $dir. Use callback if supplied.
	
	$number = '';

	// change '.ext' to lower case
	if ( $ext && strtolower($ext) != $ext ) {
		$ext2 = strtolower($ext);
		$fileName2 = preg_replace( '|' . preg_quote($ext) . '$|', $ext2, $fileName );

		// check for both lower and upper case extension or image sub-sizes may be overwritten
		while ( file_exists($dir . "/$fileName") || file_exists($dir . "/$fileName2") ) {
			$newNumber = $number + 1;
			$fileName = str_replace( "$number$ext", "$newNumber$ext", $fileName );
			$fileName2 = str_replace( "$number$ext2", "$newNumber$ext2", $fileName2 );
			$number = $newNumber;
		}
		return $fileName2;
	}

	while ( file_exists( $dir . "/$fileName" ) ) {
		if ( '' == "$number$ext" )
			$fileName = $fileName . ++$number . $ext;
		else
			$fileName = str_replace( "$number$ext", ++$number . $ext, $fileName );
	}
	

	return $fileName;
}

function getTempDir() {
	static $temp;
	if ( defined('TEMP_DIR') )
		return addTrailingSlash(TEMP_DIR);

	if ( $temp )
		return addTrailingSlash($temp);

	$temp = dirname(__FILE__).'/clone_temp/';//dirname(__FILE__) = clone_controller folder
	if ( is_dir($temp) && @is_writable($temp) ){
		//$isTrue = mkdir(dirname(__FILE__).'/clone_temp/');
		// if ($isTrue)
		return $temp;
	}

	if  ( function_exists('sys_get_temp_dir') ) {
		$temp = sys_get_temp_dir();
		if ( @is_writable($temp) )
			return addTrailingSlash($temp);
	}

	$temp = ini_get('upload_tmp_dir');
	if ( is_dir($temp) && @is_writable($temp) )
		return addTrailingSlash($temp);

	$temp = '/tmp/';
	if ( is_dir($temp) && @is_writable($temp) )
		return addTrailingSlash($temp);
	
	
	die(status('Unable to write files. Please set 777 permission to "/clone_controller/clone_temp" directory in the clone destination and try again.', $success=false, $return=true));
	
	return addTrailingSlash($temp);
}

function getTempDirFromFTP(){//currently not used

	//try creating new folder in FTP with write permission
	$FTPOpt = array('hostname' => APP_FTP_HOST, 'port' => APP_FTP_PORT, 'username' => APP_FTP_USER, 'password' => APP_FTP_PASS, 'base' => APP_FTP_BASE, 'connectionType' => (defined('APP_FTP_SSL') && APP_FTP_SSL) ? 'ftps' : '');//same array used in getFileSystemMethod()
	
	if ( ! defined('FS_CONNECT_TIMEOUT') )
		define('FS_CONNECT_TIMEOUT', 30);
	if ( ! defined('FS_TIMEOUT') )
		define('FS_TIMEOUT', 30);	
	
	$FTPObj = new filesystemFTPExt($FTPOpt);
	if(!$FTPObj->connect()){//FTP will close at the end of getTempDirFromFTP()
		return false;
	}
	if($FTPObj->chdir(removeTrailingSlash(APP_FTP_BASE).'/clone_controller')){
		$temp = removeTrailingSlash(dirname(__FILE__)).'/clone_temp';//clone_controller/clone_temp folder
		if(!$FTPObj->mkDir(removeTrailingSlash(APP_FTP_BASE).'/clone_controller/clone_temp')){
			return false;
		}
		if ( is_dir($temp) && @is_writable($temp) ){
			return addTrailingSlash($temp);
		}
		if($FTPObj->chmod(removeTrailingSlash(APP_FTP_BASE).'/clone_controller/clone_temp', 0777)){
			if ( is_dir($temp) && @is_writable($temp) )
			return addTrailingSlash($temp);
		}
	}
	return false;
}

function downloadURL($URL, $filename, $prevMultiCallResponse = array(), $wpContentURL = false){
	if(empty($prevMultiCallResponse['file'])){
		if (strpos($URL, 'db.gz')) {
			$file = dirname(__FILE__).'/clone_temp/'.$URL;
			touch($file);
		}elseif (strpos($URL, 'more.zip')) {
			$file = dirname(__FILE__).'/clone_temp/'.$URL;
			touch($file);
		}else{
			$file = getTempName($filename);
		}
	} else{
		$file = $prevMultiCallResponse['file'];
	}
	
	$downloadResponseHeaders = array();	
	$downloaded = false;
	$downloaded = multiCallDownloadUsingCURL($URL, $file, $downloadResponseHeaders, $prevMultiCallResponse, $wpContentURL);
	
	if(!$downloaded){
		//Check fsockopen is allowed
		if (!function_exists('fsockopen')){
			die(status("Please enable fsockopen on your server.", false ,true));
		}

		iwp_mmb_auto_print('downloading_in_curl');

		$downloaded = multiCallDownloadUsingFsock($URL, $file, $downloadResponseHeaders, $prevMultiCallResponse);
	}
	
	if(!$downloaded){
		die(status('The file could not be downloaded. Change "/clone_controller/clone_temp" directory permission to 777 and try again.', false ,true));
	}
	else{
		checkdownloadResponseHeaders($downloadResponseHeaders);//it using die when invalid file download
	}
	return $downloaded;
}

function multiCallDownloadUsingCURL($URL, $file, &$downloadResponseHeaders, $prevResult = array(), $wpContentURL= false){
	if (!function_exists('curl_init') || !function_exists('curl_exec')){
		return false;
	}
	if(empty($prevResult['file'])){
		$fp = fopen ($file, 'wb');
	} else{
		$file = $prevResult['file'];
		$fp = fopen ($file, 'rb+');
		fseek($fp, $prevResult['startRange']);
	}
	if(!$fp){
		return false;
	}

	$isBreak = false;
	$isMultiPart = false;

	$startRange = (empty($prevResult['startRange']) && empty($prevResult['file']))? 0 : $prevResult['startRange'];
	$endRange = (empty($prevResult['endRange']) && empty($prevResult['file']))? 10000000 : $prevResult['endRange'];
	if ($wpContentURL && !strpos($URL, $GLOBALS['WPVersionName'])) {
		$URL = $wpContentURL.'/infinitewp/backups/'.$URL;
		if (!empty($GLOBALS['URLParseData'])) {
			$URL = buildFolderAuthURL($GLOBALS['URLParseData'], $URL);
		}
	}
	$totalFileSize = curl_get_file_size($URL);
	if (strpos($URL, $GLOBALS['WPVersionName'])) {
		$endRange = $totalFileSize;
	}
	status("Downloading file ".$URL, $success=true, $return=false);
	status("Total size of the file ".$totalFileSize, $success=true, $return=false);
	status("Download start from ".$startRange, $success=true, $return=false);
	if ($_REQUEST['oneShotdownlaod']) {
		$endRange = $totalFileSize;
	}

	do{
		$ch = curl_init($URL);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
													'Connection: Keep-Alive',
													'Keep-Alive: 115'
												));
		$rangeVariable = $startRange . '-' . $endRange;
		curl_setopt($ch, CURLOPT_RANGE, $rangeVariable);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
		$callResponse = curl_exec($ch);
		//write in file
		$currentOffest = (empty($startRange)) ? 0 : $startRange;
		@fseek($fp, $currentOffest, SEEK_SET);
		@fwrite($fp, $callResponse);
		$info = curl_getinfo($ch);
		curl_close($ch);

		if($info['http_code'] == '206'){
			//multiCallDownloadUsingCURL($URL, $file, $downloadResponseHeaders);
			$isMultiPart = true;
			$startRange = ftell($fp);
			$endRange = ($startRange + 10000000);
			if($endRange >= $totalFileSize){
				$endRange = $totalFileSize;
			}
			if($startRange == $endRange){
				$isMultiPart = false;
			}
		}
		$rangeVariable = $startRange . '-' . $endRange;
		$isBreak = check_for_clone_break();
	}
	while(!($isBreak) && $isMultiPart);

	fclose($fp);
	$currentResult = array();
	initialize_response_array($currentResult);
	$currentResult['file'] = $file;
	$currentResult['startRange'] = $startRange;
	$currentResult['endRange'] = $endRange;
	status("File Downloaded size:".$startRange, $success=true, $return=false);

	if($isBreak == true){
		$currentResult['status'] = 'partiallyCompleted';
		$currentResult['isDownloadMultiCall'] = true;
	}

	if(!$isMultiPart){
		$currentResult['isDownloadMultiCall'] = false;
	}
	$downloadResponseHeaders[] = "HTTP/1.1 ".$info['http_code']." SOMETHING";
	$downloadResponseHeaders[] = "Content-Type: ".$info['content_type'];
	return $currentResult;
}

function curl_get_file_size($url) {
  // Assume failure.

  $result = -1;

  $curl = curl_init( $url );

  // Issue a HEAD request and follow any redirects.
  curl_setopt( $curl, CURLOPT_NOBODY, true );
  curl_setopt( $curl, CURLOPT_HEADER, true );
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, true );
  curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
  curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
  curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0' );

  $data = curl_exec( $curl );
  $curlInfo = array();
  $curlInfo['info'] = curl_getinfo($curl);
  if(curl_errno($curl)){
  	$curlInfo['errorNo'] = curl_errno($curl);
  	$curlInfo['error'] = curl_error($curl);
  }
  curl_close( $curl );
  if( $data ) {
    $content_length = "unknown";
    $status = "unknown";

    if( preg_match( "/Content-Length: (\d+)/", $data, $matches ) ) {
      $content_length = (int)$matches[1];
    }
    if ($content_length == 'unknown' && preg_match( "/content-length: (\d+)/", $data, $matches )) {
    	$content_length = (int)$matches[1];
    }
    $status = $curlInfo['info']['http_code'];
    // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
    if( $status == 200 || ($status > 300 && $status <= 308) ) {
      $result = $content_length;
    }
  }
  if ($result == -1) {
  	$headers = @get_headers($url, 1);
	if (!empty($headers["Content-Length"]) && $headers["Content-Length"]>0) {
		return $headers["Content-Length"];
	}
  }
  if ($result == -1) {
  		$content_length = (int)$curlInfo['info']['download_content_length'];
  		if ($content_length >0) {
  			return $content_length;
  		}
  }
  return $result;
}

function multiCallDownloadUsingFsock($infile, $outfile, &$downloadResponseHeaders, $prevResult = array()){
    $chunksize = 512;  // 1 Meg

    /**
     * parse_url breaks a part a URL into it's parts, i.e. host, path,
     * query string, etc.
     */
    $parts     = parse_url($infile);
    $i_handle  = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
	if(empty($prevResult)){
		$o_handle = fopen ($outfile, 'wb');
	} else{
		$o_handle  = fopen($outfile, 'rb+');
    }
    if ($i_handle == false || $o_handle == false) {
        return false;
    }
    if (!empty($parts['query'])) {
        $parts['path'] .= '?' . $parts['query'];
    }

    /**
     * Send the request to the server for the file
     */
    $request = "GET {$parts['path']} HTTP/1.0\r\n";
    $request .= "Host: {$parts['host']}\r\n";
	$request .= "User-agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
    $request .= "Keep-Alive: 115\r\n";
    $request .= "Connection: keep-alive\r\n\r\n";
    fwrite($i_handle, $request);

    /**
     * Now read the headers from the remote server. We'll need
     * to get the content length.
     */
    $headers = array();
    while (!feof($i_handle)) {
        $line = fgets($i_handle);
        if ($line == "\r\n")
            break;
        $headers[] = $line;
    }
	$downloadResponseHeaders = $headers;
  /**
     * Look for the Content-Length header, and get the size
     * of the remote file.
     */
    $length = 0;
    foreach ($headers as $header) {
        if (stripos($header, 'Content-Length:') === 0) {
            $length = (int) str_replace('Content-Length: ', '', $header);
            break;
        }
    }

    /**
     * Start reading in the remote file, and writing it to the
     * local file one chunk at a time.
     */
    $cnt = 0;
	$is_break = false;

	$iOffset = empty($prevResult['iOffset']) ? 0 : $prevResult['iOffset'];
	$oOffset = empty($prevResult['oOffset']) ? 0 : $prevResult['oOffset'];
	if(!empty($prevResult['iOffset'])){
		fseek($i_handle, $iOffset);
		fseek($o_handle, $oOffset);
	}
	$count = empty($prevResult['count']) ? 0 : $prevResult['count'];
    while (!feof($i_handle) && !$is_break) {
		$count++;
        iwp_mmb_auto_print('fsock_download');
        $buf   = '';
        $buf   = fread($i_handle, $chunksize);
        $bytes = fwrite($o_handle, $buf);
        if ($bytes == false) {
	return false;
        }
        $cnt += $bytes;
        /**
         * We're done reading when we've reached the content length
         */
		$is_break = check_for_clone_break();
        if ($length && $cnt >= $length || $is_break){
            break;
		}
    }

	$currentResult = array();
	initialize_response_array($currentResult);
	$currentResult['file'] = $outfile;
	$currentResult['iOffset'] = ftell($i_handle);
	$currentResult['oOffset'] = ftell($o_handle);
	$currentResult['cloneDownloadType'] = 'fsock';
	$currentResult['count'] = $count;
	if($is_break == true){
		$currentResult['status'] = 'partiallyCompleted';
		$currentResult['isDownloadMultiCall'] = true;
	}
    fclose($i_handle);
    fclose($o_handle);
    return $currentResult;
}

function downloadUsingCURL($URL, $file, &$downloadResponseHeaders){
	
	if (!function_exists('curl_init') || !function_exists('curl_exec')) return false;
	
	$fp = fopen ($file, 'w');
	if(!$fp){ return false; }
	$ch = curl_init($URL);
	curl_setopt($ch, CURLOPT_TIMEOUT, 60);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
												'Connection: Keep-Alive',
												'Keep-Alive: 115'
											));
	
	$callResponse = curl_exec($ch);
	$info = curl_getinfo($ch);
	curl_close($ch);
	fclose($fp);

	if($callResponse == 1){
		$downloadResponseHeaders[] = "HTTP/1.1 ".$info['http_code']." SOMETHING";
		$downloadResponseHeaders[] = "Content-Type: ".$info['content_type'];
		return true;
	}
	return false;
	
}

function downloadUsingFsock($infile, $outfile, &$downloadResponseHeaders){
	$chunksize = 1024 * 1024;  // 1 Meg
	
	/**
	 * parse_url breaks a part a URL into it's parts, i.e. host, path,
	 * query string, etc.
	 */
	$parts     = parse_url($infile);
	$i_handle  = fsockopen($parts['host'], 80, $errstr, $errcode, 5);
	$o_handle  = fopen($outfile, 'wb');
	
	if ($i_handle == false || $o_handle == false) {
		return false;
	}
	
	if (!empty($parts['query'])) {
		$parts['path'] .= '?' . $parts['query'];
	}
	
	/**
	 * Send the request to the server for the file
	 */
	$request = "GET {$parts['path']} HTTP/1.0\r\n";
	$request .= "Host: {$parts['host']}\r\n";
	$request .= "User-agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0". "\r\n";
	$request .= "Keep-Alive: 115\r\n";
	$request .= "Connection: keep-alive\r\n\r\n";
	fwrite($i_handle, $request);
	
	/**
	 * Now read the headers from the remote server. We'll need
	 * to get the content length.
	 */
	$headers = array();
	while (!feof($i_handle)) {
		$line = fgets($i_handle);
		if ($line == "\r\n")
			break;
		$headers[] = $line;
	}
	$downloadResponseHeaders = $headers;
	
  /**
	 * Look for the Content-Length header, and get the size
	 * of the remote file.
	 */
	$length = 0;
	foreach ($headers as $header) {
		if (stripos($header, 'Content-Length:') === 0) {
			$length = (int) str_replace('Content-Length: ', '', $header);
			break;
		}
	}
	
	/**
	 * Start reading in the remote file, and writing it to the
	 * local file one chunk at a time.
	 */
	$cnt = 0;
	while (!feof($i_handle)) {
		iwp_mmb_auto_print('fsock_download');
		$buf   = '';
		$buf   = fread($i_handle, $chunksize);
		$bytes = fwrite($o_handle, $buf);
		if ($bytes == false) {
			return false;
		}
		$cnt += $bytes;
		
		/**
		 * We're done reading when we've reached the content length
		 */
		if ($length && $cnt >= $length)
			break;
	}
	fclose($i_handle);
	fclose($o_handle);
	return $cnt;
}

function checkdownloadResponseHeaders($headers){
	$httpCodeChecked = false;
	foreach($headers as $line){
	  if(!$httpCodeChecked && stripos($line, 'HTTP/') !== false){
		  $matches = array();
		  preg_match('#HTTP/\d+\.\d+ (\d+)#', $line, $matches);
		  $httpCode = (int)$matches[1];
		  if($httpCode != 200 && $httpCode != 206){
			  die(status("Error while downloading the zip file HTTP error: ".$httpCode.".", false ,true));
		  }
		  $httpCodeChecked = true;
	  }

	  if(stripos($line, 'Content-Type') !== false){
		   //$contentType = trim(str_ireplace('Content-Type:', '', $line));
		   //if(strtolower($contentType) != 'application/zip')
		   if(stripos($line, 'application/zip') === false){
			  //die(status("Invalid zip type, please check file is downloadable.", false ,true));
			  $GLOBALS['downloadPossibleError'] = " Please check file is downloadable.";
		  }
	  }
	}
	return true;
}

function getFileSystemMethod($args = array(), $context = false) {
	//$method = defined('FS_METHOD') ? FS_METHOD : false; //Please ensure that this is either 'direct', 'ssh', 'ftpext' or 'ftpsockets'
	if (!empty($args['hostname']) && $GLOBALS['needFileSystem'] != true) {
		$method = 'direct';
	}
	if ($context == false && ! $method && $GLOBALS['needFileSystem'] != true) {
		$method = 'direct';
	}

	//if ( ! $method && isset($args['connectionType']) && 'ssh' == $args['connectionType'] && extension_loaded('ssh2') && function_exists('stream_get_contents') ) $method = 'ssh2';
	if ( ! $method && defined('APP_FTP_USE_SFTP') && APP_FTP_USE_SFTP == 1 ) $method = 'SFTPExt';
	if ( ! $method && extension_loaded('ftp') ) $method = 'FTPExt';
	//if ( ! $method && ( extension_loaded('sockets') || function_exists('fsockopen') ) ) $method = 'ftpsockets'; //Sockets: Socket extension; PHP Mode: FSockopen / fwrite / fread
	return $method;
}

function initFileSystem($args = false, $context = false){
	
	if(empty($args)){
		$args = array('hostname' => APP_FTP_HOST, 'port' => APP_FTP_PORT, 'username' => APP_FTP_USER, 'password' => APP_FTP_PASS, 'base' => APP_FTP_BASE, 'connectionType' => (defined('APP_FTP_SSL') && APP_FTP_SSL) ? 'ftps' : '', 'passive' => APP_FTP_PASV, 'hostKey' => APP_FTP_KEY);
	}

	$method = getFileSystemMethod($args, $context);

	if (!$method)
		return false;

	$method = "fileSystem".ucfirst($method);
	
	appUpdateMsg('Using '.$method.' file system..');
	
	$GLOBALS['FileSystemObj'] = new $method($args);

	//Define the timeouts for the connections. Only available after the construct is called to allow for per-transport overriding of the default.
	if ( ! defined('FS_CONNECT_TIMEOUT') )
		define('FS_CONNECT_TIMEOUT', 30);
	if ( ! defined('FS_TIMEOUT') )
		define('FS_TIMEOUT', 30);

	//if ( is_error($FileSystemObj->errors) && $FileSystemObj->errors->get_error_code() )
	//	return false;

	if ( !$GLOBALS['FileSystemObj']->connect() )
		return false; //There was an error connecting to the server.

	// Set the permission constants if not already set.
	if ( ! defined('FS_CHMOD_DIR') )
		define('FS_CHMOD_DIR', 0755 );
	if ( ! defined('FS_CHMOD_FILE') )
		define('FS_CHMOD_FILE', 0644 );

	return true;
}


function directToAnyFSCopyDir($from, $to, $skipList = array() ) {//$from => direct file system, $to => automatic filesystem

	$tempWorkingDirFS = new filesystemDirect('');	

	$dirList = $tempWorkingDirFS->dirList($from);

	$from = addTrailingSlash($from);
	$to = addTrailingSlash($to);
	
	$to = $GLOBALS['FileSystemObj']->findFolder($to);
	if($to === false){
		die(status("Could not find the directory(".$to.") using file system", $success=false, $return=true));	
	}	
	$to = addTrailingSlash($to);

	$skipRegex = '';
	foreach ( (array)$skipList as $key => $skipFile )
		$skipRegex .= preg_quote($skipFile, '!') . '|';

	if ( !empty($skipRegex) )
		$skipRegex = '!(' . rtrim($skipRegex, '|') . ')$!i';

	foreach ( (array) $dirList as $filename => $fileinfo ) {
				iwp_mmb_auto_print('tmp_dir_to_destination_copy', "Files Moved : ".$to . $filename);
		if ( !empty($skipRegex) )
			if ( preg_match($skipRegex, $from . $filename) )
				continue;

		if ( 'f' == $fileinfo['type'] ) {
			if ( ! directToAnyFSCopyFile($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$GLOBALS['FileSystemObj']->chmod($to . $filename, 0644);
				if ( ! directToAnyFSCopyFile($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ){
					die(status("Could not copy file: ".$from . $filename." ".$to . $filename, $success=false, $return=true));
					return false;//Could not copy file. $from . $filename $to . $filename
				}
			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$GLOBALS['FileSystemObj']->isDir($to . $filename) ) {
				if ( !$GLOBALS['FileSystemObj']->mkDir($to . $filename, FS_CHMOD_DIR) ){
					die(status("Could not create directory: ". $to . $filename, $success=false, $return=true));
					return false; //Could not create directory., $to . $filename
				}
			}
			$result = directToAnyFSCopyDir($from . $filename, $to . $filename, $skipList);
			if ( !$result )
				return false;
		}
	}
	return true;
}

function multicallFSCopyDirNew($from, $to, $skipList = array()){
	$tempWorkingDirFS = new filesystemDirect('');	

	$dirList = $tempWorkingDirFS->dirList($from);
	$from = addTrailingSlash($from);
	$to = addTrailingSlash($to);

	$from = $GLOBALS['FileSystemObj']->findFolder($from);
	$to = $GLOBALS['FileSystemObj']->findFolder($to);
	if($to === false){
		die(status("Could not find the directory(".$to.") using file system", $success=false, $return=true));	
	}	
	$from = addTrailingSlash($from);
	$to = addTrailingSlash($to);

	foreach ((array) $dirList as $filename => $fileinfo )  {

		$result = $GLOBALS['FileSystemObj'] ->chmod($from . $filename, 0777, true);
		$result = $GLOBALS['FileSystemObj'] ->move($from . $filename, $to . $filename, true);
		if (empty($result)) {
			die(status('Unable to move files.', $success=false, $return=true));
		}
	}
	return true;

}

function multicallFSCopyDir($from, $to, $skipList = array()){
	$tempWorkingDirFS = new filesystemDirect('');	

	$dirList = $tempWorkingDirFS->dirList($from);

	$from = addTrailingSlash($from);
	$to = addTrailingSlash($to);
	
	$to = $GLOBALS['FileSystemObj']->findFolder($to);
	if($to === false){
		die(status("Could not find the directory(".$to.") using file system", $success=false, $return=true));	
	}	
	$to = addTrailingSlash($to);
	$skipRegex = '';
	foreach ( (array)$skipList as $key => $skipFile )
		$skipRegex .= preg_quote($skipFile, '!') . '|';

	if ( !empty($skipRegex) )
		$skipRegex = '!(' . rtrim($skipRegex, '|') . ')$!i';

	foreach ( (array) $dirList as $filename => $fileinfo ) {
				iwp_mmb_auto_print('tmp_dir_to_destination_copy', "Files Moved : ".$to . $filename);
		if ( !empty($skipRegex) )
			if ( preg_match($skipRegex, $from . $filename) )
				continue;
		if ( 'f' == $fileinfo['type'] ) {
			if ( ! directToAnyFSCopyFile($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
				// If copy failed, chmod file to 0644 and try again.
				$GLOBALS['FileSystemObj']->chmod($to . $filename, 0644);
				if ( ! directToAnyFSCopyFile($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ){
					// die(status("Could not copy file: ".$from . $filename." ".$to . $filename, $success=false, $return=true));
					// return false;//Could not copy file. $from . $filename $to . $filename
				}else{
					$tempWorkingDirFS->delete($from . $filename );//recursive function will not try to copy agin in multicall
					$tempWorkingDirFS->delete($from);// recursive function will not try to list again already copied dir
				}
			}else{
					$tempWorkingDirFS->delete($from . $filename );//recursive function will not try to copy agin in multicall
					$tempWorkingDirFS->delete($from);// recursive function will not try to list again already copied dir

			}
		} elseif ( 'd' == $fileinfo['type'] ) {
			if ( !$GLOBALS['FileSystemObj']->isDir($to . $filename) ) {
				if ( !$GLOBALS['FileSystemObj']->mkDir($to . $filename, FS_CHMOD_DIR) ){
					die(status("Could not create directory: ". $to . $filename, $success=false, $return=true));
					return false; //Could not create directory., $to . $filename
				}
			}
			$result = multicallFSCopyDir($from . $filename, $to . $filename, $skipList);
			
			if ( !$result )
				return false;

		}
		$isBreak = check_for_clone_break();
		if ($isBreak) {
			$response_arr['break'] = true;
			return $response_arr;
		}
	}	
	return true;
}
function directToAnyFSCopyFile($source, $destination, $overwrite = false, $mode = false){
		//echo "<br>Final file : ".$destination;
		iwp_mmb_auto_print('tmp_dir_to_destination_copy', "Files Moved : ".$destination);
	if($GLOBALS['FileSystemObj']->method == 'direct'){
		return $GLOBALS['FileSystemObj']->copy($source, $destination, $overwrite, $mode);
	}
	elseif($GLOBALS['FileSystemObj']->method == 'FTPExt' || $GLOBALS['FileSystemObj']->method == 'ftpsockets' || $GLOBALS['FileSystemObj']->method == 'ssh2' || $GLOBALS['FileSystemObj']->method == 'sftp'){
		if ( ! $overwrite && $GLOBALS['FileSystemObj']->exists($destination) )
			return false;
		//$content = $this->get_contents($source);
//		if ( false === $content)
//			return false;
			
		//put content	
		//$tempfile = wp_tempnam($file);

		$sourceHandle = fopen($source, 'r');
		if ( ! $sourceHandle )
			return false;

		//fwrite($temp, $contents);
		//fseek($temp, 0); //Skip back to the start of the file being written to
		
		$sampleContent = fread($sourceHandle, (1024 * 1024 * 2));//1024 * 1024 * 2 => 2MB
		fseek($sourceHandle, 0); //Skip back to the start of the file being written to

		$type = isBinary($sampleContent) ? FTP_BINARY : FTP_ASCII;
		
		if($GLOBALS['FileSystemObj']->method == 'FTPExt'){
			$ret = ftp_fput($GLOBALS['FileSystemObj']->link, $destination, $sourceHandle, $type);
		} elseif($GLOBALS['FileSystemObj']->method == 'ssh2' || $GLOBALS['FileSystemObj']->method == 'sftp') {
			$ret = $GLOBALS['FileSystemObj']->putContents($destination, $sampleContent, $mode);
		}
		//elseif($GLOBALS['FileSystemObj']->method == 'ftpsockets'){
//			$GLOBALS['FileSystemObj']->ftp->SetType($type);
//			$ret = $GLOBALS['FileSystemObj']->ftp->fput($destination, $sourceHandle);
//		}
		unset($sampleContent);
		fclose($sourceHandle);
		unlink($source);//to immediately save system space
		//unlink($tempfile);
		
		if($mode){
			$GLOBALS['FileSystemObj']->chmod($destination, $mode);
		}

		return $ret;
		
		//return $this->put_contents($destination, $content, $mode);
	}
}



//==========================================================================>


class filesystemBase {
	
	var $verbose = false;
	
	var $cache = array();
	
	var $method = '';

	/**
	 * Locates a folder on the remote filesystem.
	 *
	 * Assumes that on Windows systems, Stripping off the Drive letter is OK
	 * Sanitizes \\ to / in windows filepaths.
	 *
	 * @access public
	 *
	 * @param string $folder the folder to locate
	 * @return string The location of the remote path.
	 */
	function findFolder($folder) {

		if (( stripos($this->method, 'ftp') !== false) || 'ssh2' == $this->method || 'sftp' == $this->method ) {
			$folder = str_replace(dirname(dirname(__FILE__)), APP_FTP_BASE, $folder);//dirname(dirname(__FILE__)) => one folder up to clone_controller
			$folder = str_replace('//', '/', $folder);//removing any // in the path
			return addTrailingSlash($folder);
		} elseif ( 'direct' == $this->method ) {
			$folder = str_replace('\\', '/', $folder); //Windows path sanitisation
			return addTrailingSlash($folder);
		}
		return false;
	}
	/**
	 * Returns the *nix style file permissions for a file
	 *
	 * From the PHP documentation page for fileperms()
	 *
	 * @link http://docs.php.net/fileperms
	 * @access public
	 *
	 * @param string $file string filename
	 * @return int octal representation of permissions
	 */
	function getHChmod($file){
		$perms = $this->getChmod($file);
		if (($perms & 0xC000) == 0xC000) // Socket
			$info = 's';
		elseif (($perms & 0xA000) == 0xA000) // Symbolic Link
			$info = 'l';
		elseif (($perms & 0x8000) == 0x8000) // Regular
			$info = '-';
		elseif (($perms & 0x6000) == 0x6000) // Block special
			$info = 'b';
		elseif (($perms & 0x4000) == 0x4000) // Directory
			$info = 'd';
		elseif (($perms & 0x2000) == 0x2000) // Character special
			$info = 'c';
		elseif (($perms & 0x1000) == 0x1000) // FIFO pipe
			$info = 'p';
		else // Unknown
			$info = 'u';

		// Owner
		$info .= (($perms & 0x0100) ? 'r' : '-');
		$info .= (($perms & 0x0080) ? 'w' : '-');
		$info .= (($perms & 0x0040) ?
					(($perms & 0x0800) ? 's' : 'x' ) :
					(($perms & 0x0800) ? 'S' : '-'));

		// Group
		$info .= (($perms & 0x0020) ? 'r' : '-');
		$info .= (($perms & 0x0010) ? 'w' : '-');
		$info .= (($perms & 0x0008) ?
					(($perms & 0x0400) ? 's' : 'x' ) :
					(($perms & 0x0400) ? 'S' : '-'));

		// World
		$info .= (($perms & 0x0004) ? 'r' : '-');
		$info .= (($perms & 0x0002) ? 'w' : '-');
		$info .= (($perms & 0x0001) ?
					(($perms & 0x0200) ? 't' : 'x' ) :
					(($perms & 0x0200) ? 'T' : '-'));
		return $info;
	}

	/**
	 * Converts *nix style file permissions to a octal number.
	 *
	 * Converts '-rw-r--r--' to 0644
	 * From "info at rvgate dot nl"'s comment on the PHP documentation for chmod()
	 *
	 * @link http://docs.php.net/manual/en/function.chmod.php#49614
	 * @access public
	 *
	 * @param string $mode string *nix style file permission
	 * @return int octal representation
	 */
	function getNumChmodFromH($mode) {
		$realMode = '';
		$legal =  array('', 'w', 'r', 'x', '-');
		$attArray = preg_split('//', $mode);

		for ($i=0; $i < count($attArray); $i++)
		   if ($key = array_search($attArray[$i], $legal))
			   $realMode .= $legal[$key];

		$mode = str_pad($realMode, 9, '-');
		$trans = array('-'=>'0', 'r'=>'4', 'w'=>'2', 'x'=>'1');
		$mode = strtr($mode,$trans);

		$newmode = '';
		$newmode .= $mode[0] + $mode[1] + $mode[2];
		$newmode .= $mode[3] + $mode[4] + $mode[5];
		$newmode .= $mode[6] + $mode[7] + $mode[8];
		return $newmode;
	}
	
	
	
	/**
	 * Unzips a specified ZIP file to a location on the Filesystem via the WordPress Filesystem Abstraction.
	 * Does not extract a root-level __MACOSX directory, if present.
	 *
	 * Attempts to increase the PHP Memory limit to 256M before uncompressing,
	 * However, The most memory required shouldn't be much larger than the Archive itself.
	 *
	 *
	 * @param string $file Full path and filename of zip archive
	 * @param string $to Full path on the filesystem to extract archive to
	 * @return mixed WP_Error on failure, True on success
	 */
	function unZipFile($file, $to) {
	
		if ( ! $GLOBALS['FileSystemObj'] || !is_object($GLOBALS['FileSystemObj']) ){
			//return new WP_Error('fs_unavailable', __('Could not access filesystem.'));
			appUpdateMsg('Could not access file system.', true);
		}
	
		// Unzip can use a lot of memory, but not this much hopefully
		@ini_set('memory_limit', '256M');
	
		$neededDirs = array();
		$to = addTrailingSlash($to);
	
		// Determine any parent dir's needed (of the upgrade directory)
		if ( ! $GLOBALS['FileSystemObj']->isDir($to) ) { //Only do parents if no children exist
			$path = preg_split('![/\\\]!', removeTrailingSlash($to));
			for ( $i = count($path); $i >= 0; $i-- ) {
				if ( empty($path[$i]) )
					continue;
	
				$dir = implode('/', array_slice($path, 0, $i+1) );
				if ( preg_match('!^[a-z]:$!i', $dir) ) // Skip it if it looks like a Windows Drive letter.
					continue;
	
				if ( ! $GLOBALS['FileSystemObj']->isDir($dir) )
					$neededDirs[] = $dir;
				else
					break; // A folder exists, therefor, we dont need the check the levels below this
			}
		}
	
		if ( class_exists('ZipArchive') ) {
			$result = $this->zipArchiveUnZip($file, $to, $neededDirs);
			if ( true === $result ) {
				return $result;
			} elseif ( $result != 'incompatible_archive' ) {
				return $result;
			}
		}
		// Fall through to PclZip if ZipArchive is not available, or encountered an error opening the file.
		return $this->pclZipUnZip($file, $to, $neededDirs);
	}
	
	/**
	 * This function should not be called directly, use unzip_file instead. Attempts to unzip an archive using the ZipArchive class.
	 *
	 * @see unzip_file
	 * @access private
	 *
	 * @param string $file Full path and filename of zip archive
	 * @param string $to Full path on the filesystem to extract archive to
	 * @param array $neededDirs A partial list of required folders needed to be created.
	 * @return mixed WP_Error on failure, True on success
	 */
	function zipArchiveUnZip($file, $to, $neededDirs = array() ) {
		//global $GLOBALS['FileSystemObj'];
		$z = new ZipArchive();
	
		// PHP4-compat - php4 classes can't contain constants
		$zopen = $z->open($file, /* ZIPARCHIVE::CHECKCONS */ 4);
		if ( true !== $zopen ){
			//return new WP_Error('incompatible_archive', __('Incompatible Archive.'));
			//appUpdateMsg('Incompatible Archive', true);
			return 'incompatible_archive';
		}
	
		for ( $i = 0; $i < $z->numFiles; $i++ ) {
			if ( ! $info = $z->statIndex($i) ){
				//return new WP_Error('stat_failed', __('Could not retrieve file from archive.'));
				appUpdateMsg('Could not retrieve file from archive.', true);
				return false;
			}
	
			if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Skip the OS X-created __MACOSX directory
				continue;
	
			if ( '/' == substr($info['name'], -1) ) // directory
				$neededDirs[] = $to . removeTrailingSlash($info['name']);
			else
				$neededDirs[] = $to . removeTrailingSlash(dirname($info['name']));
		}
	
		$neededDirs = array_unique($neededDirs);
		foreach ( $neededDirs as $dir ) {
			// Check the parent folders of the folders all exist within the creation array.
			if ( removeTrailingSlash($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
				continue;
			if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
				continue;
	
			$parentFolder = dirname($dir);
			while ( !empty($parentFolder) && removeTrailingSlash($to) != $parentFolder && !in_array($parentFolder, $neededDirs) ) {
				$neededDirs[] = $parentFolder;
				$parentFolder = dirname($parentFolder);
			}
		}
		asort($neededDirs);
	
		// Create those directories if need be:
		foreach ( $neededDirs as $_dir ) {
			if ( ! $GLOBALS['FileSystemObj']->mkDir($_dir, FS_CHMOD_DIR) && ! $GLOBALS['FileSystemObj']->isDir($_dir) ){ // Only check to see if the Dir exists upon creation failure. Less I/O this way.
				//return new WP_Error('mkdir_failed', __('Could not create directory.'), $_dir);
				appUpdateMsg('Could not create directory '.$_dir, true);
				return false;
			}
		}
		unset($neededDirs);
	
		for ( $i = 0; $i < $z->numFiles; $i++ ) {
			if ( ! $info = $z->statIndex($i) ){
				//return new WP_Error('stat_failed', __('Could not retrieve file from archive.'));
				appUpdateMsg('Could not retrieve file from archive', true);
				return false;
			}
	
			if ( '/' == substr($info['name'], -1) ) // directory
				continue;
	
			if ( '__MACOSX/' === substr($info['name'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
				continue;
	
			$contents = $z->getFromIndex($i);
			if ( false === $contents ){
				//return new WP_Error('extract_failed', __('Could not extract file from archive.'), $info['name']);
				appUpdateMsg('Could not extract '.$info['name'].' file from archive.', true);
				return false;
			}
	
			if ( ! $GLOBALS['FileSystemObj']->putContents( $to . $info['name'], $contents, FS_CHMOD_FILE) ){
				//return new WP_Error('copy_failed', __('Could not copy file.'), $to . $info['name']);
				appUpdateMsg('Could not copy file '.$to . $info['name'], true);
				return false;
			}
		}
	
		$z->close();
	
		return true;
	}
	
	/**
	 * This function should not be called directly, use unzip_file instead. Attempts to unzip an archive using the PclZip library.
	 *
	 * @since 3.0.0
	 * @see unzip_file
	 * @access private
	 *
	 * @param string $file Full path and filename of zip archive
	 * @param string $to Full path on the filesystem to extract archive to
	 * @param array $neededDirs A partial list of required folders needed to be created.
	 * @return mixed WP_Error on failure, True on success
	 */
	function pclZipUnZip($file, $to, $neededDirs = array()) {
		//global $GLOBALS['FileSystemObj'];
		// See #15789 - PclZip uses string functions on binary data, If it's overloaded with Multibyte safe functions the results are incorrect.
		if ( ini_get('mbstring.func_overload') && function_exists('mb_internal_encoding') ) {
			$previous_encoding = mb_internal_encoding();
			mb_internal_encoding('ISO-8859-1');
		}
	
		require_once(APP_ROOT . '/lib/pclzip.php');
	
		$archive = new PclZip($file);
	
		$archive_files = $archive->extract(PCLZIP_OPT_EXTRACT_AS_STRING);
	
		if ( isset($previous_encoding) )
			mb_internal_encoding($previous_encoding);
	
		// Is the archive valid?
		if ( !is_array($archive_files) ){
			//return new WP_Error('incompatible_archive', __('Incompatible Archive.'), $archive->errorInfo(true));
			appUpdateMsg('Incompatible Archive '.$archive->errorInfo(true), true);
			return false;
		}
	
		if ( 0 == count($archive_files) ){
			//return new WP_Error('empty_archive', __('Empty archive.'));
			appUpdateMsg('Empty archive', true);
			return false;
		}
	
		// Determine any children directories needed (From within the archive)
		foreach ( $archive_files as $file ) {
			if ( '__MACOSX/' === substr($file['filename'], 0, 9) ) // Skip the OS X-created __MACOSX directory
				continue;
	
			$neededDirs[] = $to . removeTrailingSlash( $file['folder'] ? $file['filename'] : dirname($file['filename']) );
		}
	
		$neededDirs = array_unique($neededDirs);
		foreach ( $neededDirs as $dir ) {
			// Check the parent folders of the folders all exist within the creation array.
			if ( removeTrailingSlash($to) == $dir ) // Skip over the working directory, We know this exists (or will exist)
				continue;
			if ( strpos($dir, $to) === false ) // If the directory is not within the working directory, Skip it
				continue;
	
			$parentFolder = dirname($dir);
			while ( !empty($parentFolder) && removeTrailingSlash($to) != $parentFolder && !in_array($parentFolder, $neededDirs) ) {
				$neededDirs[] = $parentFolder;
				$parentFolder = dirname($parentFolder);
			}
		}
		asort($neededDirs);
	
		// Create those directories if need be:
		foreach ( $neededDirs as $_dir ) {
			if ( ! $GLOBALS['FileSystemObj']->mkDir($_dir, FS_CHMOD_DIR) && ! $GLOBALS['FileSystemObj']->isDir($_dir) ){ // Only check to see if the dir exists upon creation failure. Less I/O this way.
				//return new WP_Error('mkdir_failed', __('Could not create directory.'), $_dir);
				appUpdateMsg('Could not create directory '.$_dir, true);
				return false;
			}
		}
		unset($neededDirs);
	
		// Extract the files from the zip
		foreach ( $archive_files as $file ) {
			if ( $file['folder'] )
				continue;
	
			if ( '__MACOSX/' === substr($file['filename'], 0, 9) ) // Don't extract the OS X-created __MACOSX directory files
				continue;
	
			if ( ! $GLOBALS['FileSystemObj']->putContents( $to . $file['filename'], $file['content'], FS_CHMOD_FILE) ){
				//return new WP_Error('copy_failed', __('Could not copy file.'), $to . $file['filename']);
				appUpdateMsg('Could not copy file '.$to . $file['filename'], true);
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Copies a directory from one location to another via the WordPress Filesystem Abstraction.
	 *
	 * @param string $from source directory
	 * @param string $to destination directory
	 * @param array $skipList a list of files/folders to skip copying
	 * @return mixed WP_Error on failure, True on success.
	 */
	function copyDir($from, $to, $skipList = array() ) {
		//global $GLOBALS['FileSystemObj'];
	
		$dirlist = $GLOBALS['FileSystemObj']->dirList($from);
	
		$from = addTrailingSlash($from);
		$to = addTrailingSlash($to);
	
		$skipRegex = '';
		foreach ( (array)$skipList as $key => $skipFile )
			$skipRegex .= preg_quote($skipFile, '!') . '|';
	
		if ( !empty($skipRegex) )
			$skipRegex = '!(' . rtrim($skipRegex, '|') . ')$!i';
	
		foreach ( (array) $dirlist as $filename => $fileinfo ) {
			if ( !empty($skipRegex) )
				if ( preg_match($skipRegex, $from . $filename) )
					continue;
	
			if ( 'f' == $fileinfo['type'] ) {
				if ( ! $GLOBALS['FileSystemObj']->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ) {
					// If copy failed, chmod file to 0644 and try again.
					$GLOBALS['FileSystemObj']->chmod($to . $filename, 0644);
					if ( ! $GLOBALS['FileSystemObj']->copy($from . $filename, $to . $filename, true, FS_CHMOD_FILE) ){
						//return new WP_Error('copy_failed', __('Could not copy file.'), $to . $filename);
						appUpdateMsg('Could not copy file '.$to . $file['filename'], true);
						return false;
					}
				}
			} elseif ( 'd' == $fileinfo['type'] ) {
				if ( !$GLOBALS['FileSystemObj']->isDir($to . $filename) ) {
					if ( !$GLOBALS['FileSystemObj']->mkDir($to . $filename, FS_CHMOD_DIR) ){
						//return new WP_Error('mkdir_failed', __('Could not create directory.'), $to . $filename);
						appUpdateMsg('Could not create directory '.$to . $filename, true);
						return false;
					}
				}
				$result = $this->copyDir($from . $filename, $to . $filename, $skipList);
				if ( $result == false )
					return $result;
			}
		}
		return true;
	}
}


//==========================================================================>


class filesystemDirect extends filesystemBase {

	function __construct($arg) {
		$this->method = 'direct';
		//$this->errors = new WP_Error();
		
	}

	function connect() {
		return true;
	}
	/**
	 * Reads entire file into a string
	 *
	 * @param string $file Name of the file to read.
	 * @return string|bool The function returns the read data or false on failure.
	 */
	function getContents($file) {
		return @file_get_contents($file);
	}
	/**
	 * Reads entire file into an array
	 *
	 * @param string $file Path to the file.
	 * @return array|bool the file contents in an array or false on failure.
	 */
	function getContentsArray($file) {
		return @file($file);
	}
	/**
	 * Write a string to a file
	 *
	 * @param string $file Remote path to the file where to write the data.
	 * @param string $contents The data to write.
	 * @param int $mode (optional) The file permissions as octal number, usually 0644.
	 * @return bool False upon failure.
	 */
	function putContents($file, $contents, $mode = false ) {
		if ( ! ($fp = @fopen($file, 'w')) )
			return false;
		@fwrite($fp, $contents);
		@fclose($fp);
		$this->chmod($file, $mode);
		return true;
	}
	/**
	 * Gets the current working directory
	 *
	 * @return string|bool the current working directory on success, or false on failure.
	 */
	function cwd() {
		return @getcwd();
	}
	/**
	 * Change directory
	 *
	 * @param string $dir The new current directory.
	 * @return bool Returns true on success or false on failure.
	 */
	function chdir($dir) {
		return @chdir($dir);
	}
	/**
	 * Changes file group
	 *
	 * @param string $file Path to the file.
	 * @param mixed $group A group name or number.
	 * @param bool $recursive (optional) If set True changes file group recursively. Defaults to False.
	 * @return bool Returns true on success or false on failure.
	 */
	function chgrp($file, $group, $recursive = false) {
		if ( ! $this->exists($file) )
			return false;
		if ( ! $recursive )
			return @chgrp($file, $group);
		if ( ! $this->isDir($file) )
			return @chgrp($file, $group);
		//Is a directory, and we want recursive
		$file = addTrailingSlash($file);
		$fileList = $this->dirList($file);
		foreach ($fileList as $fileName)
			$this->chgrp($file . $fileName, $group, $recursive);

		return true;
	}
	/**
	 * Changes filesystem permissions
	 *
	 * @param string $file Path to the file.
	 * @param int $mode (optional) The permissions as octal number, usually 0644 for files, 0755 for dirs.
	 * @param bool $recursive (optional) If set True changes file group recursively. Defaults to False.
	 * @return bool Returns true on success or false on failure.
	 */
	function chmod($file, $mode = false, $recursive = false) {
		if ( ! $mode ) {
			if ( $this->isFile($file) )
				$mode = FS_CHMOD_FILE;
			elseif ( $this->isDir($file) )
				$mode = FS_CHMOD_DIR;
			else
				return false;
		}

		if ( ! $recursive || ! $this->isDir($file) )
			return @chmod($file, $mode);
		//Is a directory, and we want recursive
		$file = addTrailingSlash($file);
		$fileList = $this->dirList($file);
		foreach ( (array)$fileList as $fileName => $filemeta)
			$this->chmod($file . $fileName, $mode, $recursive);

		return true;
	}
	/**
	 * Changes file owner
	 *
	 * @param string $file Path to the file.
	 * @param mixed $owner A user name or number.
	 * @param bool $recursive (optional) If set True changes file owner recursively. Defaults to False.
	 * @return bool Returns true on success or false on failure.
	 */
	function chown($file, $owner, $recursive = false) {
		if ( ! $this->exists($file) )
			return false;
		if ( ! $recursive )
			return @chown($file, $owner);
		if ( ! $this->isDir($file) )
			return @chown($file, $owner);
		//Is a directory, and we want recursive
		$fileList = $this->dirList($file);
		foreach ($fileList as $fileName) {
			$this->chown($file . '/' . $fileName, $owner, $recursive);
		}
		return true;
	}
	/**
	 * Gets file owner
	 *
	 * @param string $file Path to the file.
	 * @return string Username of the user.
	 */
	function owner($file) {
		$owneruid = @fileowner($file);
		if ( ! $owneruid )
			return false;
		if ( ! function_exists('posix_getpwuid') )
			return $owneruid;
		$ownerarray = posix_getpwuid($owneruid);
		return $ownerarray['name'];
	}
	/**
	 * Gets file permissions
	 *
	 * FIXME does not handle errors in fileperms()
	 *
	 * @param string $file Path to the file.
	 * @return string Mode of the file (last 4 digits).
	 */
	function getChmod($file) {
		return substr(decoct(@fileperms($file)),3);
	}
	function group($file) {
		$gid = @filegroup($file);
		if ( ! $gid )
			return false;
		if ( ! function_exists('posix_getgrgid') )
			return $gid;
		$grouparray = posix_getgrgid($gid);
		return $grouparray['name'];
	}

	function copy($source, $destination, $overwrite = false, $mode = false) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;

		$rtval = copy($source, $destination);
		if ( $mode )
			$this->chmod($destination, $mode);
		return $rtval;
	}

	function move($source, $destination, $overwrite = false) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;

		// try using rename first. if that fails (for example, source is read only) try copy
		if ( @rename($source, $destination) )
			return true;

		if ( $this->copy($source, $destination, $overwrite) && $this->exists($destination) ) {
			$this->delete($source);
			return true;
		} else {
			return false;
		}
	}

	function delete($file, $recursive = false, $type = false) {
		if ( empty($file) ) //Some filesystems report this as /, which can cause non-expected recursive deletion of all files in the filesystem.
			return false;
		$file = str_replace('\\', '/', $file); //for win32, occasional problems deleting files otherwise

		if ( 'f' == $type || $this->isFile($file) )
			return @unlink($file);
		if ( ! $recursive && $this->isDir($file) )
			return @rmdir($file);

		//At this point its a folder, and we're in recursive mode
		$file = addTrailingSlash($file);
		$fileList = $this->dirList($file, true);

		$retval = true;
		if ( is_array($fileList) ) //false if no files, So check first.
			foreach ($fileList as $fileName => $fileinfo){
				iwp_mmb_auto_print('recursive_delete');
				if ( ! $this->delete($file . $fileName, $recursive, $fileinfo['type']) )
					$retval = false;
			}
		if ( file_exists($file) && ! @rmdir($file) )
			$retval = false;
		return $retval;
	}

	function exists($file) {
		return @file_exists($file);
	}

	function isFile($file) {
		return @is_file($file);
	}

	function isDir($path) {
		return @is_dir($path);
	}

	function isReadable($file) {
		return @is_readable($file);
	}

	function isWritable($file) {
		return @is_writable($file);
	}

	function atime($file) {
		return @fileatime($file);
	}

	function mtime($file) {
		return @filemtime($file);
	}
	function size($file) {
		return @filesize($file);
	}

	function touch($file, $time = 0, $atime = 0) {
		if ($time == 0)
			$time = time();
		if ($atime == 0)
			$atime = time();
		return @touch($file, $time, $atime);
	}

	function mkDir($path, $chmod = false, $chown = false, $chgrp = false) {
		// safe mode fails with a trailing slash under certain PHP versions.
		$path = removeTrailingSlash($path);
		if ( empty($path) )
			return false;

		if ( ! $chmod )
			$chmod = FS_CHMOD_DIR;

		if ( ! @mkdir($path) )
			return false;
		$this->chmod($path, $chmod);
		if ( $chown )
			$this->chown($path, $chown);
		if ( $chgrp )
			$this->chgrp($path, $chgrp);
		return true;
	}

	function rmDir($path, $recursive = false) {
		return $this->delete($path, $recursive);
	}

	function dirList($path, $includeHidden = true, $recursive = false) {
		if ( $this->isFile($path) ) {
			$limitFile = basename($path);
			$path = dirname($path);
		} else {
			$limitFile = false;
		}

		if ( ! $this->isDir($path) )
			return false;

		$dir = @dir($path);
		if ( ! $dir )
			return false;

		$ret = array();

		while (false !== ($entry = $dir->read()) ) {
			$struc = array();
			$struc['name'] = $entry;

			if ( '.' == $struc['name'] || '..' == $struc['name'] )
				continue;

			if ( ! $includeHidden && '.' == $struc['name'][0] )
				continue;

			if ( $limitFile && $struc['name'] != $limitFile)
				continue;

			$struc['perms'] 	= $this->getHChmod($path.'/'.$entry);
			$struc['permsn']	= $this->getNumChmodFromH($struc['perms']);
			$struc['number'] 	= false;
			$struc['owner']    	= $this->owner($path.'/'.$entry);
			$struc['group']    	= $this->group($path.'/'.$entry);
			$struc['size']    	= $this->size($path.'/'.$entry);
			$struc['lastmodunix']= $this->mtime($path.'/'.$entry);
			$struc['lastmod']   = @date('M j',$struc['lastmodunix']);
			$struc['time']    	= @date('h:i:s',$struc['lastmodunix']);
			$struc['type']		= $this->isDir($path.'/'.$entry) ? 'd' : 'f';

			if ( 'd' == $struc['type'] ) {
				if ( $recursive )
					$struc['files'] = $this->dirList($path . '/' . $struc['name'], $includeHidden, $recursive);
				else
					$struc['files'] = array();
			}

			$ret[ $struc['name'] ] = $struc;
		}
		$dir->close();
		unset($dir);
		return $ret;
	}
}




//==========================================================================>


class filesystemFTPExt extends filesystemBase {
	var $link;
	var $errors = null;
	var $options = array();

	function __construct($opt='') {
		$this->method = 'FTPExt';
		//$this->errors = new WP_Error();

		//Check if possible to use ftp functions.
		if ( ! extension_loaded('ftp') ) {
			//$this->errors->add('no_ftp_ext', __('The ftp PHP extension is not available'));
			appUpdateMsg('The FTP PHP extension is not available', true);
			return false;
		}

		// Set defaults:
		//This Class uses the timeout on a per-connection basis, Others use it on a per-action basis.

		if ( ! defined('FS_TIMEOUT') )
			define('FS_TIMEOUT', 240);

		if ( empty($opt['port']) )
			$this->options['port'] = 21;
		else
			$this->options['port'] = $opt['port'];

		if ( empty($opt['hostname']) ){
			//$this->errors->add('empty_hostname', __('FTP hostname is required'));
			appUpdateMsg('FTP hostname is required');
		}
		else
			$this->options['hostname'] = $opt['hostname'];

		if ( ! empty($opt['base']) )
			$this->baseDir = $opt['base'];

		// Check if the options provided are OK.
		if ( empty($opt['username']) ){
			//$this->errors->add('empty_username', __('FTP username is required'));
			appUpdateMsg('FTP username is required');
		}
		else
			$this->options['username'] = $opt['username'];

		if ( empty($opt['password']) ){
			//$this->errors->add('empty_password', __('FTP password is required'));
			appUpdateMsg('FTP password is required');
		}
		else
			$this->options['password'] = $opt['password'];

		$this->options['ssl'] = false;
		if ( 'ftps' == $opt['connectionType'] )
			$this->options['ssl'] = true;

		if($opt['passive']){
			$this->options['passive'] = $opt['passive'];
		}
	}

	function connect() {
		if(!$this->options['hostname'] || !$this->options['username'] || !$this->options['password']){
			appUpdateMsg(sprintf('FTP hostname/username/password is missing"'));
			return false;
		}
		
		if ( isset($this->options['ssl']) && $this->options['ssl'] && function_exists('ftp_ssl_connect') )
			$this->link = @ftp_ssl_connect($this->options['hostname'], $this->options['port'], FS_CONNECT_TIMEOUT);
		else		
			$this->link = ftp_connect($this->options['hostname'], $this->options['port'], FS_CONNECT_TIMEOUT);

		if ( ! $this->link ) {
			//$this->errors->add('connect', sprintf(__('Failed to connect to FTP Server %1$s:%2$s'), $this->options['hostname'], $this->options['port']));
			appUpdateMsg(sprintf('Failed to connect to the FTP server "%1$s:%2$s"', $this->options['hostname'], $this->options['port']));
			return false;
		}

		if ( ! @ftp_login($this->link,$this->options['username'], $this->options['password']) ) {
			//$this->errors->add('auth', sprintf(__('Username/Password incorrect for %s'), $this->options['username']));
			appUpdateMsg(sprintf('FTP username or password incorrect for "%s"', $this->options['username']));
			return false;
		}

		//Set the Connection to use Passive FTP
		if($this->options['passive']){
			@ftp_pasv( $this->link, true );
		}
		if ( @ftp_get_option($this->link, FTP_TIMEOUT_SEC) < FS_TIMEOUT )
			@ftp_set_option($this->link, FTP_TIMEOUT_SEC, FS_TIMEOUT);

		return true;
	}

	function getContents($file, $type = '', $resumePos = 0 ) {
		if ( empty($type) )
			$type = FTP_BINARY;

		$tempfile = getTempName($file);
		$temp = fopen($tempfile, 'w+');

		if ( ! $temp )
			return false;

		if ( ! @ftp_fget($this->link, $temp, $file, $type, $resumePos) )
			return false;

		fseek($temp, 0); //Skip back to the start of the file being written to
		$contents = '';

		while ( ! feof($temp) )
			$contents .= fread($temp, 8192);

		fclose($temp);
		unlink($tempfile);
		return $contents;
	}
	function getContentsArray($file) {
		return explode("\n", $this->getContents($file));
	}

	function putContents($file, $contents, $mode = false ) {
		$tempfile = getTempName($file);
		$temp = fopen($tempfile, 'w+');
		if ( ! $temp )
			return false;

		fwrite($temp, $contents);
		fseek($temp, 0); //Skip back to the start of the file being written to

		$type = isBinary($contents) ? FTP_BINARY : FTP_ASCII;
		$ret = @ftp_fput($this->link, $file, $temp, $type);

		fclose($temp);
		unlink($tempfile);

		$this->chmod($file, $mode);

		return $ret;
	}
	function cwd() {
		$cwd = @ftp_pwd($this->link);
		if ( $cwd )
			$cwd = addTrailingSlash($cwd);
		return $cwd;
	}
	function chdir($dir) {
		return @ftp_chdir($this->link, $dir);
	}
	function chgrp($file, $group, $recursive = false ) {
		return false;
	}
	function chmod($file, $mode = false, $recursive = false) {
		if ( ! $mode ) {
			if ( $this->isFile($file) )
				$mode = FS_CHMOD_FILE;
			elseif ( $this->isDir($file) )
				$mode = FS_CHMOD_DIR;
			else
				return false;
		}

		// chmod any sub-objects if recursive.
		if ( $recursive && $this->isDir($file) ) {
			$fileList = $this->dirList($file);
			foreach ( (array)$fileList as $fileName => $filemeta )
				$this->chmod($file . '/' . $fileName, $mode, $recursive);
		}

		// chmod the file or directory
		if ( ! function_exists('ftp_chmod') )
			return (bool)@ftp_site($this->link, sprintf('CHMOD %o %s', $mode, $file));
		return (bool)@ftp_chmod($this->link, $mode, $file);
	}
	function chown($file, $owner, $recursive = false ) {
		return false;
	}
	function owner($file) {
		$dir = $this->dirList($file);
		return $dir[$file]['owner'];
	}
	function getChmod($file) {
		$dir = $this->dirList($file);
		return $dir[$file]['permsn'];
	}
	function group($file) {
		$dir = $this->dirList($file);
		return $dir[$file]['group'];
	}
	function copy($source, $destination, $overwrite = false, $mode = false) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;
		$content = $this->getContents($source);
		if ( false === $content)
			return false;
		return $this->putContents($destination, $content, $mode);
	}
	function move($source, $destination, $overwrite = false) {
		if ($overwrite) {
			$this->delete($destination, true);
		}
		return ftp_rename($this->link, $source, $destination);
	}

	function delete($file, $recursive = false, $type = false) {
		if ( empty($file) )
			return false;
		if ( 'f' == $type || $this->isFile($file) )
			return @ftp_delete($this->link, $file);
		if ( !$recursive )
			return @ftp_rmdir($this->link, $file);

		$fileList = $this->dirList( addTrailingSlash($file) );
		if ( !empty($fileList) )
			foreach ( $fileList as $deleteFile ){
				iwp_mmb_auto_print('recursive_delete');
				$this->delete( addTrailingSlash($file) . $deleteFile['name'], $recursive, $deleteFile['type'] );
			}
		return @ftp_rmdir($this->link, $file);
	}

	function exists($file) {
		$list = @ftp_nlist($this->link, $file);
		return !empty($list); //empty list = no file, so invert.
	}
	function isFile($file) {
		return $this->exists($file) && !$this->isDir($file);
	}
	function isDir($path) {
		$cwd = $this->cwd();
		$result = @ftp_chdir($this->link, addTrailingSlash($path) );
		if ( $result && $path == $this->cwd() || $this->cwd() != $cwd ) {
			@ftp_chdir($this->link, $cwd);
			return true;
		}
		return false;
	}
	function isReadable($file) {
		//Get dir list, Check if the file is readable by the current user??
		return true;
	}
	function isWritable($file) {
		//Get dir list, Check if the file is writable by the current user??
		return true;
	}
	function atime($file) {
		return false;
	}
	function mtime($file) {
		return ftp_mdtm($this->link, $file);
	}
	function size($file) {
		return ftp_size($this->link, $file);
	}
	function touch($file, $time = 0, $atime = 0) {
		return false;
	}
	function mkDir($path, $chmod = false, $chown = false, $chgrp = false) {
		$path = removeTrailingSlash($path);
		if ( empty($path) )
			return false;

		if ( !@ftp_mkdir($this->link, $path) )
			return false;
		$this->chmod($path, $chmod);
		if ( $chown )
			$this->chown($path, $chown);
		if ( $chgrp )
			$this->chgrp($path, $chgrp);
		return true;
	}
	function rmDir($path, $recursive = false) {
		return $this->delete($path, $recursive);
	}

	function parseListing($line) {
		static $isWindows;
		if ( is_null($isWindows) )
			$isWindows = stripos( ftp_systype($this->link), 'win') !== false;

		if ( $isWindows && preg_match('/([0-9]{2})-([0-9]{2})-([0-9]{2}) +([0-9]{2}):([0-9]{2})(AM|PM) +([0-9]+|<DIR>) +(.+)/', $line, $lucifer) ) {
			$b = array();
			if ( $lucifer[3] < 70 )
				$lucifer[3] +=2000;
			else
				$lucifer[3] += 1900; // 4digit year fix
			$b['isdir'] = ( $lucifer[7] == '<DIR>');
			if ( $b['isdir'] )
				$b['type'] = 'd';
			else
				$b['type'] = 'f';
			$b['size'] = $lucifer[7];
			$b['month'] = $lucifer[1];
			$b['day'] = $lucifer[2];
			$b['year'] = $lucifer[3];
			$b['hour'] = $lucifer[4];
			$b['minute'] = $lucifer[5];
			$b['time'] = @mktime($lucifer[4] + (strcasecmp($lucifer[6], "PM") == 0 ? 12 : 0), $lucifer[5], 0, $lucifer[1], $lucifer[2], $lucifer[3]);
			$b['am/pm'] = $lucifer[6];
			$b['name'] = $lucifer[8];
		} elseif ( !$isWindows && $lucifer = preg_split('/[ ]/', $line, 9, PREG_SPLIT_NO_EMPTY)) {
			//echo $line."\n";
			$lcount = count($lucifer);
			if ( $lcount < 8 )
				return '';
			$b = array();
			$b['isdir'] = $lucifer[0][0] === 'd';
			$b['islink'] = $lucifer[0][0] === 'l';
			if ( $b['isdir'] )
				$b['type'] = 'd';
			elseif ( $b['islink'] )
				$b['type'] = 'l';
			else
				$b['type'] = 'f';
			$b['perms'] = $lucifer[0];
			$b['number'] = $lucifer[1];
			$b['owner'] = $lucifer[2];
			$b['group'] = $lucifer[3];
			$b['size'] = $lucifer[4];
			if ( $lcount == 8 ) {
				sscanf($lucifer[5], '%d-%d-%d', $b['year'], $b['month'], $b['day']);
				sscanf($lucifer[6], '%d:%d', $b['hour'], $b['minute']);
				$b['time'] = @mktime($b['hour'], $b['minute'], 0, $b['month'], $b['day'], $b['year']);
				$b['name'] = $lucifer[7];
			} else {
				$b['month'] = $lucifer[5];
				$b['day'] = $lucifer[6];
				if ( preg_match('/([0-9]{2}):([0-9]{2})/', $lucifer[7], $l2) ) {
					$b['year'] = @date("Y");
					$b['hour'] = $l2[1];
					$b['minute'] = $l2[2];
				} else {
					$b['year'] = $lucifer[7];
					$b['hour'] = 0;
					$b['minute'] = 0;
				}
				$b['time'] = strtotime( sprintf('%d %s %d %02d:%02d', $b['day'], $b['month'], $b['year'], $b['hour'], $b['minute']) );
				$b['name'] = $lucifer[8];
			}
		}

		return $b;
	}

	function dirList($path = '.', $includeHidden = true, $recursive = false) {
		if ( $this->isFile($path) ) {
			$limitFile = basename($path);
			$path = dirname($path) . '/';
		} else {
			$limitFile = false;
		}

		$pwd = @ftp_pwd($this->link);
		if ( ! @ftp_chdir($this->link, $path) ) // Cant change to folder = folder doesn't exist
			return false;
		$list = @ftp_rawlist($this->link, '-a', false);
		@ftp_chdir($this->link, $pwd);

		if ( empty($list) ) // Empty array = non-existent folder (real folder will show . at least)
			return false;

		$dirList = array();
		foreach ( $list as $k => $v ) {
			$entry = $this->parseListing($v);
			if ( empty($entry) )
				continue;

			if ( '.' == $entry['name'] || '..' == $entry['name'] )
				continue;

			if ( ! $includeHidden && '.' == $entry['name'][0] )
				continue;

			if ( $limitFile && $entry['name'] != $limitFile)
				continue;

			$dirList[ $entry['name'] ] = $entry;
		}

		$ret = array();
		foreach ( (array)$dirList as $struc ) {
			if ( 'd' == $struc['type'] ) {
				if ( $recursive )
					$struc['files'] = $this->dirList($path . '/' . $struc['name'], $includeHidden, $recursive);
				else
					$struc['files'] = array();
			}

			$ret[ $struc['name'] ] = $struc;
		}
		return $ret;
	}
	function close() {
		if ( $this->link )
			ftp_close($this->link);
	}

	function __destruct() {
		if ( $this->link )
			ftp_close($this->link);
	}
}

class filesystemSSH2Ext extends filesystemBase {

	var $link = false;
	var $sftp_link = false;
	var $keys = false;
	var $options = array();

	function __construct($opt='') {
		$this->method = 'ssh2';
		//$this->errors = new WP_Error();

		//Check if possible to use ssh2 functions.
		if ( ! extension_loaded('ssh2') ) {
			//$this->errors->add('no_ssh2_ext', __('The ssh2 PHP extension is not available'));
			appUpdateMsg('The ssh2 PHP extension is not available', true);
			return false;
		}
		if ( !function_exists('stream_get_contents') ) {
			//$this->errors->add('ssh2_php_requirement', __('The ssh2 PHP extension is available, however, we require the PHP5 function <code>stream_get_contents()</code>'));
			appUpdateMsg('The ssh2 PHP extension is available, however, we require the PHP5 function <code>stream_get_contents()</code>', true);
			return false;
		}

		// Set defaults:
		if ( empty($opt['port']) )
			$this->options['port'] = 22;
		else
			$this->options['port'] = $opt['port'];

		if ( empty($opt['hostname']) )
			//$this->errors->add('empty_hostname', __('SSH2 hostname is required'));
			appUpdateMsg('SSH2 hostname is required', true);
		else
			$this->options['hostname'] = $opt['hostname'];

		if ( ! empty($opt['base']) )
			$this->wp_base = $opt['base'];

		// Check if the options provided are OK.
		if ( !empty ($opt['public_key']) && !empty ($opt['private_key']) ) {
			$this->options['public_key'] = $opt['public_key'];
			$this->options['private_key'] = $opt['private_key'];

			$this->options['hostkey'] = array('hostkey' => 'ssh-rsa');

			$this->keys = true;
		} elseif ( empty ($opt['username']) ) {
			//$this->errors->add('empty_username', __('SSH2 username is required'));
			appUpdateMsg('SSH2 username is required', true);
		}

		if ( !empty($opt['username']) )
			$this->options['username'] = $opt['username'];

		if ( empty ($opt['password']) ) {
			if ( !$this->keys )	//password can be blank if we are using keys
				//$this->errors->add('empty_password', __('SSH2 password is required'));
				appUpdateMsg('SSH2 password is required', true);
		} else {
			$this->options['password'] = $opt['password'];
		}

	}

	function connect() {
		if ( ! $this->keys ) {
			$this->link = @ssh2_connect($this->options['hostname'], $this->options['port']);
		} else {
			$this->link = @ssh2_connect($this->options['hostname'], $this->options['port'], $this->options['hostkey']);
		}

		if ( ! $this->link ) {
			//$this->errors->add('connect', sprintf(__('Failed to connect to SSH2 Server %1$s:%2$s'), $this->options['hostname'], $this->options['port']));
			appUpdateMsg(sprintf('Failed to connect to SSH2 Server %1$s:%2$s', $this->options['hostname'], $this->options['port']), true);
			return false;
		}

		if ( !$this->keys ) {
			if ( ! @ssh2_auth_password($this->link, $this->options['username'], $this->options['password']) ) {
				//$this->errors->add('auth', sprintf(__('Username/Password incorrect for %s'), $this->options['username']));
				appUpdateMsg(sprintf('Username/Password incorrect for %s', $this->options['username']), true);
				return false;
			}
		} else {
			if ( ! @ssh2_auth_pubkey_file($this->link, $this->options['username'], $this->options['public_key'], $this->options['private_key'], $this->options['password'] ) ) {
				//$this->errors->add('auth', sprintf(__('Public and Private keys incorrect for %s'), $this->options['username']));
				appUpdateMsg(sprintf('Public and Private keys incorrect for %s', $this->options['username']), true);
				return false;
			}
		}

		$this->sftp_link = ssh2_sftp($this->link);

		return true;
	}

	function runCommand( $command, $returnbool = false) {

		if ( ! $this->link )
			return false;

		if ( ! ($stream = ssh2_exec($this->link, $command)) ) {
			//$this->errors->add('command', sprintf(__('Unable to perform command: %s'), $command));
			appUpdateMsg(sprintf('Unable to perform command: %s', $command), true);
		} else {
			stream_set_blocking( $stream, true );
			stream_set_timeout( $stream, FS_TIMEOUT );
			$data = stream_get_contents( $stream );
			fclose( $stream );

			if ( $returnbool )
				return ( $data === false ) ? false : '' != trim($data);
			else
				return $data;
		}
		return false;
	}

	function getContents( $file ) {
		$file = ltrim($file, '/');
		return file_get_contents('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function getContentsArray($file) {
		$file = ltrim($file, '/');
		return file('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function putContents($file, $contents, $mode = false ) {
		$ret = file_put_contents( 'ssh2.sftp://' . $this->sftp_link . '/' . ltrim( $file, '/' ), $contents );

		if ( $ret !== strlen( $contents ) )
			return false;

		$this->chmod($file, $mode);

		return true;
	}

	function cwd() {
		$cwd = $this->runCommand('pwd');
		if ( $cwd )
			$cwd = addTrailingSlash($cwd);
		return $cwd;
	}

	function chdir($dir) {
		return $this->runCommand('cd ' . $dir, true);
	}

	function chgrp($file, $group, $recursive = false ) {
		if ( ! $this->exists($file) )
			return false;
		if ( ! $recursive || ! $this->is_dir($file) )
			return $this->runCommand(sprintf('chgrp %s %s', escapeshellarg($group), escapeshellarg($file)), true);
		return $this->runCommand(sprintf('chgrp -R %s %s', escapeshellarg($group), escapeshellarg($file)), true);
	}

	function chmod($file, $mode = false, $recursive = false) {
		if ( ! $this->exists($file) )
			return false;

		if ( ! $mode ) {
			if ( $this->is_file($file) )
				$mode = FS_CHMOD_FILE;
			elseif ( $this->is_dir($file) )
				$mode = FS_CHMOD_DIR;
			else
				return false;
		}

		if ( ! $recursive || ! $this->is_dir($file) )
			return $this->runCommand(sprintf('chmod %o %s', $mode, escapeshellarg($file)), true);
		return $this->runCommand(sprintf('chmod -R %o %s', $mode, escapeshellarg($file)), true);
	}

	/**
	 * Change the ownership of a file / folder.
	 *
	 * @since Unknown
	 *
	 * @param string $file    Path to the file.
	 * @param mixed  $owner   A user name or number.
	 * @param bool $recursive Optional. If set True changes file owner recursivly. Defaults to False.
	 * @return bool Returns true on success or false on failure.
	 */
	function chown( $file, $owner, $recursive = false ) {
		if ( ! $this->exists($file) )
			return false;
		if ( ! $recursive || ! $this->is_dir($file) )
			return $this->runCommand(sprintf('chown %s %s', escapeshellarg($owner), escapeshellarg($file)), true);
		return $this->runCommand(sprintf('chown -R %s %s', escapeshellarg($owner), escapeshellarg($file)), true);
	}

	function owner($file) {
		$owneruid = @fileowner('ssh2.sftp://' . $this->sftp_link . '/' . ltrim($file, '/'));
		if ( ! $owneruid )
			return false;
		if ( ! function_exists('posix_getpwuid') )
			return $owneruid;
		$ownerarray = posix_getpwuid($owneruid);
		return $ownerarray['name'];
	}

	function getchmod($file) {
		return substr(decoct(@fileperms( 'ssh2.sftp://' . $this->sftp_link . '/' . ltrim($file, '/') )),3);
	}

	function group($file) {
		$gid = @filegroup('ssh2.sftp://' . $this->sftp_link . '/' . ltrim($file, '/'));
		if ( ! $gid )
			return false;
		if ( ! function_exists('posix_getgrgid') )
			return $gid;
		$grouparray = posix_getgrgid($gid);
		return $grouparray['name'];
	}

	function copy($source, $destination, $overwrite = false, $mode = false) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;
		$content = $this->getContents($source);
		if ( false === $content)
			return false;
		return $this->putContents($destination, $content, $mode);
	}

	function move($source, $destination, $overwrite = false) {
		return @ssh2_sftp_rename($this->link, $source, $destination);
	}

	function delete($file, $recursive = false, $type = false) {
		if ( 'f' == $type || $this->isFile($file) )
			return ssh2_sftp_unlink($this->sftp_link, $file);
		if ( ! $recursive )
			 return ssh2_sftp_rmdir($this->sftp_link, $file);
		$filelist = $this->dirlist($file);
		if ( is_array($filelist) ) {
			foreach ( $filelist as $filename => $fileinfo) {
				iwp_mmb_auto_print('recursive_delete');
				$this->delete($file . '/' . $filename, $recursive, $fileinfo['type']);
			}
		}
		return ssh2_sftp_rmdir($this->sftp_link, $file);
	}

	function exists($file) {
		$file = ltrim($file, '/');
		return file_exists('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function isFile($file) {
		$file = ltrim($file, '/');
		return is_file('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function isDir($path) {
		$path = ltrim($path, '/');
		return is_dir('ssh2.sftp://' . $this->sftp_link . '/' . $path);
	}

	function isReadable($file) {
		$file = ltrim($file, '/');
		return is_readable('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function isWritable($file) {
		$file = ltrim($file, '/');
		return is_writable('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function atime($file) {
		$file = ltrim($file, '/');
		return fileatime('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function mtime($file) {
		$file = ltrim($file, '/');
		return filemtime('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function size($file) {
		$file = ltrim($file, '/');
		return filesize('ssh2.sftp://' . $this->sftp_link . '/' . $file);
	}

	function touch($file, $time = 0, $atime = 0) {
		//Not implemented.
	}

	function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		$path = removeTrailingSlash($path);
		if ( empty($path) )
			return false;

		if ( ! $chmod )
			$chmod = FS_CHMOD_DIR;
		if ( ! ssh2_sftp_mkdir($this->sftp_link, $path, $chmod, true) )
			return false;
		if ( $chown )
			$this->chown($path, $chown);
		if ( $chgrp )
			$this->chgrp($path, $chgrp);
		return true;
	}

	function rmdir($path, $recursive = false) {
		return $this->delete($path, $recursive);
	}

	function dirlist($path, $include_hidden = true, $recursive = false) {
		if ( $this->isFile($path) ) {
			$limit_file = basename($path);
			$path = dirname($path);
		} else {
			$limit_file = false;
		}

		if ( ! $this->isDir($path) )
			return false;

		$ret = array();
		$dir = @dir('ssh2.sftp://' . $this->sftp_link .'/' . ltrim($path, '/') );

		if ( ! $dir )
			return false;

		while (false !== ($entry = $dir->read()) ) {
			$struc = array();
			$struc['name'] = $entry;

			if ( '.' == $struc['name'] || '..' == $struc['name'] )
				continue; //Do not care about these folders.

			if ( ! $include_hidden && '.' == $struc['name'][0] )
				continue;

			if ( $limit_file && $struc['name'] != $limit_file )
				continue;

			$struc['perms'] 	= $this->gethchmod($path.'/'.$entry);
			$struc['permsn']	= $this->getnumchmodfromh($struc['perms']);
			$struc['number'] 	= false;
			$struc['owner']    	= $this->owner($path.'/'.$entry);
			$struc['group']    	= $this->group($path.'/'.$entry);
			$struc['size']    	= $this->size($path.'/'.$entry);
			$struc['lastmodunix']= $this->mtime($path.'/'.$entry);
			$struc['lastmod']   = date('M j',$struc['lastmodunix']);
			$struc['time']    	= date('h:i:s',$struc['lastmodunix']);
			$struc['type']		= $this->isDir($path.'/'.$entry) ? 'd' : 'f';

			if ( 'd' == $struc['type'] ) {
				if ( $recursive )
					$struc['files'] = $this->dirlist($path . '/' . $struc['name'], $include_hidden, $recursive);
				else
					$struc['files'] = array();
			}

			$ret[ $struc['name'] ] = $struc;
		}
		$dir->close();
		unset($dir);
		return $ret;
	}
}

class filesystemSFTPExt extends filesystemBase {

	var $link = false;
	var $sftp_link = false;
	var $keys = false;
	var $options = array();

	function __construct($opt='') {
		$this->method = 'sftp';
		 include dirname(__FILE__).'/phpseclib/vendor/autoload.php';
		//$this->errors = new WP_Error();

		
		// Set defaults:
		if ( empty($opt['port']) )
			$this->options['port'] = 22;
		else
			$this->options['port'] = $opt['port'];

		if ( empty($opt['hostname']) )
			//$this->errors->add('empty_hostname', __('SSH2 hostname is required'));
			appUpdateMsg('SSH2 hostname is required', true);
		else
			$this->options['hostname'] = $opt['hostname'];

		if ( ! empty($opt['base']) )
			$this->wp_base = $opt['base'];

		if ( empty ($opt['username']) ) {
			//$this->errors->add('empty_username', __('SSH2 username is required'));
			appUpdateMsg('SFTP username is required', true);
		}

		if ( !empty($opt['username']) )
			$this->options['username'] = $opt['username'];

		if ( empty ($opt['password']) && empty ($opt['hostKey'])) {
			if ( !$this->keys )	//password can be blank if we are using keys
				//$this->errors->add('empty_password', __('SSH2 password is required'));
				appUpdateMsg('SSH2 password is required', true);
		} else {
			if (!empty($opt['password'])) {
				$this->options['password'] = $opt['password'];
			}
			if (!empty($opt['hostKey'])) {
				$this->options['hostKey'] = $opt['hostKey'];
			}
		}

	}

	function connect() {
		$this->link = new \phpseclib\Net\SFTP($this->options['hostname'], $this->options['port']);
		
		 if (!empty($this->options['hostKey'])) {
			
			$rsa = new  \phpseclib\Crypt\RSA();
			if (false === $rsa->loadKey($this->options['hostKey'])) {
				appUpdateMsg('The key provided was not in a valid format, or was corrupt', true);
			}
			$this->options['password'] = $rsa;
	    }

		if ( ! $this->link ) {
			//$this->errors->add('connect', sprintf(__('Failed to connect to SSH2 Server %1$s:%2$s'), $this->options['hostname'], $this->options['port']));
			appUpdateMsg(sprintf('Failed to connect to SSH2 Server %1$s:%2$s', $this->options['hostname'], $this->options['port']), true);
			return false;
		}

		if ( ! $this->link->login($this->options['username'], $this->options['password']) ) {
			appUpdateMsg(sprintf('Username/Password incorrect for %s', $this->options['username']), true);
			return false;
		}

		return true;
	}

	function runCommand( $command, $returnbool = false) {

		$validSFTPCommands = array(
		/*
		sftp CLI commands:
		 'cd',
		 'chgrp',
		 'chmod',
		 'chown',
		 'df',
		 'get',
		 'ln',
		 'ls',
		 'mkdir',
		 'put',
		 'pwd',
		 'rename',
		 'rm',
		 'rmdir',
		 'symlink'
		*/
		// Available Net_SFTP commands:
		 'pwd',
		 'chmod', // ignored though
		 'chgrp', // ignored though
		 'chown'  // ignored though
		);
		if ( ! $this->link )
			return false;
				$cmdline = preg_split('/[[:blank:]]+/', $command);
		if ( ! in_array(($cmd=$cmdline[0]), $validSFTPCommands) )
			return false;
				if (substr($cmd, 0, 2) == 'ch') return true;
		$data = $this->link->$cmd();
		if ( $returnbool )
			return ( $data === false ) ? false : '' != trim($data);
		else
			return $data;
	}

		// strip FTP_BASE part of path; reduce to relative path
	function fixPath($file) {
		if (defined('FTP_BASE')) {
				if (substr($file, 0, ($l=strlen(FTP_BASE))) == FTP_BASE)
					$file = ltrim(substr($file, $l), '/');
		}
		return $file;
	}
		
	function getContents( $file ) {
		return $this->link->get($this->fixPath($file));
	}

	function getContentsArray($file) {
		return preg_split("/\n+/", $this->getContents($file));
	}

	function putContents($file, $contents, $mode = 2, $start = -1, $local_start = -1, $progressCallback = null ) {
		$file = $this->fixPath($file);
		$ret = $this->link->put($file, $contents, $mode, $start, $local_start, $progressCallback);
		return false !== $ret;

	}

	function cwd() {
		$cwd = $this->runCommand('pwd');
		if ( $cwd )
			$cwd = addTrailingSlash($cwd);
		return $cwd;
	}

	function chdir($dir) {
		return $this->link->chdir($this->fixPath($dir));
	}

	function chgrp($file, $group, $recursive = false ) {
		return true; // not supported
	}

	function chmod($file, $mode = false, $recursive = false) {
		return $this->link->chmod($mode, $file, $recursive); // SFTP does support chmod, better though to configure the right (default) permissions on the server side
	}

	/**
	 * Change the ownership of a file / folder.
	 *
	 * @since Unknown
	 *
	 * @param string $file    Path to the file.
	 * @param mixed  $owner   A user name or number.
	 * @param bool $recursive Optional. If set True changes file owner recursivly. Defaults to False.
	 * @return bool Returns true on success or false on failure.
	 */
	function chown( $file, $owner, $recursive = false ) {
		return true; // not supported
	}
		
		function stat($file) {
			$file = $this->fixPath($file);
			$stat = $this->link->stat($file);
			if ($stat !== false) {
				if (!isset($stat['permissions'])) {
					return false;
				}
				$stat['size'] = $this->link->size($file);
			}
			else {
			}
			return $stat;
		}

	function owner($file) {
		$stat = $this->stat($file);
		if ( ! $stat )
			return false;
		if ( ! isset($stat['uid']) )
			return false;
		$owneruid = $stat['uid'];
		if ( ! function_exists('posix_getpwuid') )
			return $owneruid;
		$ownerarray = posix_getpwuid($owneruid);
		return $ownerarray['name'];
	}

	function getchmod($file) {
		$stat = $this->stat($file);
		return substr(($stat['permissions'] & 000777), -3);
	}

	function group($file) {
		$stat = $this->stat($file);
		if ( ! $stat )
			return false;
		if ( ! isset($stat['gid']) )
			return false;
		$ownergid = $stat['gid'];
		if ( ! function_exists('posix_getgrgid') )
			return $gid;
		$grouparray = posix_getgrgid($ownergid);
		return $grouparray['name'];
	}

	function copy($source, $destination, $overwrite = false, $mode = false) {
		if ( ! $overwrite && $this->exists($destination) )
			return false;
		$content = $this->getContents($source);
		if ( false === $content)
			return false;
		return $this->putContents($destination, $content, $mode);
	}


	function move($source, $destination, $overwrite = false) {
		if ($overwrite) {
			$this->delete($destination, true);
		}
		return $this->link->rename($this->fixPath($source), $this->fixPath($destination));
	}


	function delete($file, $recursive = false, $type = false) {
		$file = $this->fixPath($file);
		if ( 'f' === $type || $this->isFile($file) ) {
			return $this->link->delete($file);
		}
		if ( ! $recursive ) {
			 return $this->link->rmdir($file);
		}
		//At this point its a folder, and we're in recursive mode
		$file = addTrailingSlash($file);
		$filelist = $this->dirlist($file, true);
		$retval = true;
		if ( is_array($filelist) ) //false if no files, So check first.
			foreach ($filelist as $filename => $fileinfo){
				iwp_mmb_auto_print('recursive_delete');
				if ( ! $this->delete($file . $filename, $recursive, $fileinfo['type']) )
					$retval = false;
			}

		if ( $this->exists($file) && ! $this->link->rmdir($file) )
			$retval = false;
		return $retval;
	}


	function exists($file) {
		return $this->stat($file) !== false;
	}
		
		function S_ISDIR($stat) {
		return( ($stat['permissions'] & 040000) == 040000 );
	}
	

	function S_ISREG($stat) {
		return( ($stat['permissions'] & 0100000) == 0100000 );
	}

	function isFile($file) {
		return $this->S_ISREG($this->stat($file));
	}


	function isDir($path) {
		return $this->S_ISDIR($this->stat($path));
	}


	function isReadable($file) {
		$stat = $this->stat($file);
		$perms = $stat['permissions'];
		return ($perms & 0x000400);
	}


	function isWritable($file) {
		$stat = $this->stat($file);
		$perms = $stat['permissions'];
		return ($perms & 0x000200);
	}


	function atime($file) {
		$stat = $this->stat($file);
		return $stat['atime'];
	}


	function mtime($file) {
		$stat = $this->stat($file);
		return $stat['mtime'];
	}

	function size($file) {
		$stat = $this->stat($file);
		return $stat['size'];
	}


	function touch($file, $time = 0, $atime = 0) {
		return $this->link->touch($file, $time, $atime);
	}


	function mkdir($path, $chmod = false, $chown = false, $chgrp = false) {
		return $this->link->mkdir($path);
	}


	function rmdir($path, $recursive = false) {
		return $this->delete($path, $recursive);
	}

	function dirlist($path, $include_hidden = true, $recursive = false) {
		if ( $this->isFile($path) ) {
			$limit_file = basename($path);
			$path = dirname($path);
		} else {
			$limit_file = false;
		}

		if ( ! $this->isDir($path) )
			return false;

		$ret = array();
		$curdir = $this->fixPath($path);
		$dir = $this->link->nlist($curdir);

		if ( ! $dir )
			return false;

		foreach ($dir as $entry) {
			$struc = $this->stat($curdir.'/'.$entry);
			$struc['name'] = $entry;


			if ( '.' == $struc['name'] || '..' == $struc['name'] )
				continue; //Do not care about these folders.

			if ( ! $include_hidden && '.' == $struc['name'][0] )
				continue;

			if ( $limit_file && $struc['name'] != $limit_file )
				continue;

			$struc['perms'] 	= $this->gethchmod($path.'/'.$entry);
			$struc['permsn']	= $struc['permissions'] & 000777;
			$struc['number'] 	= false;
			$struc['owner']    	= $this->owner($path.'/'.$entry);
			$struc['group']    	= $this->group($path.'/'.$entry);
			$struc['size']    	= $this->size($path.'/'.$entry);
			$struc['lastmodunix']= $this->mtime($path.'/'.$entry);
			$struc['lastmod']   = date('M j',$struc['lastmodunix']);
			$struc['time']    	= date('h:i:s',$struc['lastmodunix']);
			$struc['type']		= $this->isDir($path.'/'.$entry) ? 'd' : 'f';

			if ( 'd' == $struc['type'] ) {
				if ( $recursive )
					$struc['files'] = $this->dirlist($path . '/' . $struc['name'], $include_hidden, $recursive);
				else
					$struc['files'] = array();
			}

			$ret[ $struc['name'] ] = $struc;
		}
		return $ret;
	}
	

	function lastError() {
	  return $this->link->getLastSFTPError();
	}


	function getErrors() {
	  return $this->link->getSFTPErrors();
	}
}

class IWP_Seek_Iterator{

	public $iterator_common;
	public $external_obj;
	public $iterator_loop_limit;
	public $path;
	public $type;
	public $query;
	public $processed_files;
	public $app_functions;
	public $is_recursive;
	public $exclude_class_obj;

	public function __construct($type = false, $iterator_loop_limit = 1000){
	    $this->type = $type;
	    $this->iterator_loop_limit = $iterator_loop_limit;
	}

	public function get_seekable_files_obj($path){

	    $temp_path = $path;

	    // IWP_add_abspath($path);

	    $path = is_valid_path($path);

	    if( is_array($path) ) {
	        return $path;
	    }

	    $this->path = $temp_path;

	    return new DirectoryIterator($path);
	}

	public function process_iterator($path, $offset = false, $is_recursive = false){

	    $iterator = $this->get_seekable_files_obj($path);

	    if (empty($iterator)) {
	        return ;
	    }

	    $this->seek = empty($offset) ? array() : explode('-', $offset);

	    $this->counter = 0;
	    $this->is_recursive = $is_recursive;

	    if ($is_recursive) {
	        $this->recursive_iterator($iterator, false);
	    } else {
	        $this->iterator($iterator);
	    }
	}

	public function process_file($iterator, $key){
		process_file($iterator, $this->is_recursive, $this->path, $key, $this->counter, $this->iterator_loop_limit);
	}

	private function extra_check_query(){
	    if (!empty($this->query)) {
	        insert_into_current_process($this->query);
	        $this->query = '';
	    }
	}

	public function iterator($iterator){
	    //Moving satelite into position.
	    $this->seek_offset($iterator);

	    while ($iterator->valid()) {

	        $this->counter++;

	        $recursive_path = $iterator->getPathname();

	        //Dont recursive iterator if its a dir or dot
	        if ($iterator->isDot() || !$iterator->isReadable()  || $iterator->isDir()) {

	            //move to next file
	            $iterator->next();

	            continue;
	        }

	        $key = $iterator->key();

	        $this->process_file( $iterator, $key );

	        //move to next file
	        $iterator->next();
	    }

	    $this->extra_check_query();
	}


	public function recursive_iterator($iterator, $key_recursive) {

	    $this->seek_offset($iterator);

	    while ($iterator->valid()) {

	        //Forming current path from iterator
	        $recursive_path = $iterator->getPathname();

	        //Mapping keys
	        $key = ($key_recursive !== false ) ? $key_recursive . '-' . $iterator->key() : $iterator->key() ;

	        //Do recursive iterator if its a dir
	        if (!$iterator->isDot() && $iterator->isReadable() && $iterator->isDir() ) {

	            if (1) {//exclude
	                //create new object for new dir
	                $sub_iterator = new DirectoryIterator($recursive_path);

	                $this->recursive_iterator($sub_iterator, $key);

	            } else{
	            }

	        }

	        //Ignore dots paths
	        if(!$iterator->isDot()){
	            $this->process_file( $iterator, $key );
	        }

	        //move to next file
	        $iterator->next();
	    }

	    $this->extra_check_query();
	}

	private function seek_offset(&$iterator){

	    if(!count($this->seek)){
	        return false;
	    }

	    //Moving satelite into position.
	    $iterator->seek($this->seek[0]);

	    //remove positions from the array after moved satelite
	    unset($this->seek[0]);

	    //reset array index
	    $this->seek = array_values($this->seek);

	}
}

function process_copy_files_req($status){
    if ($status === 'db_clone_over') {
        $this->logger->log("Copying Files is started.", 'staging_progress', $this->staging_id);
    }

    $this->same_server_update_keys('same_server_staging_status', 'copy_files_progress');
    $iter_count = $this->same_server_copy();

    $this->same_server_update_keys('same_server_staging_status', 'copy_files_over');

    $this->logger->log("All files are copied to staging location succesfully.", 'staging_progress', $this->staging_id);

    IWP_manual_debug('', 'end_copy_files_staging');
}

function same_server_copy(){
    scan_entire_site();
    copy_selected_folders();
}

function scan_entire_site(){
    $dir = get_root_dir_folders();
    save_dir_list($dir);
    // $dir = get_wp_content_dir_folders();
    // save_dir_list($dir);
    // $dir = get_uploads_dir_folders();
    // save_dir_list($dir);
    // get_db_backup_file();
    // save_dir_list();
    save_deep_dir_list();
}

function get_root_dir_folders(){
    $files_obj = get_files_obj_by_path(IWP_ABSPATH);
    return add_dir_list($files_obj);
}


function get_wp_content_dir_folders(){
    $files_obj = get_files_obj_by_path(IWP_WP_CONTENT_DIR);
    return add_dir_list($files_obj);
}

function get_uploads_dir_folders(){
    $files_obj = get_files_obj_by_path(IWP_UPLOADS_DIR);
    return add_dir_list($files_obj);
}

function add_dir_list($files_obj){
    foreach ($files_obj as $key => $file_obj) {

        $file = $file_obj->getPathname();
        $file = iwp_wp_normalize_path($file);

        if (!IWP_is_dir($file)) {
            /// $this->files[] = $file;
        } else {
            // IWP_remove_abspath($file);
            $dir[] = $file;
        }
    }
    return $dir;
}

function save_dir_list($dir){
    $qry = '';
    $deep_dirs = array(
        IWP_ABSPATH,
        IWP_STAGING_DIR,
    );
    foreach ($dir as $dir) {
        if (in_array($dir, $deep_dirs)) {
            continue;
        }


        $qry .= empty($qry) ? "('" : ",('" ;
        $qry .= iwp_wp_normalize_path($dir) . "', '0')";

    }
    insert_into_iterator_process($qry);
}

function save_deep_dir_list(){
    $deep_dirs = array(
        IWP_ABSPATH,
    );
    $qry = '';
    foreach ($deep_dirs as $dir) {
        $qry .= empty($qry) ? "('" : ",('" ;
        $qry .= iwp_wp_normalize_path($dir) . "', '0')";

    }

    insert_into_iterator_process($qry);
}

function insert_into_iterator_process($qry){
    $sql = "insert into IWP_processed_iterator ( name, offset  ) values $qry";
    $result = DB::doQuery($sql);
}

function get_files_obj_by_path($path, $recursive = false){

    // IWP_add_abspath($path);

    $path = is_valid_path($path);

    if( is_array($path) ) {
        return $path;
    }

    if($recursive){
        return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path , RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD);
    }

    return new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path , RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CATCH_GET_CHILD);
}

function IWP_add_abspath(&$file, $change_reference = true){

    $file = iwp_wp_normalize_path($file);

    $temp_file = IWP_add_trailing_slash($file);

    if (strpos($temp_file, IWP_ABSPATH) !== false) {
        return $file;
    }

    if ($change_reference) {
        $file = IWP_ABSPATH . ltrim($file, '/');
        return $file;
    }

    return IWP_ABSPATH . ltrim($file, '/');
}

function IWP_remove_abspath(&$file, $change_reference = true){

	$file = iwp_wp_normalize_path($file);

	if (strpos($file, IWP_ABSPATH) === false) {
		return IWP_RELATIVE_ABSPATH . ltrim($file, '/');
	}

	if ($change_reference) {
		$file = str_replace(IWP_ABSPATH, IWP_RELATIVE_ABSPATH, $file);
		return $file;
	}

	return str_replace(IWP_ABSPATH, IWP_RELATIVE_ABSPATH, $file);
}

if (!function_exists('iwp_wp_normalize_path')) {
    function iwp_wp_normalize_path( $path ) {
        $path = str_replace( '\\', '/', $path );
        $path = preg_replace( '|(?<=.)/+|', '/', $path );
        if ( ':' === substr( $path, 1, 1 ) ) {
            $path = ucfirst( $path );
        }
        return $path;
    }
}

function IWP_add_trailing_slash($string) {
    return IWP_remove_trailing_slash($string) . '/';
}

function IWP_remove_trailing_slash($string) {
    return rtrim($string, '/');
}

function is_valid_path($path){
    $default = array();

    if (empty($path)) {
        return $default;
    }

    $path = rtrim($path, '/');

    $path = iwp_wp_normalize_path($path);

    if (empty($path)) {
        return $default;
    }

    $basename = basename($path);

    if ($basename == '..' || $basename == '.') {
        return $default;
    }

    if (!is_readable($path)) {
        return $default;
    }

    return $path;
}

function IWP_is_dir($good_path){
    $good_path = iwp_wp_normalize_path($good_path);

    if (is_dir($good_path)) {
        return true;
    }

    $ext = pathinfo($good_path, PATHINFO_EXTENSION);

    if (!empty($ext)) {
        return false;
    }

    if (is_file($good_path)) {
        return false;
    }

    return true;
}

function copy_selected_folders(){
    initFileSystem();
    $break = false;
    $deep_dirs_array = array(
        IWP_ABSPATH,
    );

    while(!$break){
        $dir_meta = get_unfnished_folder();
        $deep_dirs = false;

        if (empty($dir_meta) || $dir_meta['offset'] === -1) {
            $break = true;
            continue;
        }

        if( array_search($dir_meta['name'], $deep_dirs_array) !== false ){
            $deep_dirs = true;
        }

        $file = $dir_meta['name'];

        if ($deep_dirs === false && skip_file($file) === true) {
            update_iterator($dir_meta['name'], -1);
            continue;
        }

        if(IWP_is_dir($file)){
            iwp_copy_dir($dir_meta['name'], $dir_meta['offset'], $deep_dirs);
        } else {
            iwp_copy_file($dir_meta['name'], $update_status = true);
        }
    }
}

function get_unfnished_folder() {
    $sql = "SELECT * FROM IWP_processed_iterator WHERE offset != -1 LIMIT 1";
    $response = DB::getArray($sql);
    // IWP_log($response, '--------$response--------');

    return empty($response) ? false : $response[0];
}

function skip_file($file){
    if(!is_readable($file)){
        return true;
    }

    // if (stripos($file . '/' , $same_staging_folder) !== false ) {
    //     return true;
    // }

    // if ($this->exclude_class_obj->is_excluded_file($file)) {
    //     return true;
    // }

    return false;
}

function update_iterator($table, $offset) {
        upsert(array(
            'name' => $table,
            'offset' => $offset,
        ));
}

function upsert($data) {
	$exists = DB::getField("IWP_processed_iterator", 'id',"name = '".$data['name']."'");
	if ($exists) {
    	DB::update("IWP_processed_iterator", $data,"name = '".$data['name']."'");
	}else{
    	DB::insert("IWP_processed_iterator", $data);
	}
}

function iwp_copy_file($live_file, $update_status = false){
    // initFileSystem();
    // IWP_add_abspath($live_file);

    $live_file = iwp_wp_normalize_path($live_file);


    $staging_file = same_server_replace_pathname($live_file);

    mkdir_by_path(dirname($staging_file));

    $size = filesize($live_file);
    if (!file_exists($staging_file) && $size > 1024 * 1024 * 2) {
        $copy_status = IWP_copy_large_file($live_file, $staging_file);
    } elseif(!file_exists($staging_file)) {
        $copy_status = $GLOBALS['FileSystemObj']->touch($staging_file);
        $copy_status = $GLOBALS['FileSystemObj']->copy($live_file, $staging_file, true, FS_CHMOD_FILE);
    }elseif(file_exists($staging_file) && defined('IWP_STAGING_DIR') && $staging_file == IWP_STAGING_DIR.'/.htaccess' && IWP_LiteSpeed::$isLiteSpeedHtaccess){
    	// .htaccess file already written so backup live site .htaccess file 
    	$location_name = IWP_STAGING_DIR.'/'.IWP_LiteSpeed::$htaccessFileName;
    	$copy_status = $GLOBALS['FileSystemObj']->touch($location_name);
    	$copy_status = $GLOBALS['FileSystemObj']->copy($live_file, $location_name, true, FS_CHMOD_FILE);
    }


    if (!$copy_status) {

    }

    if ($update_status) {
        update_iterator($live_file, -1);
    }
}

function same_server_replace_pathname($path){
	$dirname = iwp_wp_normalize_path(dirname(dirname(__FILE__)));
    return iwp_wp_normalize_path(str_replace(IWP_ABSPATH ,$dirname , $path));
}

function mkdir_by_path($path, $recursive = true){
    if (empty($path)) {
        return false;
    }
    $path = iwp_wp_normalize_path($path);

    if (file_exists($path)) {
        return false;
    }
    createRecursiveFileSystemFolder($path);
}

function createRecursiveFileSystemFolder($this_temp_folder, $this_absbath_length = null, $override_abspath_check = true) {
    // initFileSystem();

    $folders = explode('/', $this_temp_folder);
    foreach ($folders as $key => $folder) {
        $current_folder = '';
        for($i=0; $i<=$key; $i++){
            $sub_dir = (string) $folders[$i];
            if ($sub_dir === false || $sub_dir === '' || $sub_dir === NULL) {
                continue;
            }
            if (is_windows_machine_IWP() && empty($current_folder)) {
                $current_folder .= $sub_dir;
            } else {
                $current_folder .= '/'. $sub_dir;
            }
        }

        // if (empty($current_folder)){
        //     continue;
        // }

        // if($override_abspath_check && strpos($current_folder.'/', $home_path) === false) {
        //     continue;
        // }
        if (!file_exists($current_folder)) {
            if (!$GLOBALS['FileSystemObj']->mkDir($current_folder, 0755)) {
                $GLOBALS['FileSystemObj']->chmod(dirname($current_folder), 0755);
                if(!$GLOBALS['FileSystemObj']->mkDir($current_folder, 0755)){
                }
            }
        } else {

            if(strpos($current_folder, 'tCapsule') !== false && $GLOBALS['FileSystemObj']->chmod($current_folder, 0755)){
            } else {
            }
        }
    }
}

function is_windows_machine_IWP(){
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return true;
    }
    return false;
}

function IWP_copy_large_file($src, $dst) {
    $src = fopen($src, 'r');
    $dest = fopen($dst, 'w');

    // Try first method:
    while (! feof($src)){
        if (false === fwrite($dest, fread($src, 1024 * 1024 * 2))){
            $error = true;
        }
    }
    // Try second method if first one failed
    if (isset($error) && ($error === true)){
        while(!feof($src)){
            stream_copy_to_stream($src, $dest, 1024 );
        }
        fclose($src);
        fclose($dest);
        return true;
    }
    fclose($src);
    fclose($dest);
    return true;
}

function iwp_copy_dir($live_path, $offset, $deep_dirs){

    init_seek_iterator();

    $is_recursive = ($deep_dirs) ? false : true;

    try{
    	$obj = new IWP_Seek_Iterator();
        $obj->process_iterator($live_path, $offset, $is_recursive);
    } catch(Exception $e){

        $exception_msg = $e->getMessage();

        update_iterator($live_path, 0);
        die_with_json_encode(array('status' => 'continue', 'msg' => 'Seeking failed, Retrying...'));
    }
    update_iterator($live_path, -1);
}

function process_file($iterator, $is_recursive, $path, $key, &$counter, $iterator_loop_limit){
    $file = $iterator->getPathname();

    if (!$iterator->isReadable()) {
        return ;
    }

    $file = iwp_wp_normalize_path($file);

    if (!$is_recursive && IWP_is_dir($file)){
        return;
    }

    if (skip_file($file) === true) {
        check_timeout_iter_file($path,  $key, $counter, $iterator_loop_limit);
        return;
    }

    if(IWP_is_dir($file)){
        return;
    }

    iwp_copy_file($file);

    check_timeout_iter_file($path,  $key, $counter, $iterator_loop_limit);
}

function check_timeout_iter_file($path, $offset, &$temp_counter, $timeout_limit){

	if (++$temp_counter < $timeout_limit) {
		return false;
	}
    // if (!check_for_clone_break()) {
    //     return false;
    // }

    update_iterator($path, $offset);

    global $response_arr;

    $response_arr = array();

    initialize_response_array($response_arr);

    $response_arr['file_iterator'] = true;

    $response_arr['status'] = 'partiallyCompleted';

    $response_arr['break'] = true;

    $response_arr['peak_mem_usage'] = (memory_get_peak_usage(true)/1024/1024);

    status("Extract will continue next call", $success=true, $return=false);
    die(status("multicall", $success=true, $return=false, $response_arr));


    // die_with_json_encode(array('status' => 'continue', 'msg' => get_processing_files_count($type = 'internal_staging'), 'percentage' => 50));
}

function get_processing_files_count($type){
    $dir = get_unfnished_folder();

    if (empty($dir)) {
        return false;
    }

    $copying_file = str_replace(IWP_ABSPATH, '', $dir->name);

    switch ($type) {
        case 'internal_staging':
            $msg = 'Copying  - ';
            break;
        case 'backup':
            $msg = ' ';
            break;
        case 'restore':
            $msg = 'Preparing files to restore - ';
            break;
    }

    if(IWP_is_dir($copying_file) && !empty($dir->offset)){
        // return $msg . $copying_file . ' ('.$dir->offset.')';
        $folders_processed = substr($dir->offset, 0, strpos($dir->offset, '-'));
        $folders_processed = empty($folders_processed) ? '' : ' ( processed ' . $folders_processed . ' folders )';
        return $msg . $copying_file . $folders_processed;
    }

    return $msg . $copying_file;
}

function insert_into_current_process($qry){
    $sql = "insert into " . $this->wpdb->base_prefix . "IWP_current_process (file_path, status, file_hash) values $qry";
    $result = $this->wpdb->query($sql);
}

function init_seek_iterator(){
	if ($GLOBALS['needFileSystem'] == true) {
    	$timeout_limit = 1000;
	}else{
    	$timeout_limit = 2000;
	}
    $seek_file_iterator = new IWP_Seek_Iterator($type = 'LIVE_TO_STAGING', $timeout_limit);
}