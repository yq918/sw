<?php
define('DEBUG', 'on');
define('WEBPATH', __DIR__);

define('SESSION_CACHE','file://localhost#sess');

require __DIR__.'/vendor/autoload.php';

Swoole\Loader::vendor_init();

Swoole\Loader::addNameSpace('SW', __DIR__.'/src/');

$config = require __DIR__.'/config.php';

$webim = new SW\Server($config);
$webim->loadSetting(__DIR__."/swoole.ini"); //加载配置文件


$server = new Swoole\Network\Server($config['server']['host'], $config['server']['port']);
$server->setProtocol($webim);
$server->run($config['swoole']);


exit;


class WebSocket extends Swoole\Protocol\WebSocket
{
    protected $message;

    /**
     * @param     $serv swoole_server
     * @param int $worker_id
     */
    function onStart($serv, $worker_id = 0)
    {
        Swoole::$php->router(array($this, 'router'));
        parent::onStart($serv, $worker_id);
    }

    function router()
    {
        var_dump($this->message);
    }

    /**
     * 进入
     * @param $client_id
     */
    function onEnter($client_id)
    {

    }

    /**
     * 下线时，通知所有人
     */
    function onExit($client_id)
    {
        //将下线消息发送给所有人
        //$this->log("onOffline: " . $client_id);
        //$this->broadcast($client_id, "onOffline: " . $client_id);
    }

    function onMessage_mvc($client_id, $ws)
    {
        $this->log("onMessage: ".$client_id.' = '.$ws['message']);

        $this->message = $ws['message'];
        $response = Swoole::$php->runMVC();

        $this->send($client_id, $response);
        //$this->broadcast($client_id, $ws['message']);
    }

    /**
     * 接收到消息时
     */
    function onMessage($client_id, $ws)
    {
        $this->log("onMessage: ".$client_id.' = '.$ws['message']);
        $this->send($client_id, 'Server: '.$ws['message']);
		//$this->broadcast($client_id, $ws['message']);
    }

    function broadcast($client_id, $msg)
    {
        foreach ($this->connections as $clid => $info)
        {
            if ($client_id != $clid)
            {
                $this->send($clid, $msg);
            }
        }
    }
}

//require __DIR__'/phar://swoole.phar';
Swoole\Config::$debug = true;
Swoole\Error::$echo_html = false;

$AppSvr = new WebSocket();
$AppSvr->loadSetting(__DIR__."/swoole.ini"); //加载配置文件
$AppSvr->setLogger(new \Swoole\Log\EchoLog(true)); //Logger

/**
 * 如果你没有安装swoole扩展，这里还可选择
 * BlockTCP 阻塞的TCP，支持windows平台
 * SelectTCP 使用select做事件循环，支持windows平台
 * EventTCP 使用libevent，需要安装libevent扩展
 */
$enable_ssl = false;
$server = Swoole\Network\Server::autoCreate('0.0.0.0', 9501, $enable_ssl);
$server->setProtocol($AppSvr);
//$server->daemonize(); //作为守护进程
$server->run(array(
    'worker_num' => 1,
    'ssl_key_file' => __DIR__.'/ssl/ssl.key',
    'ssl_cert_file' => __DIR__.'/ssl/ssl.crt',
    //'max_request' => 1000,
    //'ipc_mode' => 2,
    //'heartbeat_check_interval' => 40,
    //'heartbeat_idle_time' => 60,
));
