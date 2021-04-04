<?php

namespace NonBlockingPHP;

use NonBlockingPHP\Command\Command;
use NonBlockingPHP\Socket\Socket;

class Execute {

    /**
     * @var boolean
     */
    protected $autoMode = true;

    /**
     * @var string
     */
    protected $strictMode = null;

    /**
     * @var string
     */
    protected $strictRunner = 'all';

    /**
     * @var string
     */
    protected $currentMode;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $runner;

    /**
     * @var string
     */
    protected $mode;
    
    /**
     * @var string
     */
    protected $upperConnectionLevel='commandMode';

    /**
     * @return void
     */
    public function __construct($params) {
        $this->setParams($params);
        return $this;
    }

    /**
     * @return boolean
     */
    public function run($params) {
        if ($this->autoMode) {
            if ($this->upperConnectionLevel=='commandMode' && $this->commandRunner($params)) {
                return true;
            } else if ($this->socketRunner($params) ) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->strictMode === 'command') {
            if ($this->commandRunner($params)) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->strictMode === 'socket') {
            if ($this->socketRunner($params)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @return boolean
     */
    public function commandRunner($params) {
        $this->currentMode = new Command($this->strictRunner);
        $this->mode = 'command';
        if ($this->currentMode->runBackgroundJob($params['command'], $params['args'])) {
            $this->runner = $this->currentMode->suitableRunner;
            return true;
        } else {
            $errorData = $this->currentMode->getError();
            $this->error = $errorData['error'];
            $this->error_code = $errorData['error_code'];
            $this->runner = $this->currentMode->suitableRunner;
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function socketRunner($params) {
        $this->currentMode = new Socket($this->strictRunner);
        $this->mode = 'socket';
        if ($this->currentMode->runBackgroundJob($params['url'], $params['args'], $params['auth'])) {
            $this->runner = $this->currentMode->suitableRunner;
            return true;
        } else {
            $errorData = $this->currentMode->getError();
            $this->error = $errorData['error'];
            $this->error_code = $errorData['error_code'];
            $this->runner = $this->currentMode->suitableRunner;
            return false;
        }
    }

    /**
     * @param $setParams array
     * @return void
     */
    private function setParams($setParams) {
        if (isset($setParams['autoMode'])) {
            $this->autoMode = $setParams['autoMode'];
        }

        if (!$this->autoMode && isset($setParams['strictMode'])) {
            $this->strictMode = $setParams['strictMode'];
        }

        if (!$this->autoMode && isset($setParams['strictRunner'])) {
            $this->strictRunner = $setParams['strictRunner'];
        }
        
        if(isset($setParams['upperConnectionLevel'])){
            $this->upperConnectionLevel = $setParams['upperConnectionLevel'];
        }
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
     * @return array
     */
    public function getModeData(){
        return array(
            'autoMode'=>$this->autoMode,
            'mode'=>$this->mode,
            'runner'=>$this->runner
        );
    }

    /**
     * @return array
     */
    public function serverRequirement() {
        $command = new Command();
        $commandCheck = $command->serverCheck();
        $socket = new Socket();
        $socketCheck = $socket->serverCheck();
        $result = array_merge($commandCheck, $socketCheck);
        return $result;
    }

}
