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



  public function responds($to, $data, array $params = array())
  {
    if ( ! empty($this->callbacks[$to])) {
      $lambda = $this->callbacks[$to]['lambda'];
      $params = $this->callbacks[$to]['params'] + $params;

      return call_user_func($lambda, $data, $params);
    }
    // TODO: raise exception
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
    if (in_array($method, $this->methods)) {
      ob_start();

      $callback = array($this->klass, $method);

      $test = call_user_func_array($callback, $arguments);
      $output = ob_get_clean();

      if ($tmp = $this->responds($this->type, $test, compact('arguments'))) {
        @list($status, $headers, $response) = $tmp;
      } else {
        $status = 200;
        $headers = array();
        $response = $output;

        if (is_array($test)) {
          if (is_string(key($test))) {
            $headers = $test;
          } else {
            $status = array_shift($test) ?: $status;
            $headers = array_shift($test) ?: $headers;
            $response = array_shift($test) ?: $response;
          }
        } elseif (is_numeric($test)) {
          $status = (int) $test;
        } else {
          $response = (string) ($test ?: $output);
        }
      }

      return array($status, $headers, $response);
    } else {
      // TODO: raise exception
    }
  }

}
