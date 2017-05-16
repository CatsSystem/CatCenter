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

namespace app\server;

class MainServer
{
    public function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        $path_info = $request->server['path_info'];

    }

    public function onMessage()
    {

    }

    public function onTask(\swoole_server $serv, $task_id, $src_worker_id, $data)
    {
        $task = $data['task'];
        $action = $data['action'];
        $params = $data['params'];

        $class_name = "\\app\\task\\{$task}";
        $object = new $class_name();
        return call_user_func_array([$object, $action], $params);
    }
}