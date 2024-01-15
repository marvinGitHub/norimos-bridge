<?php

class Configuration implements ArrayAccess
{
    private string $pathname;
    private ?string $pathnameDefault;
    private array $config = [];

    public function __construct(string $pathname, ?string $pathnameDefault = null, ?bool $autoload = true)
    {
        $this->pathname = $pathname;
        $this->pathnameDefault = $pathnameDefault;

        if ($autoload) {
            try {
                $this->load();
            } catch (Exception $e) {
                Console::println(sprintf('Unable to load system configuration: %s', $e->getMessage()));
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

    public function restore(): bool
    {
        try {
            $default = new Configuration($this->pathnameDefault);
            $default->load();
            $this->config = $default->get();
            $this->save();
            return true;
        } catch (Exception $e) {
            Console::println('Unable to restore configuration');
            return false;
        }
    }

    public function get(): array
    {
        return $this->config;
    }

    public function save(): bool
    {
        $config = json_encode($this->config, JSON_PRETTY_PRINT);
        if (false === $config) {
            return false;
        }

        return file_put_contents($this->pathname, $config);
    }

    public function override(array $config)
    {
        $this->config = $config;
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