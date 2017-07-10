<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Proto\Service {

  abstract class StatService {

    public function __construct() {
    }

    /**
     * @param \Proto\Message\GetServiceRequest $argument input argument
     * @return \Proto\Message\StatResponse output argument
     **/
    abstract protected function Upload(\Proto\Message\GetServiceRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runUpload($data) {
      $argument = new \Proto\Message\GetServiceRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Upload($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\AccessRequest $argument input argument
     * @return \Proto\Message\StatResponse output argument
     **/
    abstract protected function Access(\Proto\Message\AccessRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runAccess($data) {
      $argument = new \Proto\Message\AccessRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Access($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

    /**
     * @param \Proto\Message\LogRequest $argument input argument
     * @return \Proto\Message\StatResponse output argument
     **/
    abstract protected function Log(\Proto\Message\LogRequest $argument);

    /**
     * @param \mixed $data input data
     * @return \string output data
     **/
    public function runLog($data) {
      $argument = new \Proto\Message\LogRequest();
      if (method_exists($argument, 'decode')) {
        $argument->decode($data);
      } else {
        $argument->mergeFromString($data);
      }
      $output = $this->Log($argument);
      if (method_exists($output, 'encode')) {
        return $output->encode();
      } else if(method_exists($output, 'serializeToString')){
        return $output->serializeToString();
      }
      return '';
    }

  }

}
