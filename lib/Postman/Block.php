<?php

namespace Postman;

class Block
{

  private $sections = array();


  public function __invoke($section, array $params = array())
  {
    return $this->yield($section, $params);
  }

  public function __toString()
  {
    return join('', $this->all());
  }

  public function clear($name)
  {
    if (isset($this->sections[$name])) {
      unset($this->sections[$name]);
    }
  }

  public function section($name, $content)
  {
    $this->sections[$name] = array($content);
  }

  public function prepend($section, $content)
  {
    isset($this->sections[$section]) && array_unshift($this->sections[$section], $content);
  }

  public function append($section, $content)
  {
    isset($this->sections[$section]) && $this->sections[$section] []= $content;
  }

  public function yield($section, array $params = array())
  {
    if ( ! isset($this->sections[$section])) {
      return; // TODO: raise exception
    }

    $out = '';

    foreach ($this->sections[$section] as $one) {
      if (is_callable($one)) {
        ob_start() && call_user_func($one, $params);
        $one = ob_get_clean();
      }
      $out .= $one;
    }

    return $out;
  }

  public function all(array $params = array())
  {
    $out = array();

    foreach (array_keys($this->sections) as $one) {
      $out[$one] = $this->yield($one, $params);
    }

    return $out;
  }

}
