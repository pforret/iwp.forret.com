<?php

namespace NonBlockingPHP\Socket;

use NonBlockingPHP\Socket\Runners\Fsock;
use NonBlockingPHP\Socket\Runners\SocketConnect;
use NonBlockingPHP\Socket\Runners\StreamSocketClient;

class Socket {

    /**
     * @var boolean
     */
    public $functionCheck = false;

    /**
     * @var string  
     */
    public $suitableRunner;

    /**
     * @var int
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $error;
    
    /**
     * @var array
     */
    protected $runners = array(
        'fsock' => '\Fsock',
        'socketconnect' => '\SocketConnect',
        'stream' => '\StreamSocketClient'
    );

    /**
     * @return void
     */
    public function __construct($runner='') {
        if ($runner === 'all') {
            $this->functionCheck = $this->socketIsEnabled();
        } else if($runner!='') {
            $this->initRunner($runner);
        }
    }

    /**
     * @return boolean
     */
    public function socketIsEnabled() {

        $stream = new StreamSocketClient();
        if ($stream->isEnabled()) {
            $this->suitableRunner = '\StreamSocketClient';
            return true;
        }

        $fsock = new Fsock();
        if ($fsock->isEnabled()) {
            $this->suitableRunner = '\Fsock';
            return true;
        }

        $socket = new SocketConnect();
        if ($socket->isEnabled()) {
            $this->suitableRunner = '\SocketConnect';
            return true;
        }

        /* No socket runners enabled */
        $this->error = 'Socket communication is not possibile on this server';
        return false;
    }
    
    /**
     * @return array
     */
    public function serverCheck(){
        $result = array();
        
        $this->initRunner('stream');
        $result['stream'] = $this->functionCheck;
        $this->functionCheck = false;
        $this->initRunner('fsock');
        $result['fsock'] = $this->functionCheck;
        $this->functionCheck = false;
        $this->initRunner('socketconnect');
        $result['socketconnect'] = $this->functionCheck;
        
        return $result;
    }

    /**
     * @return void
     */
    public function initRunner($runner) {
        $runner = strtolower($runner);
        if ($runner != '' && in_array($runner, array_keys($this->runners))) {
            $runnerClass = $this->runners[$runner];
            $class = "NonBlockingPHP\Socket\Runners" . $runnerClass;
            $runnerObj = new $class();
            if ($runnerObj->isEnabled()) {
                $this->functionCheck = true;
                $this->suitableRunner = $runnerClass;
            }
        }
    }

    /**
     * @param $url string
     * @return array
     */
    public function parseDestinationURL($url) {
        $parts = parse_url($url);
        if (($parts['scheme'] == 'ssl' || $parts['scheme'] == 'https') && extension_loaded('openssl')) {
            $parts['host'] = "ssl://" . $parts['host'];
            $parts['port'] = 443;
            error_reporting(0);
        } elseif (!isset($parts['port']) || $parts['port'] == '') {
            $parts['port'] = 80;
        }

        return $parts;
    }

    /**
     * @param $paramsArray array
     * @return string
     */
    public function generateBasicAuthToken($paramsArray) {
        $authToken = '';
        if (isset($paramsArray['username']) && isset($paramsArray['password'])) {
            $authToken .= "Authorization: Basic " . base64_encode($paramsArray['username'] . ':' . $paramsArray['password']);
        }
        return $authToken;
    }

    /**
     * @param $argArray array
     * @return string
     */
    public function postStringFromArray($paramsArray) {

        foreach ($paramsArray as $key => &$val) {
            if (is_array($val))
                $val = implode(',', $val);
            $params[] = $key . '=' . urlencode($val);
        }
        $paramsString = implode('&', $params);
        return $paramsString;
    }

    /**
     * @param $url string 
     * @param $params array optional
     * @param $httpAuth array optional
     * @return boolean
     */
    public function runBackgroundJob($url, $params, $httpAuth) {
        if ($this->functionCheck === false) {
            $this->error = 'socket functions is not available';
            $this->error_code = 'socket_functions_not_available';
            return false;
        }

        if ($url == '') {
            $this->error = 'Please provide valid endpoint';
            $this->error_code = 'socket_not_valid_endpoint';
            return false;
        }

        $urlData = $this->parseDestinationURL($url);

        $postString = '';
        if (is_array($params)) {
            $postString = $this->postStringFromArray($params);
        }

        $auth = '';
        if (is_array($httpAuth)) {
            $auth = $this->generateBasicAuthToken($httpAuth);
        }

        $runner = 'NonBlockingPHP\Socket\Runners' . $this->suitableRunner;
        $runnerObj = new $runner();
        $return = $runnerObj->run($urlData, $postString, $auth);
        if (!$return) {
            $errorDetail = $runnerObj->getError();
            $this->error = $errorDetail['error'];
            $this->error_code = $errorDetail['error_code'];
        }
        return $return;
    }

    /**
     * @return array
     */
    public function getError() {
        return array(
            'error' => $this->error,
            'error_code' => $this->error_code,
            'mode'=>'socket'
        );
    }

}
