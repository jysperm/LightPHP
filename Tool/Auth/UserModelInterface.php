<?php

namespace LightPHP\Tool\Auth;

interface UserModelInterface
{
    public function byID();

    public function id();

    /** @return \LightPHP\Tool\Auth\TokenModelInterface */
    public function getTokenModel();
} 