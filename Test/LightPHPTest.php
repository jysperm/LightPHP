<?php

require_once(dirname(__FILE__) . "/../LightPHP.php");

class LightPHPTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        foreach(new DirectoryIterator(dirname(__FILE__) . "/..") as $fDir)
        {
            /** @var $fDir DirectoryIterator */
            if($fDir->isDir() && $fDir->getFilename() != "Test")
            {
                foreach(new DirectoryIterator($fDir->getPathname()) as $f)
                {
                    /** @var $f DirectoryIterator */
                    if(!$f->isDot() && substr($f->getFilename(), 0, 2) == "lp")
                        require_once($f->getPathname());
                }
            }
        }
    }
}
