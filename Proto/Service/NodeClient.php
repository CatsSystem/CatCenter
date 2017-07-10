<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  class NodeClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
      parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Proto\Message\OnlineRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Online(\Proto\Message\OnlineRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Node/Online',
      $argument,
      ['\Proto\Message\OnlineResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\OfflineRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Offline(\Proto\Message\OfflineRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Node/Offline',
      $argument,
      ['\Proto\Message\OfflineResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\StatusRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function Status(\Proto\Message\StatusRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Node/Status',
      $argument,
      ['\Proto\Message\StatusResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\LoadConfigRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function LoadConfig(\Proto\Message\LoadConfigRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Node/LoadConfig',
      $argument,
      ['\Proto\Message\LoadConfigResponse', 'decode'],
      $metadata, $options);
    }

  }

}
