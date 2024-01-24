<?php

require 'authorization.php';
/** @var Configuration $configuration */
$configuration = include 'bootstrap.php';

$history = new AlarmHistory((string)$configuration['logfileAlarmHistory']);

$blockAlarms = '';

foreach (array_reverse($history->getAlarms()) as $data) {
    $alarm = new Alarm($data);
    $blockAlarms .= $alarm->toString() . PHP_EOL;
}

echo <<<HTML
<html lang="en">
<body style="font-family: sans-serif">
<b>Noris N300 Alarms</b>
<pre>
{$blockAlarms}
</pre>
</body>
</html>
HTML;
