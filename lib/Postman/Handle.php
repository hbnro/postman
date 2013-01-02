<?php

namespace Postman;

class Handle
{

  private $type = '';
  private $klass = NULL;
  private $methods = array();
  private $callbacks = array();


  function __construct($bundle, $type = '')
  {
    $this->type = $type;
    $this->klass = $bundle;
    $this->methods = get_class_methods($bundle);
  }



  public function responds($data, array $params = array())
  {
    if ( ! empty($this->callbacks[$this->type])) {
      $lambda = $this->callbacks[$this->type]['lambda'];
      $params = $this->callbacks[$this->type]['params'] + $params;

      return call_user_func($lambda, $data, $params);
    }
  }

  public function register($type, \Closure $lambda, array $params = array())
  {
    if (is_array($type)) {
      foreach ($type as $one) {
        $this->response($one, $lambda, $params);
      }
    } else {
      $this->callbacks[$type] = compact('lambda', 'params');
    }
  }

  public function execute($method, array $arguments = array())
  {
    $klass = get_class($this->klass);

    if (in_array($method, $this->methods)) {
      ob_start();

      $callback = array($this->klass, $method);

      $test = call_user_func_array($callback, $arguments);
      $output = ob_get_clean();

      // $output always become an string!
      $response = $output;
      $headers = array();
      $status = 200;


      if (is_numeric($test)) {
        $status = (int) $test;
      } elseif (is_array($test)) {
        if ( ! is_numeric(key($test))) {
          $response = $test;
        } else {
          $status = array_shift($test) ?: $status;
          $headers = array_shift($test) ?: $headers;
          $response = array_shift($test) ?: $response;
        }
      } else {
        $response = $output ?: $test;
      }

      return array($status, $headers, $response);
    } else {
      throw new \Exception("Unknown '$klass::$method' handler");
    }
  }

  public function exists($method)
  {
    return in_array($method, $this->methods);
  }

}
