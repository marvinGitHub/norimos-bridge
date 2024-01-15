<?php

use Psr\Log\LoggerInterface;

class Log implements LoggerInterface
{
    const LOG = 'log';
    const DEBUG = 'debug';
    const ERROR = 'error';
    const INFO = 'info';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';
    const CRITICAL = 'critical';
    const NOTICE = 'notice';

    private $pathname;

    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
        $this->init();
    }

    public function init(): void
    {
        if (!file_exists($this->pathname)) {
            touch($this->pathname);
        }
    }

    public function clear()
    {
        unlink($this->pathname);
        $this->init();
    }

    public function load()
    {
        return file_get_contents($this->pathname);
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = [])
    {
        $this->log(Log::EMERGENCY, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        $this->print($level, $message);
    }

    public function print(string $type, string $content): bool
    {
        switch ($type) {
            case Log::LOG:
                $message = sprintf('%s%s', $content, PHP_EOL);
                break;
            default:
                $message = sprintf('[%u] (%s) %s%s', time(), $type, $content, PHP_EOL);
                break;
        }

        return $this->append($message);
    }

    public function append(string $message): bool
    {
        return file_put_contents($this->pathname, $message, FILE_APPEND);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = [])
    {
        $this->log(Log::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = [])
    {
        $this->log(Log::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = [])
    {
        $this->log(Log::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = [])
    {
        $this->log(Log::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = [])
    {
        $this->log(Log::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = [])
    {
        $this->log(Log::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = [])
    {
        $this->log(Log::DEBUG, $message, $context);
    }
}