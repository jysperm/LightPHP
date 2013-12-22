<?php

/* 初始化选项 -- */

// 令 LightPHP 接管错误处理
const lpErrorHandling = true;
// 令 LightPHP 包装超全局变量
const lpWrapSuperGlobals = true;

/* -- 初始化选项 */

const lpInLightPHP = true;

const lpDebug = 3;
const lpDeploy = 2;
const lpDefault = 1;
const lpProduction = 0;

spl_autoload_register(function ($name) {
    static $groupMap = [
        "App" => ["lpApp", "lpFactory", "lpHandler", "lpRouter"],
        "Cache" => ["lpAPCCache", "lpFileCache", "lpMemCache"],
        "Exception" => ["lpException", "lpHandlerException", "lpPHPException", "lpPHPFatalException", "lpSQLException"],
        "Locale" => ["lpArrayLocale", "lpGetTextLocale", "lpJSONLocale"],
        "Lock" => ["lpFileLock", "lpMutex", "lpMySQLLock"],
        "Mailer" => ["lpMandrillMailer", "lpPHPMailer", "lpSmtpMailer"],
        "Model" => ["lpMongoModel", "lpPDOModel"],
        "Pluggable" => ["lpPlugin", "lpPluggableHandler"],
        "Template" => ["lpCompiledTemplate", "lpPHPTemplate"],
        "Tool" => ["lpConfig", "lpDebug"]
    ];

    foreach ($groupMap as $group => $classes)
        if (in_array($name, $classes))
            $name = "{$group}/{$name}";

    $path = dirname(__FILE__) . "/{$name}.php";
    if (file_exists($path))
        require_once($path);
});

if (lpErrorHandling) {
    set_error_handler(function ($no, $str, $file, $line, $varList) {
        throw new lpPHPException($str, 0, $no, $file, $line, null, $varList);
    });
}
