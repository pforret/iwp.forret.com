<?php

namespace NonBlockingPHP\Command\Runners;

class SystemExec {

    /**
     * @var string
     */
    protected $returnValue;

    /**
     * @var string
     */
    protected $output;

    /**
     * @return boolean
     */
    public function isEnabled() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'){
            return false;
        }
        return function_exists('system');
    }

    /**
     * @param string  $command
     */
    public function run($command) {
        ob_start();
        @system((string) $command, $this->returnValue);
        $this->output = ob_get_contents();
        ob_end_clean();
        return $this->output;
    }

    /**
     * @return string|null
     */
    public function getReturnValue() {
        return $this->returnValue;
    }

}
