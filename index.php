<?php

require 'authorization.php';
/** @var Configuration $configuration */
$configuration = include 'bootstrap.php';

$systemLog = new Log((string)$configuration['logfileSystem']);
$serialDeviceConfiguration = new SerialDeviceConfiguration((string)$configuration['serial.device']);

echo <<<HTML
<html lang="en">
<body style="font-family: sans-serif">
<pre>
  _   _            _                       ____       _     _            
 | \ | |          (_)                     |  _ \     (_)   | |           
 |  \| | ___  _ __ _ _ __ ___   ___  ___  | |_) |_ __ _  __| | __ _  ___ 
 | . ` |/ _ \| '__| | '_ ` _ \ / _ \/ __| |  _ <| '__| |/ _` |/ _` |/ _ \
 | |\  | (_) | |  | | | | | | | (_) \__ \ | |_) | |  | | (_| | (_| |  __/
 |_| \_|\___/|_|  |_|_| |_| |_|\___/|___/ |____/|_|  |_|\__,_|\__, |\___|
                                                               __/ |     
                                                              |___/      
</pre>
<form action="server.php" method="post">
    <button name="command" value="getRecentAlarms">Show Recent Alarms</button>  
    <button name="command" value="showSystemLog">Show System Log</button>
    <button name="command" value="showConsumerLog">Show Consumer Log</button>        
    <button name="command" value="clearSystemLog">Clear System Log</button>
    <button name="command" value="clearConsumerLog">Clear Consumer Log</button>
    <button name="command" value="showSystemConfigurationEditor">Show System Configuration</button>
    <button name="command" value="resetSystemConfiguration">Reset System Configuration</button>
    <button name="command" value="showDump">Show Dump</button>    
    <button name="command" value="enableDump">Enable Dump</button>
    <button name="command" value="disableDump">Disable Dump</button> 
    <button name="command" value="clearDump">Clear Dump</button>  
    <button name="command" value="showSerialDeviceConfiguration">Show Serial Device Configuration</button>
    <button name="command" value="findSerialDevices">Find Serial Devices</button>
    <button name="command" value="enablePluginAlarmPublisher">Enable Alarm Publisher</button>
    <button name="command" value="disablePluginAlarmPublisher">Disable Alarm Publisher</button> 
    <button name="command" value="restartConsumer">Restart Consumer</button> 
    <button name="command" value="showSupervisorStatus">Show Supervisor Status</button>
</form>
</body>
</html>

HTML;



