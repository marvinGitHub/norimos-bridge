<?php

class PluginSerialProcessor extends PluginAbstract
{
    public function run()
    {
        $data = $this->getContext()->getSerial()->read();

        if ($data) {
            $this->getContext()->getBuffer()->append($data);

            if ($this->getContext()->getConfiguration()['dump']) {
                $this->getContext()->getDump()->write($data);
            }
        }
    }
}