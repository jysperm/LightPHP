<?php

require_once(dirname(__FILE__) . "/../LightPHP.php");

class UserModel extends lpPDOModel
{
    protected static function metaData($data = null)
    {
        return parent::meta([
            "table" => "user",
            "primary" => "uid",
            "struct" => [
                "uid" => [self::INT, self::AI],
                "uname" => [self::VARCHAR => 256],
                "passwd" => [self::TEXT, self::NOTNULL],
                "email" => [self::TEXT],
                "settings" => [self::TEXT, self::JSON],
                "signup_at" => [self::UINT, self::DEFALT => "12345"],
            ]
        ]);
    }
}

class lpPDOModelTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        lpFactory::register("PDO.LightPHP", function() {
            return new PDO("mysql:host=localhost;dbname=test", "test", "passwd");
        });

        lpPDOModel::getDB()->exec(lpPDOModel::query("DROP TABLE `{0}`", ["user"]));

        //UserModel::install();
    }
}
