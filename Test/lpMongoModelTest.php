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
        $funcRemoveID = function ($data) {
            return array_map(function ($row) {
                unset($row["_id"]);
                return $row;
            }, $data);
        };

        $funcGetFieldList = function ($field, $cur) {
            $result = [];
            foreach ($cur as $row)
                $result[] = $row[$field];
            return $result;
        };

        // 注册数据库连接对象
        lpFactory::register("MongoDB.LightPHP", function () {
            return (new MongoClient)->selectDB("test");
        });

        // 删除旧的数据表
        lpMongoModel::getDB()->dropCollection("user");

        // 插入数据
        $time = time();
        UserMongoModel::insert(["uname" => "jySperm", "passwd" => "1a91f14ce7", "signup_at" => $time]);
        UserMongoModel::insertArray([
            ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "signup_at" => 12345],
            ["uname" => "faceair", "passwd" => "42ce82da15", "settings" => ["group" => "admin"], "signup_at" => 12345],
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
            1 => ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => 12345],
            2 => ["uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => 12345]
        ], $funcRemoveID(UserMongoModel::selectArray()));
        $this->assertEquals([
            "i@orzfly.com" => ["uname" => "orzfly", "passwd" => "946f4fe509", "email" => "i@orzfly.com", "settings" => ["meizhi" => null], "signup_at" => 12345],
            "faceair.zh@gmail.com" => ["uname" => "faceair", "passwd" => "42ce82da15", "email" => "faceair.zh@gmail.com", "settings" => ["group" => "admin"], "signup_at" => 12345]
        ], $funcRemoveID(UserMongoModel::selectPrimaryArray("email", ["signup_at" => 12345])));
        $this->assertEquals([
            "1a91f14ce7", "946f4fe509", "42ce82da15"
        ], UserMongoModel::selectValueList("passwd"));
        $this->assertEquals(3, UserMongoModel::select()->count());

        // 实例化部分
        $this->assertEquals(UserMongoModel::byID(UserMongoModel::by("passwd", "1a91f14ce7")["_id"])->data(),
            UserMongoModel::by("uname", "jySperm")->data());
        $this->assertEquals(null, UserMongoModel::by("uname", "orzfly")["settings"]["meizhi"]);

        // 查询符
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ['$or' => [["passwd" => "946f4fe509"], ["passwd" => "42ce82da15"]]]));
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ["signup_at" => ['$lt' => 20000]]));
        $this->assertEquals(["jySperm"], UserMongoModel::selectValueList("uname", ["signup_at" => ['$ne' => 12345]]));
        $this->assertEquals(["orzfly", "faceair"], UserMongoModel::selectValueList("uname", ["signup_at" => ['$gt' => 10000, '$lt' => 20000]]));

        // 游标
        $this->assertEquals(["orzfly", "faceair", "jySperm"], $funcGetFieldList("uname", UserMongoModel::select()->sort(["signup_at" => 1, "uname" => -1])));
        $this->assertEquals(["orzfly"], $funcGetFieldList("uname", UserMongoModel::select()->skip(1)->limit(1)));
        $this->assertEquals(["jySperm", "orzfly"], $funcGetFieldList("uname", UserMongoModel::select()->limit(2)));

        // 查询选项
        $this->assertEquals([
            0 => ["uname" => "jySperm", "passwd" => "1a91f14ce7"],
            1 => ["uname" => "orzfly", "passwd" => "946f4fe509"],
            2 => ["uname" => "faceair", "passwd" => "42ce82da15"]
        ], $funcRemoveID(UserMongoModel::selectArray([], ["uname", "passwd"])));
        UserMongoModel::update(["signup_at" => 12345], ["signup_at" => 1234], ["multiple" => true]);
        $this->assertEquals(2, UserMongoModel::select(["signup_at" => 1234])->count());
        UserMongoModel::replace(["passwd" => "946f4fe509"], ["uname" => "orzfly"]);
        $this->assertEquals(null, UserMongoModel::by("uname", "orzfly")["passwd"]);
    }
}
