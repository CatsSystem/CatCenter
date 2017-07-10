<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  abstract class CenterService {

    public function __construct() {
    }

    /**
     * @param \Proto\Message\GetEtcdAddressRequest $argument input argument
     * @return \Proto\Message\GetEtcdAddressResponse output argument
     **/
    abstract protected function GetEtcdAddress(\Proto\Message\GetEtcdAddressRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runGetEtcdAddress($data) {
      $argument = new \Proto\Message\GetEtcdAddressRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->GetEtcdAddress($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\GetServiceRequest $argument input argument
     * @return \Proto\Message\GetServiceResponse output argument
     **/
    abstract protected function GetService(\Proto\Message\GetServiceRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runGetService($data) {
      $argument = new \Proto\Message\GetServiceRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->GetService($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\ListServiceRequest $argument input argument
     * @return \Proto\Message\ListServiceResponse output argument
     **/
    abstract protected function ListService(\Proto\Message\ListServiceRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runListService($data) {
      $argument = new \Proto\Message\ListServiceRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->ListService($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

  }

}
