<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: message.proto

namespace Proto\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>Proto.Message.OnlineRequest</code>
 */
class OnlineRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>.Proto.Message.Service service = 1;</code>
     */
    private $service = null;

    public function __construct() {
        \GPBMetadata\Message::initOnce();
        parent::__construct();
    }

    /**
     * <code>.Proto.Message.Service service = 1;</code>
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * <code>.Proto.Message.Service service = 1;</code>
     */
    public function setService(&$var)
    {
        GPBUtil::checkMessage($var, \Proto\Message\Service::class);
        $this->service = $var;
    }

}

