<?php

class PluginSerialProcessor extends PluginAbstract
{
    public function run(PluginContext $context)
    {
        $data = $context->getSerial()->read();

        if ($data) {
            $context->getBuffer()->append($data);
            $context->getDump()->write($data);
        }
    }
}