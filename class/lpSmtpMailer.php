<?php

defined("lpInLightPHP") or die(header("HTTP/1.1 403 Not Forbidden"));

/**
*   该类用于通过SMTP协议发送简单邮件.
*
*   该类将通过 Socket 链接到 SMTP 服务器, 发送邮件.
*   目前尚不支持发送包含附件的邮件.
*
*   该类的一个实例表示一个已经配置好发信账户、地址的"发送器". 
*   可以反复通过一个实例的成员函数 send() 发送多封到不同收信箱, 不同内容的邮件.
*/

class lpSmtpMailer
{
    private $host;
    private $address;
    private $user;
    private $passwd;
    private $port;
    private $isAuth;

    private $isDebug;
    private $timeOut;
    private $myHostName;

    //以上数据成员, 都会通过构造函数来一次性赋值.

    const TXTMail = "TXTMail";
    const HTMLMail = "HTMLMail";
    
    private $socket = null;

    private $log;

    /**
    *   构造一个SMTP发信器实例.
    */

    public function __construct($host, $address, $user, $passwd = null, $port = 25, $isAuth = true,
                                  $isDebug = false, $timeOut = 30, $myHostName = "lpSmtpMail")
    {
        $this->isDebug = $isDebug;
        $this->port = $port;
        $this->isAuth = $isAuth;
        $this->timeOut = $timeOut;
        $this->myHostName = $myHostName;
            
        $this->host = $host;
        $this->address = $address;
        $this->user = $user;
        $this->passwd = $passwd;
    }

    /**
     *   发送一封邮件.
     *
     *   对于一个类实例, 你可以反复调用该函数来发送邮件.
     *
     * @param string $toAddress  收信人地址
     * @param string $title
     * @param string $body
     * @param string $contentType
     * @param string $ccList
     * @param string $bccList
     * @param string $addHeaders
     *
     * @return bool 是否全部发送成功
     * @see getLog()
     */
        
    public function send($toAddress, $title="", $body="", $contentType="TXT", $ccList="", $bccList="", $addHeaders="")
    {
        $mailFrom = $this->getAddress($this->stripComment($this->address));
        $body = preg_replace('/(^|(\r\n))(\.)/',"\1.\3", $body);
        
        $header = "MIME-Version:1.0\r\n";
        if($contentType == $this::HTMLMail)
            $header .= "Content-Type:text/html\r\n";
        
        $header .= "To: {$toAddress}\r\n";
        if(!$ccList)
            $header .= "Cc: {$ccList}\r\n";
            
        $header .= "From: {$this->address}<{$this->address}>\r\n";
        $header .= "Subject: {$title}\r\n";
        $header .= $addHeaders;
        $header .= "Date: ".date("r")."\r\n";
        $header .= "X-Mailer:lpSmtpMail on {$this->myHostName} by PHP/" . phpversion() . "\r\n";
        
        list($msec,$sec) = explode(" ",microtime());
        
        $header .= "Message-ID: <" . date("YmdHis", $sec) . "." . ($msec * 1000000) . ".{$mailFrom}>\r\n";
        $toList = explode(",", $this->stripComment($toAddress));

        if($ccList) 
            $toList = array_merge($toList, explode(",", $this->stripComment($ccList)));
        if($bccList)
            $toList = array_merge($toList, explode(",", $this->stripComment($bccList)));
            
        $isSent = true;
        
        foreach($toList as $rcptTo) 
        { 
            $rcptTo = $this->getAddress($rcptTo);

            if(!$this->openSocket()) 
            { 
                $this->log("Error: Cannot send email to {$rcptTo}\n"); 
                $isSent = false;
                continue;
            } 
            if($this->sendMail($rcptTo, $header, $body)) 
            { 
                $this->log("E-mail has been sent to <{$rcptTo}>\n");
            } 
            else 
            { 
                $this->log("Error: Cannot send email to <{$rcptTo}>\n"); 
                $isSent = false; 
            } 

            fclose($this->socket); 
            $this->log("Disconnected from remote host\n"); 
        }
        
        return $isSent; 
    }

    /**
    *    获取邮件发送日志.
    *
    *    @return string
    */

    public function getLog()
    {
        return $this->log;
    }

    /**
    *   打开到SMTP服务器的Socket连接.
    *
    *   @return bool 是否连接成功
    */
    
    private function openSocket() 
    { 
        $this->log("Trying to {$this->host}:{$this->port}\n"); 
        $this->socket = fsockopen($this->host, $this->port, $errno, $errStr, $this->timeOut);

        if(!($this->socket && $this->isOk())) 
        { 
            $this->log("Error: Cannot connenct to relay host {$this->host}\n"); 
            $this->log("Error: {$errStr} ({$errno})\n");
            return false;
        }

        $this->log("Connected to relay host {$this->host}\n");
        return true;
    }

    /**
    *   检查服务器的回复是否在预期之中.
    *
    *   @return bool 服务器回复是否正常
    */
    
    private function isOk() 
    { 
        $response = str_replace("\r\n", "", fgets($this->socket, 512)); 
        $this->log("Server: {$response}\n");

        if(!preg_match("/^[23]/", $response)) 
        { 
            fputs($this->socket, "QUIT\r\n"); 
            fgets($this->socket, 512); 
            $this->log("Error: Remote host returned \"{$response}\"\n"); 
            return false; 
        }

        return true; 
    }

    /**
    *   发送邮件的后端实现.
    *
    *   @param string $to     收信人地址, 经过 getAddress() 提取
    *   @param string $header 邮件头
    *   @param string $body   邮件内容
    *   
    *   @return bool 是否发送成功
    */
    
    private function sendMail($to, $header, $body) 
    { 
        if(!$this->sendCmd("HELO", $this->myHostName)) 
            return $this->error("sending HELO command"); 

        if($this->isAuth) 
        { 
            if(!$this->sendCmd("AUTH LOGIN", base64_encode($this->user))) 
                return $this->error("sending AUTH command");
            if(!$this->sendCmd("", base64_encode($this->passwd))) 
                return $this->error("sending AUTH command");
        }
        
        if(!$this->sendCmd("MAIL", "FROM:<{$this->address}>")) 
            return $this->error("sending MAIL FROM command");
        if(!$this->sendCmd("RCPT", "TO:<{$to}>")) 
            return $this->error("sending RCPT TO command"); 
        if(!$this->sendCmd("DATA")) 
            return $this->error("sending DATA command"); 
        if(!$this->sendMessage($header, $body)) 
            return $this->error("sending message"); 
        if(!$this->sendEom()) 
            return $this->error("sending <CR><LF>.<CR><LF> [EOM]"); 
        if(!$this->sendCmd("QUIT")) 
            return $this->error("sending QUIT command");
            
        return true; 
    }

    /**
    *   向SMTP服务器发送命令.
    *
    *   @param string $cmd  命令名称
    *   @param string $arg  命令参数
    *   
    *   @return bool 是否发送成功
    */
    
    private function sendCmd($cmd, $arg=null) 
    { 
        if($arg) 
        { 
            if(!$cmd) 
                $cmd = $arg; 
            else 
                $cmd .= " {$arg}"; 
        }
        
        fputs($this->socket, "{$cmd}\r\n");
        $this->log("Client: {$cmd}\n");
        return $this->isOk();
    }

    /**
    *   向SMTP服务器发送邮件正文.
    *
    *   @param string $header  邮件头
    *   @param string $body    邮件正文
    *   
    *   @return bool 是否发送成功
    */

    private function sendMessage($header, $body) 
    { 
        fputs($this->socket, "{$header}\r\n{$body}");
        $this->log(str_replace("\r\n", "\n"."> ", "\n{$header}\n> {$body}\n> ")); 
        return true; 
    }

    /**
    *   向SMTP服务器发送EOM.
    *
    *   http://en.wikipedia.org/wiki/End_of_message
    *   
    *   @return bool 是否发送成功
    */

    private function sendEom() 
    {
        fputs($this->socket, "\r\n.\r\n");
        $this->log("\nClient: . [EOM]\n"); 
        return $this->isOk(); 
    }

    /**
    *   剔除Email中的昵称
    *   
    *   @param string $address Email地址
    *
    *   @return string Email地址
    */

    private static function stripComment($address) 
    { 
        $comment = '/\([^()]*\)/'; 
        while(preg_match($comment, $address)) 
            $address = preg_replace($comment, "", $address); 
        return $address; 
    }

    /**
    *   剔除Email中的尖括号和非法字符
    *   
    *   @param string $address Email地址
    *
    *   @return string Email地址
    */
    
    private static function getAddress($address) 
    { 
        $address = preg_replace('/([ \t\r\n])+/', "", $address); 
        $address = preg_replace('/^.*<(.+)>.*$/', "\1", $address); 
        return $address; 
    }

    /**
    *   记录一条日志.
    *
    *   @param string $msg 日志内容
    */
    
    private function log($msg) 
    {
        if($this->isDebug)
            echo $msg;
        
        $this->log .= gmdate("Y.m.d H:i:s", time()) . " {$msg}";
    }

    /**
    *  在日志中记录一条错误.
    *
    *  @param string $msg 错误信息
     * @return bool
     */

    private function error($msg) 
    { 
        $this->log("Error: Error occurred while {$msg}.\n"); 
        return false; 
    }
}
