<?php

require(__DIR__ . "/../vendor/autoload.php");

$ymlParse = new \Symfony\Component\Yaml\Yaml();
$yml = $ymlParse->parse(__DIR__ . "/../app/config/parameters.yml");
$host = $yml['parameters']['beanstalkd_host'];
$port = $yml['parameters']['beanstalkd_port'];
$tube = $yml['parameters']['beanstalkd_tube'];
$timeout = 10;

$queue = new \Pheanstalk\Pheanstalk($host . ":" . $port, $timeout);

// Set which queues to bind to
$queue->watch($tube);

$console = realpath(__DIR__ . '/../app/console');

// pick a job and process it
while ($job = $queue->reserve()) {
    $data = $job->getData();
    $data = json_decode($data, true);

    sleep(1);
    exec('php ' . $console . ' nsm:generate:sales-incentive ' . $data['agentId'] . ' ' . $data['durationType'] . ' d d');
    echo $data['agentId'].PHP_EOL;
    $queue->delete($job);
}