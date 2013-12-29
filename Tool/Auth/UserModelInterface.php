<?php

namespace LightPHP\Tool\Auth;

interface UserModelInterface {
    public function byID();

    public function id();

    /** @return iTokenModel */
    public function getTokenModel();
} 