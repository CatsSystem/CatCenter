<?php
/*******************************************************************************
 *  This file is part of CatCenter.
 *
 *  CatCenter is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  CatCenter is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *******************************************************************************
 * Author: Lidanyang  <simonarthur2012@gmail.com>
 ******************************************************************************/

namespace Utils;

class GrpcTransform {
	private static $instance = NULL;
	private        $tree     = [];

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
				if ($item instanceof \Google\Protobuf\Internal\RepeatedField || $item instanceof \Google\Protobuf\Internal\MapField) {
					$tmp = [];
					foreach ($item as $k => $v) {
						$tmp[$k] = $v;
					}
					$array[$key] = $tmp;
				} else {
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
	 * @param array  $array
	 * @param array  $tree
	 * @return mixed
	 * @throws \Exception
	 */
	public function arrToObject($objectClass, array $array, $tree = []) {
		$this->tree = $tree;
		$response   = new $objectClass();
		foreach ($array as $k => $item) {
			if (is_numeric($k)) {
				throw new \Exception('error array type');
			}
			$func = 'set' . ucwords($k);
			if (method_exists($response, $func)) {
				$val = $this->getToObjectItem($item, $k);
				if (is_array($val) && empty($val)) {
					continue;
				}
				$response->$func($val);
			}
		}
		$this->tree = [];
		return $response;
	}

	private function getToObjectItem($item, $keyName = '') {
		if (is_array($item) && $item) {
			$isArr = FALSE;
			foreach ($item as $k => $value) {
				if (is_numeric($k)) {
					$isArr = TRUE;
				}
				break;
			}
			if ($isArr || !isset($this->tree[$keyName])) {
				foreach ($item as $k => $value) {
					if (is_array($value)) {
						$item[$k] = $this->getToObjectItem($value, $keyName);
					}
				}
				return $item;
			}
			$className = $this->tree[$keyName];
			$object    = new $className();
			foreach ($item as $k => $value) {
				$func = 'set' . ucwords($k);
				if (method_exists($object, $func)) {
					$val = $this->getToObjectItem($value, $keyName . '.' . $k);
					if (is_array($val) && empty($val)) {
						continue;
					}
					$object->$func($val);
				}
			}
			return $object;
		}
		return $item;
	}
}