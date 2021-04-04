<?php

require 'vendor/autoload.php';

use NonBlockingPHP\Execute;

$query = array('historyId' => 12, 'Token' => 'Auto Mode');
$execute = new Execute(array('autoMode' => true));

/* * ***********Command Mode***************** */

/* $query = array('historyId'=>12, 'Token'=>'Command Exec');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'command', 'strictRunner'=>'exec')); */
/* $query = array('historyId'=>12, 'Token'=>'Command passthru');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'command', 'strictRunner'=>'passthru')); */

/* $query = array('historyId'=>12, 'Token'=>'Command shellexec');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'command', 'strictRunner'=>'shellexec'));
 */
/* $query = array('historyId'=>12, 'Token'=>'Command systemexec');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'command', 'strictRunner'=>'systemexec'));
 */
/* * ***********Sockets ***************** */

/* $query = array('historyId'=>12, 'Token'=>'Command stream');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'socket', 'strictRunner'=>'stream'));
 */
/* $query = array('historyId'=>12, 'Token'=>'Command fsock');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'socket', 'strictRunner'=>'fsock')); */

/* $query = array('historyId'=>12, 'Token'=>'Command socketconnect');
  $execute = new Execute(array('autoMode'=>false,'strictMode'=>'socket', 'strictRunner'=>'socketconnect')); */

/* sample auth parameters */
$auth = array('username' => 'babu', 'password' => 'passme');
/* sample parameters */
$params = array(
    'url' => 'http://localhost/non-blocking-php/poc/exectest.php',
    'command' => 'php /var/www/html/non-blocking-php/poc/exectest.php',
    'auth' => $auth,
    'args' => $query
);

echo "<pre>";
$result = $execute->run($params);
if ($result) {
    echo "Background call initiated";
} else {
    print_r($execute->getError());
}