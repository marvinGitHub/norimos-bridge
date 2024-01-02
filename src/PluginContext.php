<?php

class PluginContext
{
    use ConfigurationAwareTrait;

    private $storage;
    private $alarmQueue;
    private $alarmHistory;
    private $log;
    private $buffer;
    private $serial;
    private $dump;

    public function __construct(Configuration $configuration, KeyValueStorage $storage, StringBuffer $buffer, Serial $serial, AlarmQueue $alarmQueue, AlarmHistory $alarmHistory, Log $log, Dump $dump)
    {
        $this->setConfiguration($configuration);

        $this->storage = $storage;
        $this->buffer = $buffer;
        $this->serial = $serial;
        $this->alarmQueue = $alarmQueue;
        $this->alarmHistory = $alarmHistory;
        $this->log = $log;
        $this->dump = $dump;
    }

    public function getStorage(): KeyValueStorage
    {
        return $this->storage;
    }

    public function getBuffer(): StringBuffer
    {
        return $this->buffer;
    }

    public function getSerial(): Serial
    {
        return $this->serial;
    }

    public function getAlarmQueue(): AlarmQueue
    {
        return $this->alarmQueue;
    }

    public function getAlarmHistory(): AlarmHistory
    {
        return $this->alarmHistory;
    }

    public function getLog(): Log
    {
        return $this->log;
    }

    public function getDump(): Dump
    {
        return $this->dump;
    }
}