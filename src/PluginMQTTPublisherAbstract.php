<?php

use PhpMqtt\Client\MQTTClient;

abstract class PluginMQTTPublisherAbstract extends PluginAbstract
{
    private string $broker;

    private int $retries;
    private int $timeout;
    private int $timeoutPerElement;

    /**
     * @param string $broker
     * @param int $retries
     * @param int $timeout
     * @param int $timeoutPerElement
     */
    public function __construct(string $broker, int $retries, int $timeout, int $timeoutPerElement)
    {
        $this->setBroker($broker);
        $this->setRetries($retries);
        $this->setTimeout($timeout);
        $this->setTimeoutPerElement($timeoutPerElement);
    }

    /**
     * @param string $broker
     * @return void
     */
    public function setBroker(string $broker)
    {
        $this->broker = $broker;
    }

    /**
     * @return void
     */
    public function run()
    {
        if (0 === $this->getQueue()->count()) {
            return;
        }

        if (false === $broker = parse_url($this->getBroker())) {
            throw new InvalidArgumentException(sprintf('Unsupported broker url detected: %s', var_export($broker, true)));
        }

        while (null !== $element = $this->getQueue()->next()) {
            $retries = $this->getRetries();

            do {
                try {
                    if (!Helper::checkPortAccessibility($host = $broker['host'], $port = (int)$broker['port'], 2)) {
                        throw new RuntimeException(sprintf('Unable to connect to mqtt broker: %s:%u', $host, $port));
                    }

                    $mqtt = new MQTTClient($host, $port);
                    $mqtt->connect($broker['user'], $broker['pass'], null, true);

                    $this->publish($mqtt, $element);

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

            sleep($this->getTimeoutPerElement());
        }
    }

    abstract public function getQueue(): QueueMQTT;

    /**
     * @return string
     */
    private function getBroker(): string
    {
        return $this->broker;
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

    abstract public function publish(MQTTClient $client, $element);

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
    public function getTimeoutPerElement(): int
    {
        return $this->timeoutPerElement;
    }

    /**
     * @param int $timeoutPerElement
     * @return void
     */
    public function setTimeoutPerElement(int $timeoutPerElement)
    {
        $this->timeoutPerElement = $timeoutPerElement;
    }
}