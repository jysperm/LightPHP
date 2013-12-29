<?php

/* 初始化选项 -- */

// 令 LightPHP 接管错误处理
const lpErrorHandling = true;

// 令 LightPHP 包装超全局变量
const lpWrapSuperGlobals = true;

// 最低支持的 PHP 版本
const lpMinPHPVersion = "5.4.0";

/* -- 初始化选项 */

const lpInLightPHP = true;

const lpDebug = 3;
const lpDeploy = 2;
const lpDefault = 1;
const lpProduction = 0;

spl_autoload_register(function ($name) {
    $paths = explode("\\", $name);
    $path = implode(DIRECTORY_SEPARATOR, $paths);
    $path = "{$path}.php";

    if(file_exists($path))
    {
        /** @noinspection PhpIncludeInspection */
        require_once($path);
    }
});

if (lpErrorHandling) {
    set_error_handler(function ($no, $str, $file, $line, $varList) {
        throw new \LightPHP\Core\Exception\PHPException($str, 0, $no, $file, $line, null, $varList);
    });
}
