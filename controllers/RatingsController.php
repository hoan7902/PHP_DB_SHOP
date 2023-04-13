<?php

require_once("./utils/RestApi.php");
require_once("./utils/JWTHelper.php");
require_once("./utils/HandleUri.php");

class RatingsController extends Controller
{
    private $ratingsModel;
    public function __construct()
    {
        $this->ratingsModel = $this->model("RatingsModel");
    }
}
