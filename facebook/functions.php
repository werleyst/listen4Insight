<?php

require_once 'codes.php';
require_once 'vendor/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => $app_id,
  'app_secret' => $app_secret,
  'default_graph_version' => 'v2.5',
]);




?>