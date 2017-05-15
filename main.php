<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-11-27
 * Time: 下午8:44
 */

use base\server\SwooleServer;
use core\component\config\Config;

require "vendor/autoload.php";

global $debug;
Config::load(__DIR__ . "/config/{$debug}/");
SwooleServer::run(__DIR__);