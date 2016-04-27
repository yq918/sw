<?php
$config['server'] = array(
    //监听的HOST
    'host'   => '192.168.68.137',
    //监听的端口
    'port'   => '9501',
    //WebSocket的URL地址，供浏览器使用的
    'url'    => 'ws://192.168.68.137:9501',
    //用于Comet跨域，必须设置为html所在的URL
    'origin' => 'http://192.168.68.137:8888',
);

$config['swoole'] = array(
    'log_file'        => __DIR__ . '/log/swoole.log',
    'worker_num'      => 1,
    'max_request'     => 0,
    'task_worker_num' => 1,
    'daemonize'       => 0,
);

$config['user'] = array(
    'data_dir' => __DIR__ . '/data/',
    'log_file' => __DIR__ . '/log/user.log',
);

$config['redis'] = array(
    'host'    => "192.168.68.1",
    'port'    => 6379,
    'password' => '',
    'timeout' => 0.25,
    'pconnect' => false,
//    'database' => 1,
);
$config['dbmaster'] = array(
    'type'       => Swoole\Database::TYPE_MYSQL,
    'host'       => "192.168.68.1",
    'port'       => 3306,
    'dbms'       => 'mysql',
    'engine'     => 'MyISAM',
    'user'       => "root",
    'passwd'     => "",
    'name'       => "test",
    'charset'    => "utf8",
    'setname'    => true,
    'persistent' => false, //MySQL长连接
);

return $config;