<?php

class Configuration implements ArrayAccess
{
    private $pathname;
    private $pathnameDefault;
    private $config = [];

    public function __construct(string $pathname, ?string $pathnameDefault = null, ?bool $autoload = true)
    {
        $this->pathname = $pathname;
        $this->pathnameDefault = $pathnameDefault;

        if ($autoload) {
            try {
                $this->load();
            } catch (Exception $e) {
                Console::log(sprintf('Unable to load system configuration: %s', $e->getMessage()));
                exit;
            }
        }
    }

    public function load()
    {
        if (!file_exists($this->pathname)) {
            $this->restore();
        }
        if (false === $configuration = file_get_contents($this->pathname)) {
            throw new RuntimeException('Unable to load configuration');
        }
        if (null === $configuration = json_decode($configuration, true)) {
            throw new RuntimeException('Unable to parse configuration');
        }
        $this->config = $configuration;
    }

    public function get() : array
    {
        return $this->config;
    }

    public function restore()
    {
        return $this->save(file_get_contents($this->pathnameDefault));
    }

    /**
     * @throws Exception
     */
    public function save($configuration)
    {
        if (is_string($configuration)) {
            $configuration = json_decode($configuration, true);
        }
        if (!is_array($configuration)) {
            throw new Exception('Unsupported configuration provided');
        }
        return file_put_contents($this->pathname, json_encode($configuration, JSON_PRETTY_PRINT));
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->config);
    }

    public function offsetGet($offset)
    {
        return $this->config[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->config[$offset]);
    }
}