<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: message.proto

namespace Proto\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>Proto.Message.ListServiceResponse</code>
 */
class ListServiceResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>.Proto.Message.ResponseHeader header = 1;</code>
     */
    private $header = null;
    /**
     * <code>uint32 count = 2;</code>
     */
    private $count = 0;
    /**
     * <code>repeated .Proto.Message.Service list = 3;</code>
     */
    private $list;

    public function __construct() {
        \GPBMetadata\Message::initOnce();
        parent::__construct();
    }

    /**
     * <code>.Proto.Message.ResponseHeader header = 1;</code>
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * <code>.Proto.Message.ResponseHeader header = 1;</code>
     */
    public function setHeader(&$var)
    {
        GPBUtil::checkMessage($var, \Proto\Message\ResponseHeader::class);
        $this->header = $var;
    }

    /**
     * <code>uint32 count = 2;</code>
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * <code>uint32 count = 2;</code>
     */
    public function setCount($var)
    {
        GPBUtil::checkUint32($var);
        $this->count = $var;
    }

    /**
     * <code>repeated .Proto.Message.Service list = 3;</code>
     */
    public function getList()
    {
        return $this->list;
    }

    /**
     * <code>repeated .Proto.Message.Service list = 3;</code>
     */
    public function setList(&$var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, \Proto\Message\Service::class);
        $this->list = $arr;
    }

}

