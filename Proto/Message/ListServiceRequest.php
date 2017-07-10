<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: message.proto

namespace Proto\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>Proto.Message.ListServiceRequest</code>
 */
class ListServiceRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>string name = 1;</code>
     */
    private $name = '';
    /**
     * <code>string host = 2;</code>
     */
    private $host = '';
    /**
     * <code>uint32 port = 3;</code>
     */
    private $port = 0;
    /**
     * <code>uint32 type = 4;</code>
     */
    private $type = 0;

    public function __construct() {
        \GPBMetadata\Message::initOnce();
        parent::__construct();
    }

    /**
     * <code>string name = 1;</code>
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * <code>string name = 1;</code>
     */
    public function setName($var)
    {
        GPBUtil::checkString($var, True);
        $this->name = $var;
    }

    /**
     * <code>string host = 2;</code>
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * <code>string host = 2;</code>
     */
    public function setHost($var)
    {
        GPBUtil::checkString($var, True);
        $this->host = $var;
    }

    /**
     * <code>uint32 port = 3;</code>
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * <code>uint32 port = 3;</code>
     */
    public function setPort($var)
    {
        GPBUtil::checkUint32($var);
        $this->port = $var;
    }

    /**
     * <code>uint32 type = 4;</code>
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * <code>uint32 type = 4;</code>
     */
    public function setType($var)
    {
        GPBUtil::checkUint32($var);
        $this->type = $var;
    }

}

