<?php

trait ConfigurationAwareTrait
{
    protected $configuration;

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
}