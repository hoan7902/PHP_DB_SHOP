<?php
require_once('./utils/preflight.php');
session_start();
require_once "./middlewares/Bridge.php";
$routes = new Routes();
