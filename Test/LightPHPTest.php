<?php

require_once(dirname(__FILE__) . "/../LightPHP.php");

class LightPHPTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        foreach(new DirectoryIterator(dirname(__FILE__) . "/../class") as $fileinfo)
        {
            if(!$fileinfo->isDot())
            {
                foreach(new DirectoryIterator($fileinfo->getPathname()) as $f)
                {
                    if(!$f->isDot())
                        require_once($f->getPathname());
                }
            }
        }
    }
}
