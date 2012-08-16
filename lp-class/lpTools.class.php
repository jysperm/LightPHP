<?php

class lpTools
{
    public static function onError($info,$file,$line)
    {
        if(lpCfgDebug)
        {
            $out="错误:{$info} in {$file} line {$line}";
            die($out);
        }
        else
        {
            //TODO:清空缓冲区
            die(lpErrorMsg);
        }
    }

    public static function rxMatch($rx,$str)
    {
        if(preg_match($rx,$str,$r) && isset($r[0]))
            return $r[0];
        else
            return NULL;
    }

    
}

?>
