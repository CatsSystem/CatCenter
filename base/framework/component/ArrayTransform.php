<?php

namespace App\Utils;

/**
 * Created by PhpStorm.
 * User: skj
 * Date: 2017/6/19
 * Time: 上午11:38
 */
class ArrayTransform {
	private static $instance  = NULL;
	private        $namespace = '';
	private        $tree      = [];

	public static function getInstance() {
		if (!self::$instance) {
			self::$instance = new static;
		}
		return self::$instance;
	}

	/**
	 * 对象转数组
	 * @param $object
	 * @return array
	 */
	public function objectToArr($object) {
		$array   = [];
		$reflect = new \ReflectionObject($object);
		$methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);
		foreach ($methods as $method) {
			$methodName = $method->getName();
			if (strpos($methodName, 'get') === 0) {
				$keyName         = lcfirst(substr($methodName, 3));
				$array[$keyName] = $object->$methodName();
			}
		}
		return $this->getToArrayItem($array);
	}

	private function getToArrayItem($array) {

		foreach ($array as $key => $item) {
			if (is_object($item)) {
				if($item instanceof \Google\Protobuf\Internal\RepeatedField){
					$tmp = [];
					foreach ($item as $v){
						$tmp[] = $v;
					}
					$array[$key] = $tmp;
				}else{
					$array[$key] = $this->objectToArr($item);
				}
			}
			if (is_array($array[$key])) {
				$array[$key] = $this->getToArrayItem($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * 数组转对象
	 * @param        $objectClass
	 * @param string $namespace
	 * @param array  $array
	 * @param array  $tree
	 * @return mixed
	 * @throws \Exception
	 */
	public function arrToObject($namespace, $objectClass, array $array, $tree = []) {
		$this->tree      = $tree;
		$this->namespace = $namespace;
		$responseClass   = $this->namespace . '\\' . $objectClass;
		$response        = new $responseClass();
		foreach ($array as $k => $item) {
			if (is_numeric($k)) {
				throw new \Exception('error array type');
			}
			$func = 'set' . ucwords($k);
			if (method_exists($response, $func)) {
				$response->$func($this->getToObjectItem($item, $k));
			}
		}
		$this->tree      = [];
		$this->namespace = '';
		return $response;
	}

	private function getToObjectItem($item, $keyName = '') {
		if (is_array($item)) {
			foreach ($item as $k => $value) {
				if (is_numeric($k)) {
					if (is_array($value)) {
						$item[$k] = $this->getToObjectItem($value, $keyName);
					}
					return $item;
				} else {
					break;
				}
			}
			if (!isset($this->tree[$keyName])) {
				throw new \Exception("not found [$keyName] class map");
			}
			$className = $this->namespace . '\\' . $this->tree[$keyName];
			$object    = new $className();
			foreach ($item as $k => $value) {
				$func = 'set' . ucwords($k);
				if (method_exists($object, $func)) {
					$object->$func($this->getToObjectItem($value, $keyName . '.' . $k));
				}
			}
			return $object;
		}
		return $item;
	}
}