<?php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../h2o.php');

(new H2O\web\Application())->run();
?>