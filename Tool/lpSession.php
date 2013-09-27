<?php

class lpSession
{
    private $isAuth = null;
    private $userID = null;
    private $userModel = null;

    public function __construct($userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @return bool 是否已经登录
     */
    public function isAuth()
    {
        if($this->isAuth !== null)
            return $this->isAuth;

        // Session 方式
        if(isset($_SESSION['is_auth']) && $_SESSION['is_auth'])
            return true;

        // Cookie 方式
        if(self::tryTokenLogin(self::getCookieToken()))
        {
            $_SESSION['is_auth'] = true;
            $_SESSION['user'] = $this->userModel;
            $_SESSION["userID"] = $this->userModel["id"];
            return true;
        }

        $this->isAuth = false;
        return false;
    }

    /**
     * @return UserModel 当前AccountModel
     */
    public function user()
    {
        if($this->isAuth())
            return $this->userModel;
        return null;
    }

    /**
     * 将当前会话标记为已验证
     *
     * @param string $userID  用户ID
     */
    public function authenticated($userID)
    {
        $this->isAuth = true;
        $this->userID = $userID;
        $_SESSION['is_auth'] = true;
        $_SESSION['userID'] = $userID;
    }

    /**
     *  生成一个新的 Token, 调用时需为登录状态
     */
    public static function newToken()
    {
        if(!self::isAuth())
            return null;
        return TokenModel::newToken(self::user());
    }

    /**
     * 吊销 token
     *
     * @param $token
     */
    public static function revokeToken($token)
    {
        TokenModel::byCode($token)->remove();
    }

    /**
     * 清空所有方式的登录信息
     */
    public static function logout()
    {
        if(isset($_SESSION['token']))
            self::revokeToken($_SESSION['token']);

        if(self::$token && self::$token != $_SESSION['token'])
            self::revokeToken(self::$token);

        setcookie('token', '', 1, '/');
        self::resetSession();
    }

    /**
     * 尝试以 Token 登录
     * Token 可能来自移动设备，也可能来自 Cookie
     *
     * @param $token
     * @return bool
     */
    public static function tryTokenLogin($token)
    {
        $token = TokenModel::byCode($token);

        if(!$token->isNull() && self::isTokenValid($token))
        {
            $token->renew();
            self::$isAuth = true;
            self::$userID = $token["account_id"];
            return true;
        }
        return false;
    }

    // Cookie 方式相关

    /**
     * @return string|null Cookie 中的 Token
     */
    public static function getCookieToken()
    {
        return isset($_COOKIE['token']) ? $_COOKIE['token'] : null;
    }
    /**
     *  通过 Cookie 记住密码
     */
    public static function cookieRemember()
    {
        setcookie('token', self::newToken(), time() + lgConfig::get("session")["remember_time"], '/');
    }

    // PHP Session 管理

    /**
     *  初始化Session
     */
    public static function initSession()
    {
        if(!isset($_SESSION))
            session_start();

        self::isAuth();
    }

    /**
     *  重置Session
     */
    public static function resetSession()
    {
        session_destroy();
        session_start();
        session_regenerate_id();
    }

    public static function set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public static function get($name, $remove = false)
    {
        $result = null;
        if(isset($_SESSION[$name]))
        {
            $result = $_SESSION[$name];
            if($remove)
                unset($_SESSION[$name]);
        }
        return $result;
    }

    // 私有成员

    /**
     * @param $row
     * @return bool
     */
    private static function isTokenValid($row)
    {
        return time() - $row['accessed_at'] < lgConfig::get("session")["remember_time"];
    }
}