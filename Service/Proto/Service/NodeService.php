<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  abstract class NodeService {

    public function __construct() {
    }

    /**
     * @param \Proto\Message\OnlineRequest $argument input argument
     * @return \Proto\Message\OnlineResponse output argument
     **/
    abstract protected function Online(\Proto\Message\OnlineRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runOnline($data) {
      $argument = new \Proto\Message\OnlineRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Online($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\OfflineRequest $argument input argument
     * @return \Proto\Message\OfflineResponse output argument
     **/
    abstract protected function Offline(\Proto\Message\OfflineRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runOffline($data) {
      $argument = new \Proto\Message\OfflineRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Offline($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\StatusRequest $argument input argument
     * @return \Proto\Message\StatusResponse output argument
     **/
    abstract protected function Status(\Proto\Message\StatusRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runStatus($data) {
      $argument = new \Proto\Message\StatusRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Status($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\LoadConfigRequest $argument input argument
     * @return \Proto\Message\LoadConfigResponse output argument
     **/
    abstract protected function LoadConfig(\Proto\Message\LoadConfigRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runLoadConfig($data) {
      $argument = new \Proto\Message\LoadConfigRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->LoadConfig($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

  }

}
