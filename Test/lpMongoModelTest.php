<?php

require_once(dirname(__FILE__) . "/../LightPHP.php");

class UserMongoModel extends lpMongoModel
{
    protected static function metaData($data = null)
    {
        return parent::meta([
            "table" => "user"
        ]);
    }
}

class lpMongoModelTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        $funcRemoveID = function($row) {
            unset($row["_id"]);
            return $row;
        };

        // 注册数据库连接对象
        lpFactory::register("MongoDB.LightPHP", function() {
            return (new MongoClient)->selectDB("test");
        });

        // 删除旧的数据表
        lpMongoModel::getDB()->dropCollection("user");

        // 插入数据
        $time = time();
        UserMongoModel::insert(["uname" => "jySperm", "passwd" => "1a91f14ce7", "signup_at" => $time]);
        UserMongoModel::insertArray([
            ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "signup_at" => "12345"],
            ["uname" => "faceair", "passwd" => "42ce82da15", "settings" => ["group" => "admin"], "signup_at" => "12345"],
            ["uname" => "abort", "passwd" => "49cf267661"]
        ]);

        // 更新数据
        UserMongoModel::update(["email" => "i@orzfly.com"], [
            "settings" => ["meizhi" => null]
        ]);
        UserMongoModel::update(["uname" => "faceair"], [
            "email" => "faceair.zh@gmail.com"
        ]);

        // 删除数据
        UserMongoModel::delete(["uname" => "abort"]);

        // 查询数据并做断言
        $this->assertEquals([
            0 => ["uname" => "jySperm", "passwd" => "1a91f14ce7", "signup_at" => $time],
            1 => ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => "12345"],
            2 => ["uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => "12345"]
        ], $funcRemoveID(UserMongoModel::selectArray()));
        $this->assertEquals([
            "i@orzfly.com" => ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => "12345"],
            "faceair.zh@gmail.com" => ["uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => "12345"]
        ], $funcRemoveID(UserMongoModel::selectPrimaryArray("email", ["signup_at" => "12345"])));
        $this->assertEquals([
            "1a91f14ce7", "946f4fe509", "42ce82da15"
        ], UserMongoModel::selectValueList("passwd"));
        $this->assertEquals(3, UserMongoModel::select()->count());

        // 实例化部分
        $this->assertEquals(UserMongoModel::byID(UserMongoModel::by("passwd", "1a91f14ce7")["_id"]),
                            UserMongoModel::by("uname", "jySperm")->data());
        $this->assertEquals(null, UserMongoModel::by("uname", "orzfly")["settings"]["meizhi"]);

        // 高级查询
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ['$OR' => [["uid" => 2], ["uid" => 3]]]));
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ['$LT' => ["signup_at" => 20000]]));
        $this->assertEquals(["jySperm"], UserMongoModel::selectValueList("uname", ['$NE' => ["signup_at" => 12345]]));
        $this->assertEquals(["orzfly"], UserMongoModel::selectValueList("uname", ['$%LIKE%' => ["email" => "fly"]]));
        $this->assertEquals(["orzfly"], UserMongoModel::selectValueList("uname", ['$LIKE' => ["email" => "i%"]]));
        $this->assertEquals(["faceair"], UserMongoModel::selectValueList("uname", ['$REGEXP' => ["email" => "^f"]]));
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ["`signup_at` BETWEEN '10000' AND '20000'"]));

        // 查询选项
        $this->assertEquals(["orzfly", "faceair", "jySperm"], UserMongoModel::selectValueList("uname", [], ["sort" => ["signup_at", "uname" => false]]));
        $this->assertEquals([
            0 => ["uid" => "1", "uname" => "jySperm"],
            1 => ["uid" => "2", "uname" => "orzfly"],
            2 => ["uid" => "3", "uname" => "faceair"]
        ], UserMongoModel::selectArray([], ["select" => ["uid", "uname"]]));
        $this->assertEquals(["orzfly"], UserMongoModel::selectValueList("uname", [], ["skip" => 1, "limit" => 1]));
        $this->assertEquals(["jySperm", "orzfly"], UserMongoModel::selectValueList("uname", [], ["limit" => 2]));
    }
}
