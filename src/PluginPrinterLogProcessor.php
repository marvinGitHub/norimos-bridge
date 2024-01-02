<?php

class PluginPrinterLogProcessor extends PluginAbstract
{
    const REGEX_PRINTER_LOG = '#([A-Z0-9]{4,})\s+([0-9]{2}\.[0-9]{2}/[0-9]{2}:[0-9]{2})\s+([A-Z0-9]{3,})\s+(.{2})\s+([^\r\n]*)[\r\n]+#i';

    public function run(PluginContext $context)
    {
        $buffer = $context->getBuffer()->toString();

        $matches = [];

        if (0 === preg_match(static::REGEX_PRINTER_LOG, $buffer, $matches)) {
            return;
        }

        $context->getLog()->print('info', sprintf('Received incoming alarm on channel %s', $matches[1]));

        $alarm = new Alarm([
            'channel' => $matches[1],
            'datetime' => $matches[2],
            'group' => $matches[3],
            'state' => $matches[4],
            'message' => $matches[5]
        ]);

        $context->getBuffer()->remove($matches[0]);
        $context->getAlarmQueue()->queue($alarm);

        $addedToHistory = $context->getAlarmHistory()->add($alarm);
        if (!$addedToHistory) {
            $context->getLog()->print(LOG::ERROR, 'Unable to add alarm to history');
        }
    }
}