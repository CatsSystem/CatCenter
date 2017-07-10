<?php

namespace base\framework\redis;

use core\component\config\Config;

/**
 * Created by PhpStorm.
 * User: wutong
 * Date: 2017/6/19
 * Time: 下午6:50
 */
class Redis {
	static private $instances = [];
    const PING_INTERVAL = 120;

	private        $config    = [];
	private        $redis     = NULL;
	private        $last_ping = 0;

	/**
	 * 获取Redis从节点实例
	 * @param $node
	 * @return \Redis
	 */
	static function getSlaveInstance($node) {
		if ($_nodes = Config::getSubField('redis', $node, 'slave')) {
			return self::getInstance($node . '.slave', $_nodes[array_rand($_nodes)]);
		} else {
			return self::getMasterInstance($node);
		}
	}

	/**
	 *  获取Redis主节点实例
	 * @param $node
	 * @return \Redis
	 */
	static function getMasterInstance($node) {
		$_node = Config::getSubField('redis', $node, 'master');
		return self::getInstance($node . '.master', $_node);
	}

	/**
	 * @param $name
	 * @param $_node
	 * @return mixed
	 */
	private static function getInstance($name, $_node) {
		if (!isset(self::$instances[$name])) {
			self::$instances[$name] = new self($_node);
		}
		return self::$instances[$name];
	}

	/**
	 * Redis constructor.
	 * @param $_node
	 */
	public function __construct($_node) {
		$this->config = explode(':', $_node);
		$ip           = $this->config[0]??'';
		$port         = $this->config[1]??'';
		$auth         = $this->config[2]??'';
		for ($try = 1; $try <= 3; $try++) {
			try {
				$redis = new \Redis();
				$bool  = $redis->connect($ip, $port);
				if ($bool && $auth) {
					$bool = $redis->auth($auth);
				}
				if ($bool) {
					$this->redis = $redis;
					break;
				}
			} catch (\Throwable $e) {
				Logger::error('ErrRedisConnect', $e->getMessage());
			}
		}
        $this->last_ping = time();
	}

	private function reconnect() {
		$ip   = $this->config[0]??'';
		$port = $this->config[1]??'';
		$auth = $this->config[2]??'';
		$bool = $this->redis->connect($ip, $port);
		if ($bool && $auth) {
			return $this->redis->auth($auth);
		}
		return $bool;
	}

	public function __call($name, $arguments) {
		try {
            $time = time();
            if( $time  >= ($this->last_ping + self::PING_INTERVAL) )
            {
                $pong = $this->redis->ping();
                if( $pong !== 'PONG' && $this->reconnect())
                {
                    $this->last_ping = time();
                }
            }
			return call_user_func_array([$this->redis, $name], $arguments);
		} catch (\Exception $e) {
			if ($this->reconnect()) {
                $this->last_ping = time();
				return call_user_func_array([$this->redis, $name], $arguments);
			}
			Logger::error('ErrorRedis', $e->getMessage());
            return NULL;
		}
	}
}