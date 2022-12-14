<?php

declare(strict_types=1);

use function Imi\env;
use Imi\Util\Imi;

return [
    // 项目根命名空间
    'namespace'    => 'app',

    // 配置文件
    'configs'    => [
        'beans'        => __DIR__ . '/beans.php',
    ],

    'ignorePaths' => [
        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'public',
    ],

    // 主服务器配置
    'mainServer'    => [
        'namespace'    => 'app\ApiServer',
        'type'         => \Imi\Swoole\Server\Type::HTTP,
        'host'         => '0.0.0.0',
        'port'         => 8080,
        'configs'      => [
            'worker_num'      => 1,
            'task_worker_num' => 1,
        ],
    ],

    // 子服务器（端口监听）配置
    'subServers'        => [
    ],

    // Workerman 服务器配置
    'workermanServer' => [
        'http' => [
            'namespace'    => 'app\ApiServer',
            'type'         => \Imi\Workerman\Server\Type::HTTP,
            'host'         => '0.0.0.0',
            'port'         => 8080,
            'configs'      => [
            ],
        ],
        'channel' => [
            'namespace'   => '',
            'type'        => \Imi\Workerman\Server\Type::CHANNEL,
            'host'        => '0.0.0.0',
            'port'        => 13005,
            'configs'     => [
            ],
        ],
    ],

    'workerman' => [
        // 多进程通讯组件配置
        'channel' => [
            'host' => '127.0.0.1',
            'port' => 13005,
        ],
    ],

    // fpm 服务器配置
    'fpm' => [
        'serverPath' => \dirname(__DIR__) . '/ApiServer',
    ],

    'imi' => [
        'beans' => Imi::checkAppType('workerman') ? [
            'ServerUtil' => 'ChannelServerUtil',
        ] : [],
    ],

    // 连接池配置
    'pools'    => [
    ],

    'influxDB' => [
        'clients' => [
            'app' => [
                'host'            => env('IMI_INFLUXDB_HOST', '127.0.0.1'),
                'port'            => env('IMI_INFLUXDB_PORT', 8086),
                'path'            => env('IMI_INFLUXDB_PATH', '/'),
                'username'        => env('IMI_INFLUXDB_USERNAME', ''),
                'password'        => env('IMI_INFLUXDB_PASSWORD', ''),
                'defaultDatabase' => 'imi_influxdb',
                'createDatabase'  => env('IMI_INFLUXDB_CREATE_DATABASE', true),
            ],
        ],
        'default' => 'app',
    ],

    // 日志配置
    'logger' => [
        'channels' => [
            'imi' => [
                'handlers' => [
                    [
                        'class'     => \Imi\Log\Handler\ConsoleHandler::class,
                        'formatter' => [
                            'class'     => \Imi\Log\Formatter\ConsoleLineFormatter::class,
                            'construct' => [
                                'format'                     => null,
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                    [
                        'class'     => \Monolog\Handler\RotatingFileHandler::class,
                        'construct' => [
                            'filename' => \dirname(__DIR__) . '/.runtime/logs/log.log',
                        ],
                        'formatter' => [
                            'class'     => \Monolog\Formatter\LineFormatter::class,
                            'construct' => [
                                'dateFormat'                 => 'Y-m-d H:i:s',
                                'allowInlineLineBreaks'      => true,
                                'ignoreEmptyContextAndExtra' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
