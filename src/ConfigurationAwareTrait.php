<?php

trait ConfigurationAwareTrait
{
    protected ?Configuration $configuration;

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }
}