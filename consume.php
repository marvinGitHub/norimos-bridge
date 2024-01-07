<?php

/** @var Configuration $configuration */
$configuration = include 'bootstrap.php';

$systemLog = new Log((string)$configuration['logfileSystem']);
$systemLog->print('info', 'Starting consumer, please wait ...');

$serialDeviceConfiguration = new SerialDeviceConfiguration($serialDevice = (string)$configuration['serial.device']);
if (!$serialDeviceConfiguration->allowReceivingInput(true)) {
    $systemLog->print('error', 'Failed to setup serial input');
}
if (!$serialDeviceConfiguration->setBaudrate((int)$configuration['serial.baudrate'])) {
    $systemLog->print('error', 'Failed to setup serial baudrate');
}
if (!$serialDeviceConfiguration->setParity((string)$configuration['serial.parity'])) {
    $systemLog->print('error', 'Failed to setup serial parity');
}
if (!$serialDeviceConfiguration->setCharacterLength((int)$configuration['serial.characterLength'])) {
    $systemLog->print('error', 'Failed to setup serial character length');
}
if (!$serialDeviceConfiguration->setStopBits((int)$configuration['serial.stopBits'])) {
    $systemLog->print('error', 'Failed to setup serial stop bits');
}
if (!$serialDeviceConfiguration->setFlowControl((string)$configuration['serial.flowControl'])) {
    $systemLog->print('error', 'Failed to setup serial flow control');
}

$serial = new Serial();
$serial->deviceSet($serialDevice);
$serial->deviceOpen();

$buffer = new StringBuffer();
$queue = new AlarmQueue();
$history = new AlarmHistory((string)$configuration['logfileAlarmHistory']);
$dump = new Dump((string)$configuration['logfileDump']);
$storage = new KeyValueStorage((string)$configuration['storagePath']);

$pluginContext = new PluginContext($configuration, $storage, $buffer, $serial, $queue, $history, $systemLog, $dump);
$pluginHandler = new PluginHandler($pluginContext);

$pluginSerialProcessor = new PluginSerialProcessor();
$pluginPrinterLogProcessor = new PluginPrinterLogProcessor();
$pluginAlarmPublisher = new AlarmPublisher(
    (string)$configuration['pluginAlarmPublisher.mqttBroker'],
    (int)$configuration['pluginAlarmPublisher.retries'],
    (int)$configuration['pluginAlarmPublisher.timeout'],
    (int)$configuration['pluginAlarmPublisher.timeoutPerElement']);
$pluginAlarmPublisher->setTopic((string)$configuration['pluginAlarmPublisher.topic']);

$pluginHandler->register($pluginSerialProcessor);
$pluginHandler->register($pluginPrinterLogProcessor);
$pluginHandler->register($pluginAlarmPublisher);

$configuration['dump'] ? $dump->enable() : $dump->disable();
$configuration['pluginAlarmPublisher.enabled'] ? $pluginHandler->enable($pluginAlarmPublisher) : $pluginHandler->disable($pluginAlarmPublisher);

while (true) {
    usleep((int)$configuration['consumer.waitMicroseconds'] ?? 100);
    $pluginHandler->run();
}
