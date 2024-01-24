<?php

/** @var Configuration $configuration */
$configuration = include 'bootstrap.php';

try {
    $supportedCommands = [
        'showSystemLog',
        'showConsumerLog',
        'clearSystemLog',
        'clearConsumerLog',
        'showSystemConfigurationEditor',
        'resetSystemConfiguration',
        'showDump',
        'clearDump',
        'showSerialDeviceConfiguration',
        'findSerialDevices',
        'enableDump',
        'disableDump',
        'enablePluginAlarmPublisher',
        'disablePluginAlarmPublisher',
        'saveSystemConfiguration',
        'getRecentAlarms',
        'restartConsumer',
        'showSupervisorStatus'
    ];
    sort($supportedCommands);

    function stdout(string $content, bool $formatted = true)
    {
        if ($formatted) {
            $content = sprintf('<pre>%s</pre>', $content);
        }
        echo $content;
    }

    if (!isset($_POST['command'])) {
        stdout(sprintf('Please choose between supported commands: [%s]', implode(', ', $supportedCommands)));
        exit;
    }

    if (!in_array($command = $_POST['command'], $supportedCommands)) {
        stdout(sprintf('Unsupported command: %s', $command));
        exit;
    }

    $systemLog = new Log((string)$configuration['logfileSystem']);
    $consumerLog = new Log((string)$configuration['logfileConsumer']);
    $history = new AlarmHistory((string)$configuration['logfileAlarmHistory']);
    $dump = new Dump((string)$configuration['logfileDump']);
    $serialDeviceConfiguration = new SerialDeviceConfiguration((string)$configuration['serial.device']);

    switch ($command) {
        case 'saveSystemConfiguration':
            if (!isset($_POST['config'])) {
                stdout('No configuration provided');
                exit;
            }

            if (!is_array($config = json_decode($_POST['config'], true))) {
                stdout('Posted configuration contains errors. Please check JSON syntax.');
                exit;
            }

            $configuration->override($config);

            if ($configuration->save()) {
                stdout('System configuration has been saved successfully.');
            } else {
                stdout('System configuration could not be saved.');
            }

            exit;
        case 'showSystemConfigurationEditor':
            $config = $configuration->get();
            ksort($config);
            $config = json_encode($config, JSON_PRETTY_PRINT);
            $config = str_replace('\/', '/', $config);

            echo <<<HTML
<form action="server.php" method="post">
    <textarea name="config" style="width: 100%; height: 90%;">$config</textarea><br />
    <button name="command" value="saveSystemConfiguration">Save</button>
</form>
HTML;
            exit;
        case 'clearSystemLog':
            $systemLog->clear();
            stdout($message = 'System Log has been cleared.');
            $systemLog->print('info', $message);
            exit;
        case 'clearConsumerLog':
            $consumerLog->clear();
            stdout($message = 'Consumer Log has been cleared.');
            $consumerLog->print('info', $message);
            exit;
        case 'showSystemLog':
            stdout($systemLog->load());
            exit;
        case 'showConsumerLog':
            stdout((string)file_get_contents($configuration['logfileConsumer']));
            exit;
        case 'resetSystemConfiguration':
            $configuration->restore();
            stdout($message = 'System Configuration has been restored to default values.');
            $systemLog->print('info', $message);
            exit;
        case 'showDump':
            $systemLog->print('info', 'Load dump');
            if (empty($data = $dump->load())) {
                stdout($message = 'No dump available.');
                $systemLog->print('info', $message);
                exit;
            }
            stdout($data);
            exit;
        case 'clearDump':
            $dump->clear();
            stdout($message = 'Dump has been cleared.');
            $systemLog->print('info', $message);
            exit;
        case 'showSerialDeviceConfiguration':
            $systemLog->print('info', 'Load serial device configuration');
            if (empty($currentSerialDeviceConfiguration = $serialDeviceConfiguration->load())) {
                stdout($message = 'Failed getting current serial device configuration.');
                exit;
            }
            stdout($currentSerialDeviceConfiguration);
            exit;
        case 'findSerialDevices':
            $systemLog->print('info', 'Searching for serial devices...');
            $serialDevices = $serialDeviceConfiguration->findSerialDevices();
            if (empty($serialDevices)) {
                stdout($message = 'No serial device found.');
                $systemLog->print('error', $message);
                exit;
            }
            stdout($message = 'Found serial devices:');
            $systemLog->print('info', $message);
            stdout($message = implode(', ', $serialDevices));
            $systemLog->print('log', $message);
            exit;
        case 'enableDump':
            $configuration['dump'] = true;
            $configuration->save();
            stdout($message = 'Dump has been enabled.');
            $systemLog->print('info', $message);
            exit;
        case 'disableDump':
            $configuration['dump'] = false;
            $configuration->save();
            stdout($message = 'Dump has been disabled.');
            $systemLog->print('info', $message);
            exit;
        case 'disablePluginAlarmPublisher':
            $configuration['pluginAlarmPublisher.enabled'] = false;
            $configuration->save();
            stdout($message = 'Plugin Alarm Publisher has been disabled.');
            $systemLog->print('info', $message);
            exit;
        case 'enablePluginAlarmPublisher':
            $configuration['pluginAlarmPublisher.enabled'] = true;
            $configuration->save();
            stdout($message = 'Plugin Alarm Publisher has been enabled.');
            $systemLog->print('info', $message);
            exit;
        case 'getRecentAlarms':
            if (count($recent = $history->getRecentAlarms())) {
                foreach ($recent as $data) {
                    $alarm = new Alarm($data);
                    stdout($alarm->toString());
                }
            }
            exit;
        case 'restartConsumer':
            $exitCode = Helper::execute('supervisorctl restart norimos-consumer');
            if (0 === $exitCode) {
                stdout('Successfully restarted consumer');
            } else {
                stdout('Failed to restart consumer');
            }
            exit;
        case 'showSupervisorStatus':
            $stdout = null;
            Helper::execute('supervisorctl status', $stdout);
            stdout($stdout);
            exit;
    }
} catch (Exception $e) {
    stdout(sprintf('Unknown server error: %s', $e->getMessage()));
    stdout($e->getTraceAsString());
    exit;
}
