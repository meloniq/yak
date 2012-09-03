<?php

error_reporting(E_ALL ^ E_DEPRECATED);
require_once('../yak-standalone.php');

setcookie('billing' . ADDRESS_COOKIE_SUFFIX, '', time() - 3600, '/');
setcookie('shipping' . ADDRESS_COOKIE_SUFFIX, '', time() - 3600, '/');

echo "deleted";

?>