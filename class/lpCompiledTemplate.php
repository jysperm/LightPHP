<?php

class lpCompiledTemplate extends lpPHPTemplate
{
    public function output()
    {
        $_lpFile = $this->checkCache();

        foreach($this->values as $_lgKey => $_lgVar)
            $$_lgKey = $_lgVar;

        include($_lpFile);
    }

    public function checkCache()
    {
        $filename = substr($this->filename, strlen(DIR_ROOT) + 1);
        $filename = str_replace("/", "_", $filename);
        $folder = lgConfig::get("template")["compile_cache"] . "/" . lgApp::getLanguage();
        $file = "{$folder}/" . basename($filename, ".html") . ".php";

        if(!file_exists($file))
        {
            if(!is_dir($folder))
                mkdir($folder);
            if(!self::compile($this->filename, $file))
                throw new lpException("file access denied");
        }

        return $file;
    }

    public static function compile($source, $output)
    {
        $content = file_get_contents($source);
        if(!$content)
            return false;

        $rules = [
            // UTF8 BOM
            '/^(\xef\xbb\xbf)/' => '',
            // {$VARIABLES}
            "/\\{(\\\$[^\\s\\}]+)\\}/s" => '<?= \\1;?>',
            // ${EXPRESSION}
            '/\\\$\{(.+?)\}/is' => "<?= \\1;?>",
            // {#MSGID}
            '/\{#([^\}]+)\}/es' => "l('\\1')",
            // <!-- {else if EXPRESSION} -->
            '/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/is' => "<? else if(\\1):?>",
            // <!-- {elif EXPRESSION} -->
            '/\<\!\-\-\s*\{elif\s+(.+?)\}\s*\-\-\>/is' => "<? else if(\\1):?>",
            // <!-- {else} -->
            '/\<\!\-\-\s*\{else\}\s*\-\-\>/is' => '<? else:?>',
        ];

        foreach($rules as $ex => $replace)
            $content = preg_replace($ex, $replace, $content);

        $flowRules = [
            // <!-- {loop ARRAY KEY VALUE} --> THEN <!-- {/loop} -->
            '/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/is' => "<?php if(is_array(\\1)): foreach(\\1 as \\2 => \\3):?>\\4<? endforeach; endif;?>",
            // <!-- {loop ARRAY VALUE} --> THEN <!-- {/loop} -->
            '/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/is' => "<?php if(is_array(\\1)): foreach(\\1 as \\2):?>\\3<? endforeach; endif;?>",
            // <!-- {if EXPRESSION} --> THEN <!-- {/if} -->
            '/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/is' => "<? if(\\1):?>\\2<? endif;?>",
            // <!--{include FILE}-->
            '/<!--\s*{\s*include\s+([^\{\}]+)\s*\}\s*-->/ie' => '<? file_get_contents("\\1");?>'
        ];

        foreach($flowRules as $ex => $replace)
        {
            while(preg_match($ex, $content))
                $content = preg_replace($ex, $replace, $content);
        }

        file_put_contents($output, $content);
        return true;
    }
}
