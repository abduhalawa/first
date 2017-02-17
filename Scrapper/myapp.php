<?php
header('Content-type: text/plain');
require_once('lib1.php');

echo \App\Lib1\MYCONST . "\n";
echo \App\Lib1\MyFunction() . "\n";
echo \App\Lib1\MyClass::WhoAmI() . "\n";
?>