<?php

class AlarmHistory
{
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function add(Alarm $alarm)
    {
        return file_put_contents($this->getFilename(), sprintf('%s%s', json_encode($alarm->toArray()), PHP_EOL), FILE_APPEND);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename)
    {
        $this->filename = $filename;
    }

    public function getRecentAlarms(int $limit = 100): array
    {
        return array_slice($this->getAlarms(), -$limit);
    }

    public function getAlarms(): array
    {
        $data = file_get_contents($this->getFilename());
        if (false === $data) {
            return [];
        }
        $alarms = [];
        foreach (explode(PHP_EOL, $data) as $line) {
            if (null !== $alarm = json_decode($line, true)) {
                $alarms[] = $alarm;
            }
        }
        return $alarms;
    }
}