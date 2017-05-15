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

use app\common\HproseWrapper;
use base\server\SwooleServer;
use Hprose\Swoole\Socket\Service as SocketService;
use core\component\config\Config;

class RPCServer
{
    protected $service;

    public function init(\swoole_server $swoole_server)
    {
        $config = Config::get('rpc');
        $port = $swoole_server->listen($config['host'], $config['port'], SWOOLE_SOCK_TCP);
        $config['protocol']['open_eof_check'] = false;
        $port->set($config['protocol']);

        $this->service = new SocketService();
        $this->service->socketHandle($port);
        $this->service->errorTypes = E_ALL;

        $files = new \DirectoryIterator(SwooleServer::$rootPath . '/app/rpc');
        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }
            $filename = $file->getFilename();
            if ($filename[0] === '.' || !strpos($filename, ".php" )) {
                continue;
            }
            $filename = str_replace(".php", "", $filename);
            $handler = new HproseWrapper("\\app\\rpc\\" . $filename);
            $this->service->addMethods($handler->getInstanceMethods(), $handler);
        }

    }
}