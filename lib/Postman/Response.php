<?php

namespace Postman;

class Response
{

  public $status = 200;
  public $headers = array();
  public $response = NULL;

  private $reasons = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Reserved',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            426 => 'Upgrade Required',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            510 => 'Not Extended',
          );



  public function __construct($status = 200, array $headers = array(), $output = '')
  {
    if (is_array($status)) {
      @list($this->status, $this->headers, $this->response) = $status;
    } else {
      $this->status = (int) $status;
      $this->headers = $headers;
      $this->response = $output;
    }
  }

  public function __toString()
  {
    $test = strtoupper(PHP_SAPI);

    if ((strpos($test, 'CLI') === FALSE) OR ($test === 'CLI-SERVER')) {
      if (isset($this->reasons[$this->status])) {
        if (strpos($test, 'CGI') !== FALSE) {
          header("Status: $this->status {$this->reasons[$this->status]}", TRUE);
        } else {
          $protocol = Request::env('SERVER_PROTOCOL');
          header("$protocol $this->status {$this->reasons[$this->status]}", TRUE, $this->status);
        }
      } else {
        // TODO: raise exception
      }

      foreach ((array) $this->headers as $key => $val) {
        header("$key: $val");
      }
    }

    return (string) $this->response;
  }


  public function redirect($to = '/', $status = 302, array $params = array())
  {
    if (is_array($to)) {
      $params = array_merge($to, $params);
    } elseif ( ! isset($params['to'])) {
      $params['to'] = $to;
    }

    if (is_array($status)) {
      $params = array_merge($status, $params);
    } elseif ( ! isset($params['status'])) {
      $params['status'] = (int) $status;
    }


    $params = array_merge(array(
      'headers' => array(),
      'locals'  => array(),
      'status'  => 302,
      'to'      => '/',
    ), $params);


    if ($params['locals']) {
      $params['to'] .= strrpos($params['to'], '?') !== FALSE ? '&' : '?';
      $params['to'] .= http_build_query($params['locals'], NULL, '&');
    }


    $params['headers']['Location'] = str_replace('&amp;', '&', $params['to']);

    $output = array((int) $params['status'], (array) $params['headers'], $this->response);

    return $output;
  }

}
