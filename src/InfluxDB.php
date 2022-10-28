<?php

declare(strict_types=1);

namespace Imi\InfluxDB;

use Imi\Config;
use Yurun\InfluxDB\ORM\Client\Client;
use Yurun\InfluxDB\ORM\Client\Database;
use Yurun\InfluxDB\ORM\InfluxDBManager;

class InfluxDB
{
    private static bool $inited = false;

    private function __construct()
    {
    }

    /**
     * 获取默认客户端名.
     */
    public static function getDefaultClientName(): ?string
    {
        self::checkInit();

        return InfluxDBManager::getDefaultClientName();
    }

    /**
     * 获取 InfluxDB 客户端.
     */
    public static function getClient(?string $clientName = null): Client
    {
        self::checkInit();

        return InfluxDBManager::getClient($clientName);
    }

    /**
     * 获取 InfluxDB 数据库对象
     */
    public static function getDatabase(?string $databaseName = null, ?string $clientName = null): Database
    {
        self::checkInit();

        return InfluxDBManager::getDatabase($databaseName, $clientName);
    }

    private static function checkInit(): void
    {
        if (!self::$inited)
        {
            self::$inited = true;
            $influxDBConfig = Config::get('@app.influxDB');
            if (isset($influxDBConfig['clients']))
            {
                foreach ($influxDBConfig['clients'] as $name => $config)
                {
                    InfluxDBManager::setClientConfig($name, $config['host'] ?? '127.0.0.1', $config['port'] ?? 8086, $config['username'] ?? '', $config['password'] ?? '', $config['ssl'] ?? false, $config['verifySSL'] ?? false, $config['timeout'] ?? 0, $config['connectTimeout'] ?? 0, $config['defaultDatabase'] ?? '', $config['path'] ?? '/', $config['createDatabase'] ?? true);
                }
            }
            if (isset($influxDBConfig['default']))
            {
                InfluxDBManager::setDefaultClientName($influxDBConfig['default']);
            }
        }
    }
}
