<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: message.proto

namespace Proto\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>Proto.Message.StatusRequest</code>
 */
class StatusRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>uint64 id = 1;</code>
     */
    private $id = 0;
    /**
     * <code>uint32 status = 2;</code>
     */
    private $status = 0;

    public function __construct() {
        \GPBMetadata\Message::initOnce();
        parent::__construct();
    }

    /**
     * <code>uint64 id = 1;</code>
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * <code>uint64 id = 1;</code>
     */
    public function setId($var)
    {
        GPBUtil::checkUint64($var);
        $this->id = $var;
    }

    /**
     * <code>uint32 status = 2;</code>
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * <code>uint32 status = 2;</code>
     */
    public function setStatus($var)
    {
        GPBUtil::checkUint32($var);
        $this->status = $var;
    }

}

