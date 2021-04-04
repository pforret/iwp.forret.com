<?php

namespace NonBlockingPHP\Socket\Runners;

class SocketConnect {

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
     * @var boolean
     */
    protected $enabled = false;

    /**
     * @return boolean
     */
    public function isEnabled() {
        return function_exists('socket_create') && function_exists('socket_connect') && function_exists('socket_set_nonblock');
    }

    /**
     * @param string  $command
     */
    public function run($urlData, $postString, $auth) {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $this->error = socket_strerror(socket_last_error());
            return false;
        }

        $service_port = getservbyname($urlData['scheme'], 'tcp');

        $socketConnection = @socket_connect($socket, gethostbyname($urlData['host']), $service_port);
        if ($socketConnection === false) {
            $this->error = socket_strerror(socket_last_error($socket));
            return false;
        }

        if (!socket_set_nonblock($socket)) {
            $this->error = socket_strerror(socket_last_error($socket));
            return false;
        }

        $headers = '';
        $headers .= "GET " . $urlData['path'] . "?postString HTTP/1.0\r\n";
        $headers .= "Host: " . $urlData['host'] . "\r\n";
        if ($auth != '') {
            $headers .= $auth . "\r\n";
        }
        $headers .= "User-agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:16.0) Gecko Firefox/16.0\r\n";
        $headers .= "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n";
        $headers .= "Connection: Close\r\n\r\n";
        
        $is_written = socket_write($socket, $headers, strlen($headers));
        if (!$is_written) {
            $this->error = 'Unable to write the headers for the socket';
            return false;
        }

        socket_close($socket);
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
