<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  class StatClient extends \rpc\grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
      parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Proto\Message\GetServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Upload(\Proto\Message\GetServiceRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Stat/Upload',
      $argument,
      ['\Proto\Message\StatResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\AccessRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Access(\Proto\Message\AccessRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Stat/Access',
      $argument,
      ['\Proto\Message\StatResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\LogRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Log(\Proto\Message\LogRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Stat/Log',
      $argument,
      ['\Proto\Message\StatResponse', 'decode'],
      $metadata, $options);
    }

  }

}
