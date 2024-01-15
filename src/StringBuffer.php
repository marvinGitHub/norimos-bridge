<?php

class StringBuffer
{
    private string $buffer = '';

    public function append(string $data)
    {
        $this->buffer .= $data;
    }

    public function __toString(): string
    {
        return $this->buffer;
    }

    public function toString(): string
    {
        return $this->buffer;
    }

    public function remove(string $sequence)
    {
        $this->buffer = str_replace($sequence, '', $this->buffer);
    }

    public function clear()
    {
        $this->buffer = '';
    }
}