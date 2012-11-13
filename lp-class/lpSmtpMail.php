<?php

class lpSmtpMail
{
    private $host;
    private $uname;
    private $passwd;
    private $address;
    
    private $port;
    private $isAuth; 
    private $timeOut; 
    private $isDebug;
    private $myHostName;
    
    private $socket=NULL;
    private $log;

    public function __construct($host=NULL,$uname=NULL,$passwd=NULL,$address=NULL,$port=25,$isAuth=true,$isDebug=false,$timeOut=30,$myHostName="lpSmtpMail") 
    { 
        global $lpCfgMailHost,$lpCfgMailAddress,$lpCfgMailUName,$lpCfgMailPasswd;

        $this->isDebug=$isDebug;
        $this->port =$port;
        $this->isAuth=$isAuth;
        $this->timeOut=$timeOut;
        
        if(!$host)
            $host=$lpCfgMailHost;
        if(!$uname)
            $uname=$lpCfgMailUName;
        if(!$passwd)
            $passwd=$lpCfgMailPasswd;
        if(!$address)
            $address=$lpCfgMailAddress;
            
        $this->host=$host;
        $this->uname=$uname;
        $this->passwd=$passwd;
        $this->address=$address;
        $this->myHostName=$myHostName;
    }
        
    public function send($toAddress,$title="",$body="",$contentType="TXT",$ccList="",$bccList="",$addHeaders="")
    {
        $mailFrom=$this->getAddress($this->stripComment($this->address));
        $body=ereg_replace("(^|(\r\n))(\.)","\1.\3",$body);
        
        $header="MIME-Version:1.0\r\n";
        if($contentType=="HTML")
            $header.="Content-Type:text/html\r\n";
        
        $header.="To: {$toAddress}\r\n";
        if (!$ccList)
            $header.="Cc: {$ccList}\r\n";
            
        $header.="From: {$this->address}<{$this->address}>\r\n";
        $header.="Subject: {$title}\r\n";
        $header.=$addHeaders;
        $header.="Date: ".date("r")."\r\n";
        $header.="X-Mailer:lpSmtpMail on {$this->myHostName} by PHP/".phpversion()."\r\n";
        
        list($msec,$sec)=explode(" ",microtime());
        
        $header.="Message-ID: <".date("YmdHis", $sec).".".($msec*1000000).".{$mailFrom}>\r\n";
        $toList=explode(",",$this->stripComment($toAddress));

        if($ccList) 
            $toList=array_merge($toList,explode(",",$this->stripComment($ccList)));
        if($bccList)
            $toList=array_merge($toList,explode(",",$this->stripComment($bccList)));
            
        $isSent=true;
        
        foreach ($toList as $rcptTo) 
        { 
            $rcptTo=$this->getAddress($rcptTo); 
            if(!$this->openSocket($rcptTo)) 
            { 
                $this->log("Error: Cannot send email to {$rcptTo}\n"); 
                $isSent=false;
                continue;
            } 
            if($this->sendMail($this->myHostName,$mailFrom,$rcptTo,$header,$body)) 
            { 
                $this->log("E-mail has been sent to <{$rcptTo}>\n");
            } 
            else 
            { 
                $this->log("Error: Cannot send email to <{$rcptTo}>\n"); 
                $isSent=false; 
            } 
            fclose($this->socket); 
            $this->log("Disconnected from remote host\n"); 
        }
        
        return $isSent; 
    }

    public function getLog()
    {
        return $this->log;
    }
    
    private function openSocket($address) 
    { 
        $this->log("Trying to {$this->host}:{$this->port}\n"); 
        $this->socket=fsockopen($this->host,$this->port,$errno,$errStr,$this->timeOut);
        if(!($this->socket && $this->isOk())) 
        { 
          $this->log("Error: Cannot connenct to relay host {$this->host}\n"); 
          $this->log("Error: {$errStr} ({$errno})\n");
          return false;
        } 
        $this->log("Connected to relay host {$this->host}\n");
        return true;;
    }
    
    private function isOk() 
    { 
        $response=str_replace("\r\n", "",fgets($this->socket,512)); 
        $this->log("Server: {$response}\n"); 
        if(!ereg("^[23]",$response)) 
        { 
            fputs($this->socket,"QUIT\r\n"); 
            fgets($this->socket,512); 
            $this->log("Error: Remote host returned \"{$response}\"\n"); 
            return false; 
        } 
        return true; 
    }
    
    private function sendMail($myHostName,$mailFrom,$to,$header,$body="") 
    { 
        if(!$this->sendCmd("HELO",$myHostName)) 
            return $this->error("sending HELO command"); 

        if($this->isAuth) 
        { 
            if(!$this->sendCmd("AUTH LOGIN",base64_encode($this->uname))) 
                return $this->error("sending HELO command");
            if(!$this->sendCmd("",base64_encode($this->passwd))) 
                return $this->error("sending HELO command");
        }
        
        if(!$this->sendCmd("MAIL","FROM:<{$mailFrom}>")) 
            return $this->error("sending MAIL FROM command");
        if(!$this->sendCmd("RCPT","TO:<{$to}>")) 
            return $this->error("sending RCPT TO command"); 
        if(!$this->sendCmd("DATA")) 
            return $this->error("sending DATA command"); 
        if(!$this->sendMessage($header,$body)) 
            return $this->error("sending message"); 
        if(!$this->SendEom()) 
            return $this->error("sending <CR><LF>.<CR><LF> [EOM]"); 
        if(!$this->sendCmd("QUIT")) 
            return $this->error("sending QUIT command");
            
        return true; 
    }
    
    private function sendCmd($cmd,$arg="") 
    { 
        if($arg) 
        { 
            if(!$cmd) 
                $cmd=$arg; 
            else 
                $cmd.=" {$arg}"; 
        }
        
        fputs($this->socket,"{$cmd}\r\n");
        $this->log("Client: {$cmd}\n"); 
        return $this->isOk(); 
    }

    private function sendMessage($header,$body) 
    { 
        fputs($this->socket,"{$header}\r\n{$body}");
        $this->log(str_replace("\r\n","Client: \n"."> ","{$header}\n> {$body}\n> ")); 
        return true; 
    }

    private function SendEom() 
    {
        fputs($this->socket,"\r\n.\r\n");
        $this->log("\nClient: . [EOM]\n"); 
        return $this->isOk(); 
    }

    private function stripComment($address) 
    { 
        $comment="\([^()]*\)"; 
        while(ereg($comment,$address)) 
            $address=ereg_replace($comment,"",$address); 
        return $address; 
    }
    
    private function getAddress($address) 
    { 
        $address=ereg_replace("([ \t\r\n])+","",$address); 
        $address=ereg_replace("^.*<(.+)>.*$","\1",$address); 
        return $address; 
    }
    
    private function log($msg) 
    {
        global $lpCfgTimeToChina;
        
        if($this->isDebug)
            echo $msg;
        
        $this->log.=gmdate("Y.m.d H:i:s",time()+$lpCfgTimeToChina)." {$msg}";
    }

    private function error($msg) 
    { 
        $this->log("Error: Error occurred while {$msg}.\n"); 
        return false; 
    }
}
?>
