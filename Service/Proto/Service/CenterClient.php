<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  class CenterClient extends \rpc\grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
      parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Proto\Message\GetEtcdAddressRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetEtcdAddress(\Proto\Message\GetEtcdAddressRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Center/GetEtcdAddress',
      $argument,
      ['\Proto\Message\GetEtcdAddressResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\GetServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetService(\Proto\Message\GetServiceRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Center/GetService',
      $argument,
      ['\Proto\Message\GetServiceResponse', 'decode'],
      $metadata, $options);
    }

    /**
     * @param \Proto\Message\ListServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function ListService(\Proto\Message\ListServiceRequest $argument,
      $metadata = [], $options = []) {
      return $this->_simpleRequest('/Proto.Service.Center/ListService',
      $argument,
      ['\Proto\Message\ListServiceResponse', 'decode'],
      $metadata, $options);
    }

  }

}
