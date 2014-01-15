<?php

namespace LightPHP\Tool\Auth;

use LightPHP\Tool\Auth\TokenModelInterface;

interface UserModelInterface
{
    public function byID();
    public function id();

    /** @return TokenModelInterface */
    public function getTokenModel();
} 