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
$pluginTelegramProcessor = new PluginPrinterLogProcessor();
$pluginMQTTPublisher = new PluginMQTTPublisher(
    (string)$configuration['pluginMQTTPublisher.mqttBroker'],
    (string)$configuration['pluginMQTTPublisher.topicAlarm'],
    (int)$configuration['pluginMQTTPublisher.retries'],
    (int)$configuration['pluginMQTTPublisher.timeout'],
    (int)$configuration['pluginMQTTPublisher.timeoutAlarm']);

$pluginHandler->register($pluginSerialProcessor);
$pluginHandler->register($pluginTelegramProcessor);
$pluginHandler->register($pluginMQTTPublisher);

while (true) {
    try {
        $configuration->load();
    } catch (Exception $e) {
        Console::println(sprintf('Unable to reload system configuration: %s', $e->getMessage()));
    }

    $configuration['dump'] ? $dump->enable() : $dump->disable();

    usleep((int)$configuration['consumer.waitMicroseconds'] ?? 100);

    $configuration['pluginMQTTPublisher'] ? $pluginHandler->enable($pluginMQTTPublisher) : $pluginHandler->disable($pluginMQTTPublisher);

    $pluginHandler->run();
}
