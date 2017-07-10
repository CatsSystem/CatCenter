<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: message.proto

namespace Proto\Message;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Protobuf type <code>Proto.Message.UploadRequest</code>
 */
class UploadRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * <code>uint64 id = 1;</code>
     */
    private $id = 0;
    /**
     * <code>string data = 2;</code>
     */
    private $data = '';

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
     * <code>string data = 2;</code>
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * <code>string data = 2;</code>
     */
    public function setData($var)
    {
        GPBUtil::checkString($var, True);
        $this->data = $var;
    }

}

