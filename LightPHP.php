<?php

/* 选项 -- */

// 令 LightPHP 不接管错误处理
//const lpDisableErrorHandling = true;

/* -- 选项 */

const lpInLightPHP = true;

const lpDebug = 2;
const lpDefault = 1;
const lpProduction = 0;

function lpLoader($name)
{
    if(class_exists($name, false))
        return;

    static $groupMap = [
        "App" => ["lpApp", "lpFactory", "lpHandler", "lpPlugin", "lpRoute"],
        "Cache" => ["lpAPCCache", "lpFileCache", "lpMemCache"],
        "Exception" => ["lpException", "lpHandlerException", "lpPHPException", "lpPHPFatalException", "lpSQLException"],
        "Locale" => ["lpArrayLocale", "lpGetTextLocale", "lpJSONLocale"],
        "Lock" => ["lpFileLock", "lpMutex", "lpMySQLLock"],
        "Mailer" => ["lpMandrillMailer", "lpPHPMailer", "lpSmtpMailer"],
        "Model" => ["lpMongoModel", "lpPDOModel"],
        "Template" => ["lpCompiledTemplate", "lpPHPTemplate"],
        "Tool" => ["lpConfig", "lpDebug"]
    ];

    foreach($groupMap as $group => $classes)
        if(in_array($name, $classes))
            $name = "{$group}/{$name}";

    $path = dirname(__FILE__) ."/class/{$name}.php";
    if(file_exists($path))
        require_once($path);
}

spl_autoload_register("lpLoader");

if(!defined("lpDisableErrorHandling") || !lpDisableErrorHandling)
{
    set_error_handler(function($no, $str, $file, $line, $varList)
    {
        throw new lpPHPException($str, 0, $no, $file, $line, null, $varList);
    });
}

