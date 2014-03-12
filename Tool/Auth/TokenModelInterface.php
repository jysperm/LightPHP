<?php

namespace LightPHP\Tool\Auth;

interface TokenModelInterface
{
    public function newToken();

    /** @return TokenModelInterface */
    public function byToken();

    public function remove();

    public function userID();

    public function renew();

    public function isValid();
} 