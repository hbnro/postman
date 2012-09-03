<?php

require dirname(__DIR__).DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';

class my_app
{

  function foo()
  {
    echo 'this will not shown';
    return array(200, array(), 'xD');
  }

  function bar()
  {
    echo 'candy';
  }

  function candy()
  {
    return array(302, array('Location' => 'http://google.com/'));
  }

}

$app = new Postman\Handle(new my_app);
$out = new Postman\Response($app->execute('foo'));

echo "\Handle/Response\n";
var_dump($app);
var_dump($out);

echo "\nFinal output\n";
echo $out;
