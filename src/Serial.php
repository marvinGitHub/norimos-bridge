<?php

class Serial
{
    const SERIAL_DEVICE_NOTSET = 0;
    const SERIAL_DEVICE_SET = 1;
    const SERIAL_DEVICE_OPENED = 2;

    public string $device;
    public $handle;
    public int $state;
    public StringBuffer $buffer;

    /**
     * This var says if buffer should be flushed by sendMessage (true) or manually (false)
     *
     * @var bool
     */
    public bool $autoflush = true;

    /**
     * Constructor
     *
     * @throws RuntimeException
     */
    public function __construct()
    {
        $this->state = static::SERIAL_DEVICE_NOTSET;
        $this->buffer = new StringBuffer();

        if (Helper::execute('stty --version') === 0) {
            register_shutdown_function([$this, 'deviceClose']);
        } else {
            throw new RuntimeException('No stty available, unable to run.');
        }
    }

    /**
     * Device set public function : used to set the device name/address.
     * -> linux : use the device address, like /dev/ttyS0
     *
     * @param string $device the name of the device to be used
     * @return bool
     * @throws RuntimeException
     */
    public function deviceSet(string $device): bool
    {
        if ($this->state !== static::SERIAL_DEVICE_OPENED) {
            if (Helper::execute(sprintf('stty -F %s', $device)) === 0) {
                $this->device = $device;
                $this->state = static::SERIAL_DEVICE_SET;
                return true;
            }

            throw new RuntimeException('Specified serial port is not valid');
        }

        throw new RuntimeException('You must close your device before to set an other one');
    }

    /**
     * Opens the device for reading and/or writing.
     *
     * @param string $mode Opening mode : same parameter as fopen()
     * @return bool
     * @throws RuntimeException
     */
    public function deviceOpen(string $mode = 'r+b'): bool
    {
        if ($this->state === static::SERIAL_DEVICE_OPENED) {
            throw new RuntimeException('The device is already opened');
        }

        if ($this->state === static::SERIAL_DEVICE_NOTSET) {
            throw new RuntimeException('The device must be set before to be open');
        }

        if (!preg_match('@^[raw]\+?b?$@', $mode)) {
            throw new RuntimeException(sprintf('Invalid opening mode : %s. Use fopen() modes.', $mode));
        }

        $this->handle = @fopen($this->device, $mode);

        if ($this->handle !== false) {
            stream_set_blocking($this->handle, 0);
            $this->state = static::SERIAL_DEVICE_OPENED;
            return true;
        }

        $this->handle = null;

        throw new RuntimeException('Unable to open the device');
    }

    /**
     * Closes the device
     *
     * @return bool
     * @throws RuntimeException
     */
    public function deviceClose(): bool
    {
        if ($this->state !== static::SERIAL_DEVICE_OPENED) {
            return true;
        }

        if (fclose($this->handle)) {
            $this->handle = null;
            $this->state = static::SERIAL_DEVICE_SET;
            return true;
        }

        throw new RuntimeException('Unable to close the device');
    }

    /**
     * Sends a string to the device
     *
     * @param string $message message to be sent to the device
     * @param int $waitForReply time to wait for the reply (in microseconds)
     */
    public function write(string $message, int $waitForReply = 100)
    {
        $this->buffer->append($message);

        if ($this->autoflush === true) {
            $this->flush();
        }

        usleep($waitForReply);
    }

    /**
     * Flushes the output buffer
     *
     * @return bool
     */
    public function flush(): bool
    {
        if ($this->state !== static::SERIAL_DEVICE_OPENED) {
            return false;
        }

        $result = fwrite($this->handle, (string)$this->buffer);
        $this->buffer->clear();

        if (!$result) {
            throw new RuntimeException('Error while sending message');
        }

        return true;
    }

    /**
     * Reads the port until no new data are available, then return the content.
     *
     * @param int $count number of characters to be read (will stop before
     *  if less characters are in the buffer)
     * @param int $length
     * @return string
     */
    public function read(int $count = 0, int $length = 128): string
    {
        if ($this->state !== static::SERIAL_DEVICE_OPENED) {
            throw new RuntimeException('Device must be opened to read it');
        }

        $buffer = new StringBuffer();
        $i = 0;

        if ($count !== 0) {
            do {
                if ($i > $count) {
                    $buffer->append(fread($this->handle, ($count - $i)));
                } else {
                    $buffer->append(fread($this->handle, $length));
                }
            } while (($i += $length) === strlen((string)$buffer));
        } else {
            do {
                $buffer->append(fread($this->handle, $length));
            } while (($i += $length) === strlen((string)$buffer));
        }

        return (string)$buffer;
    }

    public function getState(): int
    {
        return $this->state;
    }
}
