<?php

// include '/path/to/my/framework/bootstrap.php';

include dirname(__FILE__) . '/Callback.php';
use phpydaemon\Callback;

$job = Callback::getJob();
// MyFramework::switchUser($job->userId);
Callback::runJob($job);

?>
