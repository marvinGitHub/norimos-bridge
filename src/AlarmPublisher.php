<?php

use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\MQTTClient;

class AlarmPublisher extends PluginMQTTPublisherAbstract
{
    private string $topic;

    public function getQueue(): QueueMQTT
    {
        return $this->getContext()->getAlarmQueue();
    }

    /**
     * @param MQTTClient $client
     * @param Alarm $element
     * @return void
     * @throws DataTransferException
     */
    public function publish(MQTTClient $client, $element)
    {
        $payload = $element->toArray();
        $payload['title'] = sprintf('Norimos Alarm (%s/%s): %s', $element->getChannel(), $element->getGroup(), $element->getState());

        $client->publish($this->getTopic(), json_encode($payload));
        $this->getContext()->getLog()->print('info', 'Successfully published alarm via mqtt');
    }

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     * @return void
     */
    public function setTopic(string $topic)
    {
        $this->topic = $topic;
    }
}