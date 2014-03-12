# 本地化

## 文件清单

    LightPHP
        Adapter
            ArrayLocale.php
            GetTextLocale.php
            JSONLocale.php
            LocaleInterface.php

        Exception
            LocaleNotExistException.php

        Wrapper
            AutoLocaleAgent.php

        LocaleAgent.php

## 配适器 (Adapter)

### ArrayLocale
ArrayLocale 提供了一个返回 PHP 数组的文件的形式的本地化文件格式，示例：

    <?php

    return [
        "hello" => "你好",
        "Locale" => "本地化"
    ];

### GetTextLocale
GetTextLocale 提供了 GUN gettext 的本地化文件格式，即 .po/mo 格式。

### JSONLocale
JSONLocale 提供了 JSON 的本地化文件格式，示例：

    {
        "hello": "你好",
        "Locale": "本地化"
    }
