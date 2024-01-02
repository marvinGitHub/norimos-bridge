<?php

require 'bootstrap.php';

$configuration = new Configuration(__DIR__ . '/config/config.json', __DIR__ . '/config/default.json');
if (false === $config = $configuration->load()) {
    echo 'Please check system configuration.';
    exit;
}

$log = new Log((string)$config['logfileSystem'], (bool)$config['verbose']);
$log->print('info', 'Starting consumer, please wait ...');

$serialDeviceConfiguration = new SerialDeviceConfiguration($serialDevice = (string)$config['serial.device']);
if (!$serialDeviceConfiguration->allowReceivingInput(true)) {
    $log->print('error', 'Failed to setup serial input');
}
if (!$serialDeviceConfiguration->setBaudrate((int)$config['serial.baudrate'])) {
    $log->print('error', 'Failed to setup serial baudrate');
}
if (!$serialDeviceConfiguration->setParity((string)$config['serial.parity'])) {
    $log->print('error', 'Failed to setup serial parity');
}
if (!$serialDeviceConfiguration->setCharacterLength((int)$config['serial.characterLength'])) {
    $log->print('error', 'Failed to setup serial character length');
}
if (!$serialDeviceConfiguration->setStopBits((int)$config['serial.stopBits'])) {
    $log->print('error', 'Failed to setup serial stop bits');
}
if (!$serialDeviceConfiguration->setFlowControl((string)$config['serial.flowControl'])) {
    $log->print('error', 'Failed to setup serial flow control');
}

$serial = new Serial();
$serial->deviceSet($serialDevice);
$serial->deviceOpen();

$buffer = new StringBuffer();
$queue = new AlarmQueue();
$history = new AlarmHistory((string)$config['logfileAlarmHistory']);
$dump = new Dump((string)$config['logfileDump']);
$storage = new KeyValueStorage((string)$config['storagePath']);

$pluginContext = new PluginContext($configuration, $storage, $buffer, $serial, $queue, $history, $log, $dump);
$pluginHandler = new PluginHandler($pluginContext);

$pluginSerialProcessor = new PluginSerialProcessor();
$pluginTelegramProcessor = new PluginPrinterLogProcessor();
$pluginMQTTPublisher = new PluginMQTTPublisher(
    (string)$config['pluginMQTTPublisher.mqttBroker'],
    (string)$config['pluginMQTTPublisher.topicAlarm'],
    (int)$config['pluginMQTTPublisher.retries'],
    (int)$config['pluginMQTTPublisher.timeout'],
    (int)$config['pluginMQTTPublisher.timeoutAlarm']);

$pluginHandler->register($pluginSerialProcessor);
$pluginHandler->register($pluginTelegramProcessor);
$pluginHandler->register($pluginMQTTPublisher);

while (true) {
    $config = $configuration->load();

    $log->setVerbose((bool)$config['verbose'] ?? false);

    $config['dump'] ? $dump->enable() : $dump->disable();

    usleep((int)$config['consumer.waitMicroseconds'] ?? 100);

    $config['pluginMQTTPublisher'] ? $pluginHandler->enable($pluginMQTTPublisher) : $pluginHandler->disable($pluginMQTTPublisher);

    $pluginHandler->run();
}
