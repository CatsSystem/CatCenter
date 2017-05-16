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
 *  along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 *******************************************************************************
 * Author: Lidanyang  <simonarthur2012@gmail.com>
 ******************************************************************************/

namespace base\framework;

use base\middleware\MiddleWareManager;
use core\component\config\Config;

class Route
{
    /**
     * @param Request $request
     * @param Response $response
     * @return mixed|string
     */
    public static function route(Request $request, Response $response)
    {
        try {
            foreach (MiddleWareManager::getInstance() as $middleware)
            {
                $result = yield $middleware->doInvoke($request, $response);
                if($result !== true)
                {
                    return $result;
                }
            }

            $action = Config::getField('project','ctrl_path', 'api') . '\\' . $request->getModule() . '\\' . $request->getCtrl();
            if (!\class_exists($action)) {
                throw new \Exception("no class {$action}");
            }
            $class =  new $action();
            $method = $request->getAction();
            if ( !($class instanceof IController) || !method_exists($class, $method)) {
                throw new \Exception("method error");
            }
            $result = yield $class->before($request, $response);
            if( $result === true ) {
                return yield $class->$method();
            }
            return $result;
        } catch (\Exception $e) {
            return self::handleException($e);
        } catch (\Error $e) {
            return self::handleException($e);
        }
    }

    /**
     * @param $e \Exception | \Error
     * @return array|mixed|string
     */
    private static function handleException($e)
    {
        $result =  \call_user_func('base\exception\ExceptionHandler::exceptionHandler', $e);
        if( !Config::get('debug', true) )
        {
            $result = "Error in Server";
        }
        return $result;
    }
}