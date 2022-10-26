<?php

declare(strict_types=1);

namespace Imi\InfluxDB\Test\Meter;

class WorkermanTest extends BaseTest
{
    protected string $registryServiceName = 'http';

    protected static function __startServer(): void
    {
        self::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__, 2) . '/example/bin/imi-workerman', 'workerman/start'], null, [
            'IMI_INFLUXDB_INTERVAL' => 3,
        ]);
        $process->start();
    }
}
