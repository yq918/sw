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
    //不要修改这里
    'max_request'     => 0,
    'task_worker_num' => 1,
    //是否要作为守护进程
    'daemonize'       => 0,
);

$config['user'] = array(
    //聊天记录存储的目录
    'data_dir' => __DIR__ . '/data/',
    'log_file' => __DIR__ . '/log/user.log',
);
return $config;