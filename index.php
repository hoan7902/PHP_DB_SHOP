<?php
session_start();
require_once("./config/CorsConfig.php");
require_once("./utils/Cors.php");
require_once("./middlewares/Bridge.php");

$cors = new Cors(CORS_ORIGIN, CORS_METHOD, CORS_HEADER);
$cors->handlePreflight();
$cors->handleRequest();
$routes = new Routes();
