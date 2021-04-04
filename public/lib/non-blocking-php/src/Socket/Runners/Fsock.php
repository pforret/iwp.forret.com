<?php

namespace NonBlockingPHP\Socket\Runners;

use NonBlockingPHP\Socket;

class Fsock {

    /**
     * @var integer
     */
    protected $timeout = 30;

    /**
     * @var integer
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $error;

    /**
     * @return boolean
     */
    public function isEnabled() {
        return function_exists('fsockopen');
    }

    /**
     * @param string  $command
     * @return boolean
     */
    public function run($urlData, $postString, $auth) {
        $requestHTTPURL = stristr( $urlData['host'], 'ssl://' ) ? substr( $urlData['host'], 6 ) : $urlData['host'] ;
        if ($urlData['scheme'] == 'http') {
            $requestURL = 'tcp://'.gethostbyname($requestHTTPURL) ;
        }else{
            $requestURL = 'ssl://'.$requestHTTPURL;
        }
        $fsock = fsockopen($requestURL, $urlData['port'], $errno, $errstr, $this->timeout);
        if (!$fsock) {
            $this->error = $errstr;
            $this->error_code = $errno;
            return false;
        }

        $headers = '';
        $headers .= "GET " . $urlData['path'] . "?$postString HTTP/1.0\r\n";
        $headers .= "Host: " . $requestHTTPURL . "\r\n";
        if ($auth != '') {
            $headers .= $auth . "\r\n";
        }
        $headers .= "User-agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0\r\n";
        $headers .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
        $headers .= "Connection: Close\r\n\r\n";
        
        $is_written = fwrite($fsock, $headers);
        if (!$is_written) {
            $this->error = 'Unable to write the headers for the stream';
            return false;
        }
        fclose($fsock);
        return true;
    }

    /**
     * @return array
     */
    public function getError() {
        return array(
            'error' => $this->error,
            'error_code' => $this->error_code
        );
    }

    /**
     * @return string|null
     */
    public function getReturnValue() {
        return $this->returnValue;
    }

}
