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
        // 注册数据库连接对象
        lpFactory::register("PDO.LightPHP", function() {
            return new PDO("mysql:host=localhost;dbname=test", "test", "passwd");
        });

        // 删除旧的数据表
        lpPDOModel::getDB()->exec(lpPDOModel::query("DROP TABLE `{0}`", ["user"]));

        // 创建数据表
        UserModel::install();

        // 插入数据
        $time = time();
        UserModel::insert(["uname" => "jySperm", "passwd" => "1a91f14ce7", "signup_at" => $time]);
        UserModel::insertArray([
            ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com"],
            ["uname" => "faceair", "passwd" => "42ce82da15", "settings" => ["group" => "admin"]],
            ["uname" => "abort", "passwd" => "49cf267661"]
        ]);

        // 更新数据
        UserModel::update(["email" => "i@orzfly.com"], [
            "settings" => ["meizhi" => null]
        ]);
        UserModel::update(["uname" => "faceair"], [
            "email" => "faceair.zh@gmail.com"
        ]);

        // 删除数据
        UserModel::delete(["uname" => "abort"]);

        // 查询数据并做断言
        $this->assertEquals([
            0 => ["uid" => "1", "uname" => "jySperm", "passwd" => "1a91f14ce7", "email" => "", "settings" => "", "signup_at" => $time],
            1 => ["uid" => "2", "uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => "12345"],
            2 => ["uid" => "3", "uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => "12345"]
        ], UserModel::selectArray());
        $this->assertEquals([
            "i@orzfly.com" => ["uid" => "2", "uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => "12345"],
            "faceair.zh@gmail.com" => ["uid" => "3", "uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => "12345"]
        ], UserModel::selectPrimaryArray("email", ["signup_at" => "12345"]));
        $this->assertEquals([
            "1a91f14ce7", "946f4fe509", "42ce82da15"
        ], UserModel::selectValueList("passwd"));
        $this->assertEquals(3, UserModel::count());

        // 实例化部分
        $this->assertEquals(UserModel::byID(1)->data(),
                            UserModel::by("uname", "jySperm")->data());
        $this->assertEquals(null, UserModel::byID(2)["settings"]["meizhi"]);

        // 高级查询
        $this->assertEquals(["orzfly", "faceair"], UserModel::selectValueList("uname", ['$OR' => [["uid" => 2], ["uid" => 3]]]));
        $this->assertEquals(["orzfly", "faceair"], UserModel::selectValueList("uname", ['$LT' => ["signup_at" => 20000]]));
        $this->assertEquals(["jySperm"], UserModel::selectValueList("uname", ['$NE' => ["signup_at" => 12345]]));
        $this->assertEquals(["orzfly"], UserModel::selectValueList("uname", ['$%LIKE%' => ["email" => "fly"]]));
        $this->assertEquals(["orzfly"], UserModel::selectValueList("uname", ['$LIKE' => ["email" => "i%"]]));
        $this->assertEquals(["faceair"], UserModel::selectValueList("uname", ['$REGEXP' => ["email" => "^f"]]));
        $this->assertEquals(["orzfly", "faceair"], UserModel::selectValueList("uname", ["`signup_at` BETWEEN '10000' AND '20000'"]));

        // 查询选项
        $this->assertEquals(["orzfly", "faceair", "jySperm"], UserModel::selectValueList("uname", [], ["sort" => ["signup_at", "uname" => false]]));
        $this->assertEquals([
            0 => ["uid" => "1", "uname" => "jySperm"],
            1 => ["uid" => "2", "uname" => "orzfly"],
            2 => ["uid" => "3", "uname" => "faceair"]
        ], UserModel::selectArray([], ["select" => ["uid", "uname"]]));
        $this->assertEquals(["orzfly"], UserModel::selectValueList("uname", [], ["skip" => 1, "limit" => 1]));
        $this->assertEquals(["jySperm", "orzfly"], UserModel::selectValueList("uname", [], ["limit" => 2]));
    }
}
