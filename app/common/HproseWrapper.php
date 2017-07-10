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


namespace app\common;


class HproseWrapper
{
    private static $magicMethods = array(
        "__construct",
        "__destruct",
        "__call",
        "__callStatic",
        "__get",
        "__set",
        "__isset",
        "__unset",
        "__sleep",
        "__wakeup",
        "__toString",
        "__invoke",
        "__set_state",
        "__clone"
    );

    private $service;
    private $instanceMethods = [];

    public function __construct($service)
    {
        $this->service = $service;
        $this->parseMethods();
    }

    public function __call($name, $arguments)
    {
        $handler = new $this->service();
        return call_user_func_array([$handler, $name], $arguments);
    }

    private function parseMethods()
    {
        $result = get_class_methods($this->service);
        if (($parentClass = get_parent_class($this->service)) !== false) {
            $inherit = get_class_methods($parentClass);
            $result = array_diff($result, $inherit);
        }
        $methods = array_diff($result, self::$magicMethods);
        foreach ($methods as $name) {
            $method = new \ReflectionMethod($this->service, $name);
            if ($method->isPublic() &&
                !$method->isStatic() &&
                !$method->isConstructor() &&
                !$method->isDestructor() &&
                !$method->isAbstract()) {
                $this->instanceMethods[] = $name;
            }
        }
    }

    /**
     * @return array
     */
    public function getInstanceMethods()
    {
        return $this->instanceMethods;
    }
}