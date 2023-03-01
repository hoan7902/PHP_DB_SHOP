<?php

class Routes
{
    public function __construct()
    {
        $this->urlProccess();
    }

    private function urlProccess()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        require_once("./routes/RoutesDefine.php");
        $check = false;
        foreach ($routes as $route) {
            if ($route['method'] == $method && $this->matchUri($route['regex'])) {
                $check = true;
                require_once("./controllers/" . $route['controller'] . '.php');
                $cont = new $route['controller']();
                $cont->{$route['action']}();
            }
        }
        if (!$check) {
            require_once('./middlewares/ErrorMiddleware.php');
            $errorMiddleware = new ErrorMiddleware('NotFound');
        }
    }

    private function matchUri($regex)
    {
        $uri = isset($_GET['uri']) ? trim($_GET['uri']) : null;
        return preg_match($regex, $uri);
    }
}
