<?php

use PhpMqtt\Client\MQTTClient;
use PhpMqtt\Client\ConnectionSettings;

abstract class PluginMQTTPublisherAbstract extends PluginAbstract
{
    private string $broker;
    private int $retries;
    private int $timeoutPerRetry;
    private int $timeoutPerElement;
    private int $timeoutSocket;

    /**
     * @param string $broker
     * @param int $retries
     * @param int $timeoutPerRetry
     * @param int $timeoutPerElement
     * @param int $timeoutSocket
     */
    public function __construct(string $broker, int $retries, int $timeoutPerRetry, int $timeoutPerElement, int $timeoutSocket)
    {
        $this->setBroker($broker);
        $this->setRetries($retries);
        $this->setTimeoutPerRetry($timeoutPerRetry);
        $this->setTimeoutPerElement($timeoutPerElement);
        $this->setTimeoutSocket($timeoutSocket);
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


        $mqtt = new MQTTClient($host = $broker['host'], $port = (int)$broker['port'], null, null, null, $this->getContext()->getLog());
        $settings = new ConnectionSettings(0, false, false, $this->getTimeoutSocket());

        while (null !== $element = $this->getQueue()->next()) {
            $retries = $this->getRetries();

            do {
                try {
                    if (!Helper::checkPortAccessibility($host, $port, 2)) {
                        throw new RuntimeException(sprintf('Unable to connect to mqtt broker: %s:%u', $host, $port));
                    }

                    $mqtt->connect($broker['user'], $broker['pass'], $settings, true);
                    $this->publish($mqtt, $element);
                    $mqtt->close();

                    break;
                } catch (Exception $e) {
                    $this->getContext()->getLog()->print(LOG::ERROR, sprintf('%s: failed publishing data', static::class));
                    $this->getContext()->getLog()->print(LOG::ERROR, $e->getMessage());
                    $this->getContext()->getLog()->print(LOG::ERROR, $e->getTraceAsString());
                }
                $this->getContext()->getLog()->print('info', sprintf('Try republishing alarm. %u retries left.', $retries));
                usleep($this->getTimeoutPerRetry());
            } while (--$retries >= 0);

            usleep($this->getTimeoutPerElement());
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

    /**
     * @param MQTTClient $client
     * @param mixed $element
     * @return void
     */
    abstract public function publish(MQTTClient $client, $element): void;

    /**
     * Get timeout per retry in microseconds
     *
     * @return int
     */
    public function getTimeoutPerRetry(): int
    {
        return $this->timeoutPerRetry;
    }

    /**
     * Set timeout per retry in microseconds
     *
     * @param int $timeoutPerRetry
     * @return void
     */
    public function setTimeoutPerRetry(int $timeoutPerRetry)
    {
        $this->timeoutPerRetry = $timeoutPerRetry;
    }

    /**
     * Get timeout per element in microseconds
     *
     * @return int
     */
    public function getTimeoutPerElement(): int
    {
        return $this->timeoutPerElement;
    }

    /**
     * Set timeout per element in microseconds
     *
     * @param int $timeoutPerElement
     * @return void
     */
    public function setTimeoutPerElement(int $timeoutPerElement)
    {
        $this->timeoutPerElement = $timeoutPerElement;
    }

    /**
     * Set timeout for socket in seconds
     *
     * @param int $timeoutSocket
     * @return void
     */
    public function setTimeoutSocket(int $timeoutSocket)
    {
        $this->timeoutSocket = $timeoutSocket;
    }

    /**
     * @return int
     */
    public function getTimeoutSocket(): int
    {
        return $this->timeoutSocket;
    }
}