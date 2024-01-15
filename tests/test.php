<?php

require_once __DIR__ . '/../bootstrap.php';

$lines = explode(PHP_EOL, file_get_contents(__DIR__ . '/printer.log'));

$serialDeviceConfiguration = new SerialDeviceConfiguration($serialDevice = '/dev/ttyUSB0');
$serialDeviceConfiguration->allowReceivingInput(true);

$serialDeviceConfiguration->setBaudrate(19200);
$serialDeviceConfiguration->setParity('none');
$serialDeviceConfiguration->setCharacterLength(8);
$serialDeviceConfiguration->setStopBits(1);
$serialDeviceConfiguration->setFlowControl('none');

$serial = new Serial();
$serial->deviceSet($serialDevice);
$serial->deviceOpen();

foreach ($lines as $line) {
    $serial->write($line);
    $serial->write(PHP_EOL);
}