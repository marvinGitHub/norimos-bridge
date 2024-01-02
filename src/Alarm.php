<?php

class Alarm
{
    private $channel;
    private $datetime;
    private $group;
    private $state;
    private $message;

    public function __construct(array $params)
    {
        $this->fromArray($params);
    }

    public function fromArray(array $params)
    {
        if (array_key_exists('channel', $params)) {
            $this->setChannel($params['channel']);
        }
        if (array_key_exists('datetime', $params)) {
            $this->setDateTime($params['datetime']);
        }
        if (array_key_exists('group', $params)) {
            $this->setGroup($params['group']);
        }
        if (array_key_exists('state', $params)) {
            $this->setState($params['state']);
        }
        if (array_key_exists('message', $params)) {
            $this->setMessage($params['message']);
        }
    }

    public function toArray(): array
    {
        return [
            'channel' => $this->getChannel(),
            'datetime' => $this->getDateTime(),
            'group' => $this->getGroup(),
            'state' => $this->getState(),
            'message' => $this->getMessage()
        ];
    }

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function setChannel(string $channel)
    {
        $this->channel = $channel;
    }

    public function getDateTime(): string
    {
        return $this->datetime;
    }

    public function setDateTime(string $dateTime)
    {
        $this->datetime = $dateTime;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function setGroup(string $group)
    {
        $this->group = $group;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state)
    {
        $this->state = $state;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }
}