<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/4/11
 * Time: 下午4:17
 */

namespace redis;

use ZPHP\Core\Config;

class Redis
{
    const PING_INTERVAL = 120;

    private static $instance = null;

    /**
     * @return \redis\Redis
     *
     */
    public static function getInstance()
    {
        if(self::$instance == null )
        {
            self::$instance = new Redis(Config::get('redis'));
        }
        return self::$instance;
    }

    /**
     * @var \Redis;
     */
    private $conn;
    private $config;
    private $last_ping;

    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }

    public function connect()
    {
        $timeOut = 0;
        if(isset($this->config['timeout'])) {
            $timeOut = $this->config['timeout'];
        }

        $this->conn = new \Redis();
        $this->conn->connect($this->config['host'], $this->config['port'], $timeOut);
        $this->conn->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
        $this->last_ping = time();
    }

    /**
     * @return \Redis
     */
    public function getConnection()
    {
        $time = time();
        if( $time  >= ($this->last_ping + self::PING_INTERVAL) )
        {
            try{
                $pong = $this->conn->ping();
                if( $pong !== 'PONG' )
                {
                    $this->connect();
                }
                $this->last_ping = $time;
            }catch (\Exception $e) {
                $this->connect();
            }
        }
        return $this->conn;
    }

    public function close()
    {
        $this->conn->close();
    }

}