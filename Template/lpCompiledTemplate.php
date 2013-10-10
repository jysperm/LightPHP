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

    public static function compile($source, $output)
    {
        $content = file_get_contents($source);
        if(!$content)
            return false;

        $rules = [
            // UTF8 BOM
            '/^(\xef\xbb\xbf)/' => '',
            // {$VARIABLES}
            '/\\{(\\\$[^\\s\\}]+)\\}/s' => '<?= \\1;?>',
            // ${EXPRESSION}
            '/\$\{(.+?)\}/is' => "<?= \\1;?>",
            // <!-- {else if EXPRESSION} -->
            '/\<\!\-\-\s*\{else\s*if\s+(.+?)\}\s*\-\-\>/is' => '<? elseif(\\1):?>',
            // <!-- {elif EXPRESSION} -->
            '/\<\!\-\-\s*\{elif\s+(.+?)\}\s*\-\-\>/is' => '<? elseif(\\1):?>',
            // <!-- {else} -->
            '/\<\!\-\-\s*\{else\}\s*\-\-\>/is' => '<? else:?>',
        ];

        foreach($rules as $ex => $replace)
            $content = preg_replace($ex, $replace, $content);


        $execRules = [
            // {#MSGID}
            '/\{#([^\}]+)\}/s' => function($match) {
                return lpFactory::get("lpLocale")->get($match[1]);
            },
        ];

        foreach($execRules as $ex => $callback)
            $content = preg_replace_callback($ex, $callback, $content);

        $flowRules = [
            // <!-- {loop ARRAY KEY VALUE} --> THEN <!-- {/loop} -->
            '/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/is' => "<?php if(is_array(\\1)): foreach(\\1 as \\2 => \\3):?>\\4<? endforeach; endif;?>",
            // <!-- {loop ARRAY VALUE} --> THEN <!-- {/loop} -->
            '/\<\!\-\-\s*\{loop\s+(\S+)\s+(\S+)\s*\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/loop\}\s*\-\-\>/is' => "<?php if(is_array(\\1)): foreach(\\1 as \\2):?>\\3<? endforeach; endif;?>",
            // <!-- {if EXPRESSION} --> THEN <!-- {/if} -->
            '/\<\!\-\-\s*\{if\s+(.+?)\}\s*\-\-\>(.+?)\<\!\-\-\s*\{\/if\}\s*\-\-\>/is' => '<? if(\\1):?>\\2<? endif;?>',
        ];

        foreach($flowRules as $ex => $replace)
            while(preg_match($ex, $content))
                $content = preg_replace($ex, $replace, $content);

        $execFlowRules = [
            // <!--{include FILE}-->
            '/<!--\s*{\s*include\s+([^\{\}]+)\s*\}\s*-->/i' => function($match) {
                return file_get_contents($match[1]);
            },
        ];

        foreach($execFlowRules as $ex => $callback)
            while(preg_match($ex, $content))
                $content = preg_replace_callback($ex, $callback, $content);

        if(!file_put_contents($output, $content))
            return false;
        return true;
    }
}
