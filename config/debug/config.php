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

return [
    'project'=>array(
        'pid_path'          => __DIR__ . '/../../',
        'project_name'      => 'CatCenter',

        'main_server'       => '\\base\\server\\MainServer',
    ),

    'server' => [
        'host'          => '0.0.0.0',
        'port'          => '8340',
        'socket_type'   => 'http2',
        'enable_ssl'    => false,

        'setting'   => [
            'worker_num'    => 1,
            'dispatch_mode' => 2,

            'daemonize'     => 0,

            'open_http2_protocol' => true
        ]
    ],

    'grpc' => [
        'host'          => '0.0.0.0',
        'port'          => 8341,
        'protocol'      => [

        ]
    ],

    'etcd'  => [
        'hostname' => '172.20.111.172:2379',
    ]
];