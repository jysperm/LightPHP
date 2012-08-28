<?php

function lpBeginBlock()
{
    ob_start();
}

function lpEndBlock()
{
    return ob_get_clean();
}

class lpTemplate
{
    private $isParse=false;

    public function __construct()
    {
        ob_start();
    }

    public function parse($filename,$lpVars=array())
    {
        if($this->isParse)
            lpGlobal::onError("lpTemplate::parse():对同一个实例进行了多次解析",__FILE__,__LINE__);
        $this->isParse=true;

        $lpContents_=ob_get_clean();

        $temp=function($lpFilename,$lpContents_,$lpVars_)
        {
            $lpInTemplate=true;

            foreach ($lpVars_ as $key => $value) 
            {
                $value=base64_encode(serialize($value));
                eval("\${$key} = unserialize(base64_decode('{$value}'));");
            }

            $lpContents=$lpContents_;

            $lpCode_=file_get_contents($lpFilename);
            eval("?>{$lpCode_} <?php ");
        };

        $temp($filename,$lpContents_,$lpVars);
    }
    
    public function __destruct()
    {
        if(!$this->isParse)
        {
            lpGlobal::onWarning("lpTemplate::__destruct():没有对该实例进行解析，捕捉到的输入将被释放",__FILE__,__LINE__);
            ob_end_flush();
        }
    }
    
	public static function parseFile($file)
    {
        $tmp=new lpTemplate;
        $tmp->parse($file);
        
        return true;
    }
}

?>
