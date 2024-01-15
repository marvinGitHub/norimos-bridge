<?php

abstract class PluginAbstract
{
    private ?PluginContext $context;

    public function getContext(): ?PluginContext
    {
        return $this->context;
    }

    public function setContext(PluginContext $context)
    {
        $this->context = $context;
    }

    abstract public function run();


}