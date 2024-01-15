<?php

class Helper
{
    public static function checkPortAccessibility(string $host, int $port, int $timeout = 1): bool
    {
        $result = false;

        if ($pf = @fsockopen($host, $port, $err, $err_string, $timeout)) {
            $result = true;
            fclose($pf);
        }

        return $result;
    }

    public static function execute($cmd, &$stdout = null, &$stderr = null): int
    {
        $desc = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];

        $proc = proc_open($cmd, $desc, $pipes);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($proc);
    }
}