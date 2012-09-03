<?php

namespace Postman;

class Handle
{

  private $klass = NULL;
  private $methods = array();

  // TODO: populate object!


  function __construct($obj)
  {
    $this->klass = $obj;
    $this->methods = get_class_methods($obj);
  }



  public function execute($method, array $arguments = array())
  {
    if (in_array($method, $this->methods)) {
      ob_start();

      $callback = array($this->klass, $method);

      $test = call_user_func_array($callback, $arguments);
      $output = ob_get_clean();

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
        $response = (string) $test;
      }

      return array($status, $headers, $response);
    } else {
      // TODO: raise exception
    }
  }

}
