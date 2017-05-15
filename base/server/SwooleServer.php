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

namespace base\server;

use core\common\Globals;
use core\component\config\Config;

class SwooleServer extends BaseCallback
{
    public static $rootPath = '';
    /**
     * @var \swoole_websocket_server
     */
    protected $server;

    public function __construct()
    {
        $config = Config::get('swoole');
        $this->server = new \swoole_websocket_server($config['host'], $config['port']);
        $this->server->set($config);

        $this->project_name = Config::getField('project', 'project_name');
        $this->pid_path   = Config::getField('project', 'pid_path');

        $this->server->on('Start', array($this, 'onStart'));
        $this->server->on('Shutdown', array($this, 'onShutdown'));
        $this->server->on('ManagerStart', array($this, 'onManagerStart'));
        $this->server->on('ManagerStop', array($this, 'onManagerStop'));
        $this->server->on('WorkerStart', array($this, 'onWorkerStart'));

        $this->server->on('Finish', function(){});
    }

    public function start()
    {
        $callback = Config::getField('project', 'main_callback');
        if( !class_exists($callback) )
        {
             throw new \Exception("{$callback} not exists");
        }
        $object = new $callback();
        $this->server->on('Request', array($object, 'onRequest'));
        $this->server->on('Message', array($object, 'onMessage'));
        $this->server->on('Task', array($object, 'onTask'));
        $callback = Config::getField('project', 'rpc_callback');
        if( !class_exists($callback) )
        {
            throw new \Exception("{$callback} not exists");
        }
        $rpc = new $callback();
        $rpc->init($this->server);
        $this->server->start();
    }

    public static function run($rootPath)
    {
        SwooleServer::$rootPath = $rootPath;
        $server = new SwooleServer();
        $server->start();
    }
}