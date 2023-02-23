<?php

class HandleUri
{
    public function SliceUri()
    {
        if (isset($_GET['uri'])) {
            return explode('/', trim($_GET['uri']));
        }
        return null;
    }
}
