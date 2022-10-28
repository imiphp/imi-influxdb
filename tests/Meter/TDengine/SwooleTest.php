<?php

declare(strict_types=1);

namespace Imi\InfluxDB\Test\Meter\TDengine;

use function Imi\env;
use Imi\InfluxDB\Test\Meter\BaseTest;

class SwooleTest extends BaseTest
{
    protected string $registryServiceName = 'main';

    protected static function __startServer(): void
    {
        self::$process = $process = new \Symfony\Component\Process\Process([\PHP_BINARY, \dirname(__DIR__, 3) . '/example/bin/imi-swoole', 'swoole/start'], null, [
            'IMI_INFLUXDB_CREATE_DATABASE' => false,
            'IMI_INFLUXDB_USERNAME'        => 'root',
            'IMI_INFLUXDB_PASSWORD'        => 'taosdata',
            'IMI_INFLUXDB_PATH'            => '/influxdb/v1/',
            'IMI_INFLUXDB_HOST'            => env('IMI_TDENGINE_HOST', '127.0.0.1'),
            'IMI_INFLUXDB_PORT'            => 6041,
            'IMI_INFLUXDB_INTERVAL'        => 3,
        ]);
        $process->start();
    }

    /**
     * {@inheritDoc}
     */
    public static function setUpBeforeClass(): void
    {
        if (!\extension_loaded('swoole'))
        {
            self::markTestSkipped('no swoole');
        }
        parent::setUpBeforeClass();
    }
}
