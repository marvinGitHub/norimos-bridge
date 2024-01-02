<?php

class AlarmQueue
{
    private $queue = [];

    public function count(): int
    {
        return count($this->queue);
    }

    public function queue(Alarm $alarm)
    {
        $this->queue[] = $alarm;
    }

    public function clear()
    {
        $this->queue = [];
    }

    public function next()
    {
        return array_pop($this->queue);
    }
}
