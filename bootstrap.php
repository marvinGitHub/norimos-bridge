<?php

require_once 'src/autoload.php';
require_once 'vendor/autoload.php';

ini_set('serialize_precision', 10);

return new Configuration(__DIR__ . '/config/config.json', __DIR__ . '/config/default.json', true);