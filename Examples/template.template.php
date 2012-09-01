<?php if(!isset($lpInTemplate)) die(); /*如果不是被模板引擎调用，则退出*/ ?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8" />
    <title><?php echo $title; ?></title>
  </head>
  <body>
    <h1><?php echo $title; ?></h1>
    <p>
      <b>段落1</b>
      <?php echo $p1; ?>
    </p>
    <p>
      <b>段落2</b>
      <?php echo $p2; ?>
    </p>
    <p>
      <?php echo $lpContents; ?>
    </p>
  </body>
</html>

<?php
/*
模板文件其实就是一个PHP文件，你可以写任何PHP代码.
你也还可以在模板文件中再继续嵌套模板.

你还可以使用下面的短标记来输出PHP变量，
但是这在PHP 5.4之前的版本中需要在php.ini中开启short_open_tag

    <?= $title ?>

*/

?>
