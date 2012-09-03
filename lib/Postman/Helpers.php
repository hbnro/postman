<?php

namespace Postman;

class Helpers
{

  static function fetch($from, $that = NULL, $or = FALSE)
  {
    if (is_scalar($from)) {
      return $or;
    } elseif (preg_match_all('/\[([^\[\]]*)\]/U', $that, $matches) OR ($matches[1] = explode('.', $that))) {
      // TODO: there is a previous bug when the first argument has only 1 level?
      $key = ($offset = strpos($that, '[')) > 0 ? substr($that, 0, $offset) : '';

      if ( ! empty($key)) {
        array_unshift($matches[1], $key);
      }

      $key   = array_shift($matches[1]);
      $get   = join('.', $matches[1]);
      $depth = sizeof($matches[1]);

      if (is_object($from) && isset($from->$key)) {
        $tmp = $from->$key;
      } elseif (is_array($from) && isset($from[$key])) {
        $tmp = $from[$key];
      } else {
        $tmp = $or;
      }

      $value = ! $depth ? $tmp : static::fetch($tmp, $get, $or);

      return $value;
    }
  }

}
