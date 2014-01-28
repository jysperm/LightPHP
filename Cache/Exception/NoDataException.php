<?php

namespace LightPHP\Cache\Exception;

use LightPHP\Core\Exception;

class NoDataException extends Exception
{
    public function __construct()
    {
        parent::__construct("No data");
    }
}
