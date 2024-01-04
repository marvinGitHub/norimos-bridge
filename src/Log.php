<?php

class Log
{
    const LOG = 'log';
    const DEBUG = 'debug';
    const ERROR = 'error';
    const INFO = 'info';

    private $pathname;

    public function __construct(string $pathname)
    {
        $this->pathname = $pathname;
        $this->init();
    }

    public function init() : void
    {
        if (!file_exists($this->pathname)) {
            touch($this->pathname);
        }
    }

    public function append(string $message) : bool
    {
        return file_put_contents($this->pathname, $message, FILE_APPEND);
    }

    public function print(string $type, string $content) : bool
    {
        $doLog = $type === Log::LOG || $type === Log::ERROR || $type === Log::INFO || $type === Log::DEBUG && $this->verbose === true;

        if (!$doLog) {
            return false;
        }

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

    public function clear()
    {
        unlink($this->pathname);
        $this->init();
    }

    public function load()
    {
        return file_get_contents($this->pathname);
    }
}