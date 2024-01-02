<?php

class SerialDeviceConfiguration
{
    private $serialDeviceName;

    /**
     * @param string $serialDeviceName
     */
    public function __construct(string $serialDeviceName)
    {
        $this->serialDeviceName = $serialDeviceName;
    }

    /**
     * @return array|false
     */
    public function findSerialDevices()
    {
        return glob('/dev/tty*');
    }

    /**
     * @return false|string
     */
    public function load()
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        $output = null;

        if (false === exec(sprintf('stty -a -F %s', escapeshellarg($this->serialDeviceName)), $output)) {
            return false;
        }

        return implode(PHP_EOL, $output);
    }

    /**
     * @return bool
     */
    public function serialDeviceAttached(): bool
    {
        return file_exists($this->serialDeviceName);
    }

    public function setBaudrate(int $baudrate): bool
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        $exitCode = Helper::execute($c = sprintf('stty -F %s %u', escapeshellarg($this->serialDeviceName), $baudrate));
        return 0 === $exitCode;
    }

    public function setParity(?string $parity): bool
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        if (null === $parity) {
            $parity = 'none';
        }

        $args = [
            'none' => '-parenb -parodd',
            'odd' => 'parenb parodd',
            'even' => 'parenb -parodd',
        ];

        if (!isset($args[$parity])) {
            return false;
        }

        $exitCode = Helper::execute(sprintf('stty -F %s %s', escapeshellarg($this->serialDeviceName), $args[$parity]));
        return 0 === $exitCode;
    }

    public function setCharacterLength(int $length): bool
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        $exitCode = Helper::execute(sprintf('stty -F %s cs%u', escapeshellarg($this->serialDeviceName), $length));
        return 0 === $exitCode;
    }

    public function setStopBits(int $length): bool
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        $exitCode = Helper::execute(sprintf('stty -F %s %s', escapeshellarg($this->serialDeviceName), escapeshellarg($length === 1 ? '-cstopb' : '')));
        return 0 === $exitCode;
    }

    public function setFlowControl(?string $flowControl): bool
    {
        if (!$this->serialDeviceAttached()) {
            return false;
        }

        if (null === $flowControl) {
            $flowControl = 'none';
        }

        $args = [
            'none' => 'clocal -crtscts -ixon -ixoff',
            'rts/cts' => '-clocal crtscts -ixon -ixoff',
            'xon/xoff' => '-clocal -crtscts ixon ixoff'
        ];

        if (!isset($args[$flowControl])) {
            return false;
        }

        $exitCode = Helper::execute($c = sprintf('stty -F %s %s', escapeshellarg($this->serialDeviceName), $args[$flowControl]));
        return 0 === $exitCode;
    }

    public function allowReceivingInput(bool $toggle): bool
    {
        $exitCode = Helper::execute($c = sprintf('stty -F %s %scread', escapeshellarg($this->serialDeviceName), $toggle ? '' : '-'));
        return 0 === $exitCode;
    }
}
