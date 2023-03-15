<?php

class HandleUri
{
    static public function sliceUri()
    {
        if (isset($_GET['uri'])) {
            return explode('/', trim($_GET['uri']));
        }
        return null;
    }
}
