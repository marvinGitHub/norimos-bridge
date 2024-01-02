<?php

require 'bootstrap.php';

try {
    $supportedCommands = [
        'showSystemLog',
        'showConsumerLog',
        'clearSystemLog',
        'showSystemConfigurationEditor',
        'resetSystemConfiguration',
        'showDump',
        'clearDump',
        'showSerialDeviceConfiguration',
        'findSerialDevices',
        'enableDump',
        'disableDump',
        'enablePluginMQTTPublisher',
        'disablePluginMQTTPublisher',
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

    function printJSON($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT) ?? '';
    }

    if (!isset($_POST['command'])) {
        stdout(sprintf('Please choose between supported commands: [%s]', implode(', ', $supportedCommands)));
        exit;
    }

    if (!in_array($command = $_POST['command'], $supportedCommands)) {
        stdout(sprintf('Unsupported command: %s', $command));
        exit;
    }

    $configuration = new Configuration(__DIR__ . '/config/config.json', __DIR__ . '/config/default.json');
    if (empty($config = $configuration->load())) {
        stdout('Please check system configuration.');
        exit;
    }

    $log = new Log((string)$config['logfileSystem'], (bool)$config['verbose']);
    $history = new AlarmHistory((string)$config['logfileAlarmHistory']);
    $dump = new Dump((string)$config['logfileDump']);
    $serialDeviceConfiguration = new SerialDeviceConfiguration((string)$config['serial.device']);
    $storage = new KeyValueStorage((string)$config['storagePath']);

    switch ($command) {
        case 'saveSystemConfiguration':
            if (!isset($_POST['config'])) {
                stdout('No configuration provided');
                exit;
            }

            try {
                if (!$configuration->save($_POST['config'])) {
                    throw new Exception();
                }
            } catch (Exception $e) {
                stdout('Posted configuration contains errors. Please check JSON syntax.');
                exit;
            }

            stdout('System configuration has been saved successfully.');
            exit;
        case 'showSystemConfigurationEditor':
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
            $log->clear();
            stdout($message = 'System Log has been cleared.');
            $log->print('info', $message);
            exit;
        case 'showSystemLog':
            stdout($log->load());
            exit;
        case 'showConsumerLog':
            stdout((string)file_get_contents($config['logfileConsumer']));
            exit;
        case 'resetSystemConfiguration':
            $configuration->restore();
            stdout($message = 'System Configuration has been restored to default values.');
            $log->print('info', $message);
            exit;
        case 'showDump':
            $log->print('info', 'Load dump');
            if (empty($data = $dump->load())) {
                stdout($message = 'No dump available.');
                $log->print('info', $message);
                exit;
            }
            stdout($data);
            exit;
        case 'clearDump':
            $dump->clear();
            stdout($message = 'Dump has been cleared.');
            $log->print('info', $message);
            exit;
        case 'showSerialDeviceConfiguration':
            $log->print('info', 'Load serial device configuration');
            if (empty($currentSerialDeviceConfiguration = $serialDeviceConfiguration->load())) {
                stdout($message = 'Failed getting current serial device configuration.');
                exit;
            }
            stdout($currentSerialDeviceConfiguration);
            exit;
        case 'findSerialDevices':
            $log->print('info', 'Searching for serial devices...');
            $serialDevices = $serialDeviceConfiguration->findSerialDevices();
            if (empty($serialDevices)) {
                stdout($message = 'No serial device found.');
                $log->print('error', $message);
                exit;
            }
            stdout($message = 'Found serial devices:');
            $log->print('info', $message);
            stdout($message = implode(', ', $serialDevices));
            $log->print('log', $message);
            exit;
        case 'enableDump':
            $config['dump'] = true;
            $configuration->save($config);
            stdout($message = 'Dump has been enabled.');
            $log->print('info', $message);
            exit;
        case 'disableDump':
            $config['dump'] = false;
            $configuration->save($config);
            stdout($message = 'Dump has been disabled.');
            $log->print('info', $message);
            exit;
        case 'disablePluginMQTTPublisher':
            $config['pluginMQTTPublisher'] = false;
            $configuration->save($config);
            stdout($message = 'Plugin MQTT Publisher has been disabled.');
            $log->print('info', $message);
            exit;
        case 'enablePluginMQTTPublisher':
            $config['pluginMQTTPublisher'] = true;
            $configuration->save($config);
            stdout($message = 'Plugin MQTT Publisher has been enabled.');
            $log->print('info', $message);
            exit;
        case 'getRecentAlarms':
            if (count($recent = $history->getRecentAlarms())) {
                foreach ($recent as $alarm) {
                    stdout(sprintf('%s %s %s %s %s', $alarm['channel'], $alarm['datetime'], $alarm['group'], $alarm['state'], $alarm['message']));
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
    stdout('Unknown server error');
    exit;
}
