<?php

class HandleUri
{
    public function sliceUri()
    {
        if (isset($_GET['uri'])) {
            return explode('/', trim($_GET['uri']));
        }
        return null;
    }
}
