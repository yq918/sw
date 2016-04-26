<?php
namespace SW;

use SW\Store\File;
use Swoole;
use Swoole\Filter;


class UserServer extends Swoole\Protocol\WebSocket
{
    private static $config;
    private static $_redisClient;
    private static $_mysqlClient;
    private static $_sessionClient;
    private static $token = '123456';

    function __construct($config = array())
    {
        //将配置写入config.js
        $config_js = <<<HTML
var webim = {
    'server' : '{$config['server']['url']}'
}
HTML;
        file_put_contents(WEBPATH . '/admin/js/config.js', $config_js);

        //检测日志目录是否存在
        if (isset($config['user']['log_file']) && !empty($config['user']['log_file'])) {
            $log_dir = dirname($config['user']['log_file']);
        }
        if (isset($log_dir) && !is_dir($log_dir)) {
            mkdir($log_dir, 0777, true);
        }
        if (isset($log_dir))
        {
            $logger = new Swoole\Log\FileLog($config['user']['log_file']);
            $this->setLogger($logger);   //Logger
         }

       // $this->setStore(new \SW\Store\File($config['user']['data_dir']));
        $this->origin = $config['server']['origin'];
        parent::__construct($config);

        if( self::getSession() !== NULL)
        {
            self::getSession()->initSess();
        }

        self::$config = $config;
    }



    /**
     * 登录
     * @param $client_id
     * @param $msg
     */
    function cmd_login($client_id, $msg)
    {
        $info['name'] = Filter::escape($msg['name']);
        $info['passwd'] = Filter::escape($msg['passwd']);
        file_put_contents('/zhang/aa.log',var_export($msg,true),FILE_APPEND);

        //用户名或密码为空、提示重新输入
        if(empty($info['name']) || empty($info['passwd']))
        {
            $resMsg = array(
                'cmd' => 'input',
                'fd' => $client_id,
                'data' => '请输入用户名与密码'
            );
            $this->sendJson($client_id, $resMsg);
          //  $this->close($client_id);
            exit;
        }
        //查询用户信息
        $userData =  self::getUserData($info['name'],$info['passwd']);
        if(empty($userData)){
            $resMsg = array(
                'cmd' => 'input',
                'fd' => $client_id,
                'data' => '用户信息错误，没有此用户'
            );
            $this->sendJson($client_id, $resMsg);
           // $this->close($client_id);
            exit;
        }
        session_start();
        $_SESSION['uname'] = $info['name'];
        $_SESSION['user_id'] = $info['user_id'];
        $_SESSION['utoken'] = md5($info['name'].self::$token);

        $redis = self::getRedisInstance();

        if($redis== NULL)
        {
            $resMsg = array(
                'cmd' => 'error',
                'fd' => $client_id,
                'data' => 'redis  connent error'
            );
            $this->sendJson($client_id, $resMsg);
            $this->close($client_id);
            exit;
        }

            $key = md5($_SESSION['uname']);
            $clientinfo =  $redis->get($key);
          if(isset($clientinfo) && !empty($clientinfo)){
            //表示已经有人登录了 回复给登录用户
            $resMsg = array(
                'cmd' => 'login',
                'fd' => $clientinfo,
                'data' => '你的帐号在别的地方登录'
            );
            //将下线消息发送给之前的登录人
            $this->sendJson($clientinfo, $resMsg);
            $this->close($clientinfo);
            exit ;
        }

        $redis -> set($key,$client_id);
        $resMsg = array(
            'cmd' => 'success',
            'fd' => $client_id,
            'data' => '登录成功'
        );
        $this->sendJson($client_id, $resMsg);
    }



    /**
     * 接收到消息时
     * @see WSProtocol::onMessage()
     */
    function onMessage($client_id, $ws)
    {
        $this->log("onMessage #$client_id: " . $ws['message']);
        $msg = json_decode($ws['message'], true);
        if (empty($msg['cmd']))
        {
            $this->sendErrorMessage($client_id, 101, "invalid command");
            return;
        }
        $func = 'cmd_'.$msg['cmd'];
        if (method_exists($this, $func))
        {
            $this->$func($client_id, $msg);
        }
        else
        {
            $this->sendErrorMessage($client_id, 102, "command $func no support.");
            return;
        }
    }

    /**
     * 发送错误信息
     * @param $client_id
     * @param $code
     * @param $msg
     */
    function sendErrorMessage($client_id, $code, $msg)
    {
        $this->sendJson($client_id, array('cmd' => 'error', 'code' => $code, 'msg' => $msg));
    }

    /**
     * 发送JSON数据
     * @param $client_id
     * @param $array
     */
    function sendJson($client_id, $array)
    {
        $msg = json_encode($array);
        if ($this->send($client_id, $msg) === false)
        {
            $this->close($client_id);
        }
    }


    /*  getRedisInstance
     *  实例化 redis
     * */
    public static function getRedisInstance()
    {
        $config = self::$config;
        if(!(self::$_redisClient instanceof swoole_redis))
        {
            try{
               self::$_redisClient = new swoole_redis($config['host'],$config['port']);
            }catch(\Exception $e)
            {
                self::$_redisClient = NULL;
            }
        }
        return self::$_redisClient;
    }

    /*  getMysqlInstance
    *   实例化 mysql
    * */
    public static function getMysqlInstance()
    {
        $config = self::$config;
        if(!(self::$_mysqlClient instanceof Swoole\SelectDB))
        {
            try{
                self::$_mysqlClient = new Swoole\SelectDB($config['dbmaster']);
            }catch(\Exception $e)
            {
                self::$_mysqlClient = NULL;
            }
        }
        return self::$_mysqlClient;
    }


    /*setSession
     * 实例化SESSION
     * */
  public  static  function getSession()
  {

      if(!(self::$_sessionClient instanceof Swoole\Session))
      {
          try{
              self::$_sessionClient = new Swoole\Session('FileCache');
          }catch(\Exception $e)
          {
              self::$_sessionClient = NULL;
          }
      }
      return self::$_sessionClient;
  }

    /*getUserData
     *获取用户信息
     *@return array
     * */
    public static function getUserData($username,$password)
    {
        $apt =  self::getMysqlInstance();
        $apt->from('users');
        $apt->equal('username', $username);
        $apt->equal('password', md5($password));
        $res = $apt->getall();
        return $res;
    }


}