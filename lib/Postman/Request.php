<?php

namespace Postman;

class Request
{

  private static $headers = array();


  public static function headers()
  {
    if ( ! static::$headers) {
      foreach ($_SERVER as $key => $val) {
        if (substr($key, 0, 5) === 'HTTP_') {
          $key = strtolower(substr($key, 5));
          $key = ucwords(strtr($key, '_', ' '));

          static::$headers[$key] = strtr($val, ' ', '-');
        }
      }
    }
    return static::$headers;
  }

  public static function header($name, $default = FALSE)
  {
    if ($set = static::headers()) {
      return ! empty($set[$name]) ? $set[$name] : $default;
    }
    return $default;
  }

  public static function host($path = '/', $complete = TRUE)
  {
    $host = '';

    if ($complete === TRUE) {
      $test  = explode('/', static::env('SERVER_PROTOCOL'));
      $port  = static::env('SERVER_PORT');

      $host .= strtolower(array_shift($test));
      $host .= static::is_secure() ? 's' : '';

      $host .= '://' . static::env('SERVER_NAME');
      $host .= $port <> 80 ? ":$port" : '';
    } else {
      $host .= (string) $complete;
    }

    return "$host$path";
  }

  public static function env($key, $default = FALSE)
  {
    return ! empty($_SERVER[$key]) ? $_SERVER[$key] : $default;
  }

  public static function data()
  {
    $out = (string) @file_get_contents('php://input');

    if (static::header('Content-Type') === 'application/x-www-form-urlencoded') {
      parse_str($out, $out);
    }
    return $out;
  }

  public static function value($key, $default = FALSE)
  {
    return Helpers::fetch($_POST, $key, Helpers::fetch($_GET, $key, $default));
  }

  public static function address()
  {
    return is_callable('gethostbyaddr') ? gethostbyaddr(static::ip()) : static::ip();
  }

  public static function port()
  {
    return (int) static::env('REMOTE_PORT');
  }

  public static function agent()
  {
    return static::env('HTTP_USER_AGENT');
  }

  public static function method()
  {
    return static::env('REQUEST_METHOD');
  }

  public static function referer($or = FALSE)
  {
    return static::env('HTTP_REFERER', $or);
  }

  public static function ip($or = FALSE)
  {
    return static::env('HTTP_X_FORWARDED_FOR', static::env('HTTP_CLIENT_IP', static::env('REMOTE_ADDR', $or)));
  }

  public static function is_local($test = NULL)
  {
    static $regex = '/^(::|127\.|192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[01])\.|localhost)/';

    if (strpos($test, '://') !== FALSE) {
      $host = static::env('HTTP_HOST');
      $test = parse_url($test);

      if (isset($test['host']) && ($test['host'] !== $host)) {
        return FALSE;
      }
      return TRUE;
    }

    return preg_match($regex, $test ?: static::env('REMOTE_ADDR')) > 0;
  }

  public static function is_xhr()
  {
    return static::env('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest';
  }

  public static function is_post()
  {
    return static::method() === POST;
  }

  public static function is_get()
  {
    return static::method() === GET;
  }

  public static function is_put()
  {
    return static::method() === PUT;
  }

  public static function is_delete()
  {
    return static::method() === DELETE;
  }

  public static function is_upload($key = NULL)
  {
    if (func_num_args() == 0) {
      return sizeof($_FILES) > 0;
    }


    $test = Helpers::fetch($_FILES, $key);

    if ( ! empty($test['name'][0]) && $test['error'][0] == 0) {
      return TRUE;
    } elseif (is_array($test) && $test['error'] == 0) {
      return TRUE;
    }

    return FALSE;
  }

  public static function is_secure()
  {
    if ($test = static::env('HTTPS')) {
      if (strtolower($test) === 'on') {
        return TRUE;
      } elseif ((int) $test > 0) {
        return TRUE;
      }
    } elseif (static::port() == 443) {
      return TRUE;
    }
    return FALSE;
  }

}
