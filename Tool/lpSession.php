<?php

class lpSession
{
    /** @var bool|null 缓存当前登陆状态 */
    private $isAuth = null;
    /** @var int|null 当前用户 ID */
    private $userID = null;

    /** @var lpPlugins\UserCenter\UserModel 当前的用户 Model */
    private $userModel;
    /** @var int Token 的有效期，单位秒 */
    private $TokenExpired;

    public function __construct($userModel, $TokenExpired = 2592000)
    {
        $this->userModel = $userModel;
        $this->TokenExpired = $TokenExpired;
    }

    /**
     * @return bool 是否已经登录
     */
    public function isAuth()
    {
        if($this->isAuth !== null)
            return $this->isAuth;

        // Session 方式
        if(isset($_SESSION['lpIsAuth']) && $_SESSION['lpIsAuth'])
        {
            $this->isAuth = true;
            $this->userID = $_SESSION["lpUserID"];
            $this->userModel = $this->userModel->byID($this->userID);
            return true;
        }


        // Cookie 方式
        if(isset($_COOKIE['lpToken']) && self::tryTokenLogin($_COOKIE['lpToken']))
        {
            $_SESSION['lpIsAuth'] = true;
            $_SESSION["lpUserID"] = $this->userModel->id();
            $_SESSION["lpToken"] = $_COOKIE['lpToken'];
            return true;
        }

        $this->isAuth = false;
        return false;
    }

    /**
     * @return lpPlugins\UserCenter\UserModel|null 当前用户 Model
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
     * @param int $userID 用户ID
     */
    public function authenticated($userID)
    {
        $this->isAuth = true;
        $this->userID = $userID;
        $this->userModel = $this->userModel->byID($userID);
        $_SESSION['lpIsAuth'] = true;
        $_SESSION['userID'] = $userID;
    }

    /**
     *  生成一个新的 Token, 调用时需为登录状态
     */
    public function newToken()
    {
        return $this->userModel->getTokenModel()->newToken($this->userModel);
    }

    /**
     * 吊销 token
     *
     * @param string $token
     */
    public function revokeToken($token)
    {
        $this->userModel->getTokenModel()->byToken($token)->remove();
    }

    /**
     * 清空所有方式的登录信息，并注销 Token
     */
    public function logout()
    {
        if(isset($_SESSION['lpToken']))
            $this->revokeToken($_SESSION['lpToken']);

        setcookie('token', '', 1, '/');
        $this->resetSession();
    }

    /**
     * 尝试以 Token 登录
     *
     * @param $token
     * @return bool
     */
    public function tryTokenLogin($token)
    {
        $token = $this->userModel->getTokenModel()->byToken($token);

        if($token->data() && self::isTokenValid($token))
        {
            $token->renew();
            $this->isAuth = true;
            $this->userID = $token["user_id"];
            $this->userModel = $this->userModel->byID($this->userID);
            return true;
        }
        return false;
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