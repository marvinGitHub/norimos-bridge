<?php

use PhpMqtt\Client\MQTTClient;

class PluginMQTTPublisher extends PluginAbstract
{
    private string $broker;
    private string $topicAlarm;
    private int $retries;
    private int $timeout;
    private int $timeoutAlarm;

    /**
     * @param string $broker
     * @param string $topicAlarm
     * @param int $retries
     * @param int $timeout
     * @param int $timeoutAlarm
     */
    public function __construct(string $broker, string $topicAlarm, int $retries, int $timeout, int $timeoutAlarm)
    {
        $this->setBroker($broker);
        $this->setTopicAlarm($topicAlarm);
        $this->setRetries($retries);
        $this->setTimeout($timeout);
        $this->setTimeoutAlarm($timeoutAlarm);
    }

    /**
     * @param string $broker
     * @return void
     */
    public function setBroker(string $broker)
    {
        if (false === $fragments = parse_url($broker)) {
            throw new InvalidArgumentException(sprintf('Unsupported broker url detected: %s', var_export($broker, true)));
        }
        $this->broker = $fragments;
    }


    /**
     * @return void
     */
    public function run()
    {
        if (0 === $this->getContext()->getAlarmQueue()->count()) {
            return;
        }

        /** @var $alarm Alarm */
        while (null !== $alarm = $this->getContext()->getAlarmQueue()->next()) {
            $retries = $this->getRetries();

            do {
                try {
                    $broker = $this->getBroker();

                    if (!Helper::checkPortAccessibility($host = $broker['host'], $port = (int)$broker['port'], 2)) {
                        throw new RuntimeException(sprintf('Unable to connect to mqtt broker: %s:%u', $host, $port));
                    }

                    $mqtt = new MQTTClient($host, $port, null, null, null, $this->getContext()->getLog());
                    $mqtt->connect($broker['user'], $broker['pass'], null, true);

                    $payload = $alarm->toArray();
                    $payload['title'] = sprintf('Norimos Alarm (%s/%s): %s', $alarm->getChannel(), $alarm->getGroup(), $alarm->getState());

                    $mqtt->publish($this->getTopicAlarm(), json_encode($payload));
                    $this->getContext()->getLog()->print('info', 'Successfully published alarm via mqtt');

                    $mqtt->close();

                    break;
                } catch (Exception $e) {
                    $this->getContext()->getLog()->print(LOG::ERROR, sprintf('%s: failed publishing data', static::class));
                    $this->getContext()->getLog()->print(LOG::ERROR, $e->getMessage());
                    $this->getContext()->getLog()->print(LOG::ERROR, $e->getTraceAsString());
                }
                $this->getContext()->getLog()->print('info', sprintf('Try republishing alarm. %u retries left.', $retries));
                sleep($this->getTimeout());
            } while (--$retries >= 0);

            sleep($this->getTimeoutAlarm());
        }
    }

    /**
     * @return int
     */
    public function getRetries(): int
    {
        return $this->retries;
    }

    /**
     * @param int $retries
     * @return void
     */
    public function setRetries(int $retries)
    {
        $this->retries = $retries;
    }

    /**
     * @return array
     */
    private function getBroker(): array
    {
        return $this->broker;
    }

    /**
     * @return string
     */
    public function getTopicAlarm(): string
    {
        return $this->topicAlarm;
    }

    /**
     * @param string $topic
     * @return void
     */
    public function setTopicAlarm(string $topic)
    {
        $this->topicAlarm = $topic;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $timeout
     * @return void
     */
    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * @return int
     */
    public function getTimeoutAlarm(): int
    {
        return $this->timeoutAlarm;
    }

    /**
     * @param int $timeoutAlarm
     * @return void
     */
    public function setTimeoutAlarm(int $timeoutAlarm)
    {
        $this->timeoutAlarm = $timeoutAlarm;
    }
}