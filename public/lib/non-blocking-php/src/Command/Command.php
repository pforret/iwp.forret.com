<?php

namespace NonBlockingPHP\Command;

use NonBlockingPHP\Command\Runners\Exec;
use NonBlockingPHP\Command\Runners\Passthru;
use NonBlockingPHP\Command\Runners\ShellExec;
use NonBlockingPHP\Command\Runners\SystemExec;
use NonBlockingPHP\Command\ExitCode;

class Command {

    /**
     * @var boolean
     */
    protected $nohupEnabled = false;

    /**
     * @var boolean
     */
    protected $functionCheck = false;

    /**
     * @var string  
     */
    public $suitableRunner;

    /**
     * @var integer
     */
    protected $returnValue = null;

    /**
     * @var int
     */
    protected $error_code;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $command_prefix = 'nohup ';

    /**
     * @var string 
     */
    protected $command_suffix = '> /dev/null &';

    /**
     * @var array
     */
    protected $runners = array(
        'exec' => '\Exec',
        'systemexec' => '\SystemExec',
        'passthru' => '\Passthru',
        'shellexec' => '\ShellExec',
    );

    /**
     * @var array 
     */
    protected $null_return_runners = array(
        '\ShellExec',
        '\Passthru'
    );

    /**
     * @return void
     */
    public function __construct($runner = '') {
        if ($runner === 'all') {
            $this->functionCheck = $this->commandIsEnabled();
            $this->nohupEnabled = $this->isNoHupAvaiable();
        } else if ($runner != '') {
            $this->initRunner($runner);
        }
    }

    /**
     * @return boolean
     */
    public function commandIsEnabled() {

        $exec = new Exec();
        if ($exec->isEnabled()) {
            $this->suitableRunner = '\Exec';
            return true;
        }

        $system = new SystemExec();
        if ($system->isEnabled()) {
            $this->suitableRunner = '\SystemExec';
            return true;
        }

        $passthru = new Passthru();
        if ($passthru->isEnabled()) {
            $this->suitableRunner = '\Passthru';
            return true;
        }

        $shellexec = new ShellExec();
        if ($shellexec->isEnabled()) {
            $this->suitableRunner = '\ShellExec';
            return true;
        }

        /* No command runners enabled */
        return false;
    }

    /**
     * @return true|false
     */
    public function isNoHupAvaiable() {

        if ($this->functionCheck) {
            $runner = 'NonBlockingPHP\Command\Runners' . $this->suitableRunner;

            $runnerObj = new $runner();
            $nohup = $runnerObj->run('nohup echo hello world');
            $this->returnValue = $runnerObj->getReturnValue();
            if (($this->returnValue === 0 || trim($this->returnValue) === 'hello world') && trim($nohup) === 'hello world') {
                $this->nohupEnabled = true;
                return true;
            }
            $this->error = ExitCode::getDescription($this->returnValue);
        } else {
            $this->error = 'Function is not available';
            $this->error_code = 'command_functions_not available';
        }
        return false;
    }

    /**
     * @return array
     */
    public function serverCheck() {
        $result = array();
        $this->initRunner('exec');
        $result['exec'] = $this->nohupEnabled;
        $this->initRunner('systemexec');
        $result['systemexec'] = $this->nohupEnabled;
        $this->initRunner('passthru');
        $result['passthru'] = $this->nohupEnabled;
        $this->initRunner('shellexec');
        $result['shellexec'] = $this->nohupEnabled;
        return $result;
    }

    /**
     * @return boolean
     */
    public function isNoHupAvaiableForRunner() {
        $runner = 'NonBlockingPHP\Command\Runners' . $this->suitableRunner;
        $runnerObj = new $runner();
        $nohup = $runnerObj->run('nohup echo hello world');
        $this->returnValue = $runnerObj->getReturnValue();
        if (($this->returnValue === 0 || trim($this->returnValue) == 'hello world') && trim($nohup) == 'hello world') {
            $this->nohupEnabled = true;
            return true;
        } else {
            $this->nohupEnabled = false;
        }
        $this->error = ExitCode::getDescription($this->returnValue);
        $this->error_code = 'no_hup_not_available';
        return false;
    }

    /**
     * @return void
     */
    public function initRunner($inrunner) {
        $runner = strtolower($inrunner);
        if ($runner != '' && in_array($runner, array_keys($this->runners))) {
            $runnerClass = $this->runners[$runner];
            $class = "NonBlockingPHP\Command\Runners" . $runnerClass;
            $runnerObj = new $class();
            if ($runnerObj->isEnabled()) {
                $this->suitableRunner = $runnerClass;
                $this->isNoHupAvaiableForRunner();
            } else {
                $this->nohupEnabled = false;
            }
        }
    }

    /**
     * @return boolean
     */
    public function runBackgroundJob($executables, $args) {
        if ($this->nohupEnabled === false) {
            return false;
        }
        $arguments = '';
        if (is_array($args)) {
            $arguments = $this->argsFromArray($args);
        }

        $runner = 'NonBlockingPHP\Command\Runners' . $this->suitableRunner;
        $runnerObj = new $runner();
        $command = $this->command_prefix . $executables . $arguments . $this->command_suffix;
        $runnerObj->run($command);
        $this->returnValue = $runnerObj->getReturnValue();
        if ($this->returnValue === 0 || in_array($this->suitableRunner, $this->null_return_runners)) {
            return true;
        } else {
            $this->error = ExitCode::getDescription($this->returnValue);
            $this->error_code = 'background_action_failed';
            return false;
        }
    }

    /**
     * @param argArray array
     * @return string
     */
    public function argsFromArray($argArray) {
        $arg = ' commandnb ';
        foreach ($argArray as $argName => $argValue) {
            $arg .= $argName . '=' . $argValue . ' ';
        }
        return $arg;
    }

    /**
     * @return array
     */
    public function getError() {
        return array(
            'error' => $this->error,
            'error_code' => $this->error_code,
            'runner' => $this->suitableRunner,
            'mode' => 'command'
        );
    }

}
