<?php

class AlarmQueue implements QueueMQTT
{
    private array $queue = [];

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

    public function current()
    {
        return current($this->queue);
    }

    public function key()
    {
        return key($this->queue);
    }

    public function valid(): bool
    {
        return true;
    }

    public function rewind()
    {
        reset($this->queue);
    }
}
