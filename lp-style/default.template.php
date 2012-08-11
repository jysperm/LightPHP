<?php if(!isset($lpInTemplate)) die();
/*
##参数：
* string $titile - 页面标题
* string $LPPath - LightPHP相对于根目录的路径(如`/libs/LightPHP/`)
* bool $isMin - 是否使用CSS/JS库的min版本
* bool $isResponsive - 是否使用Bootstrap的相应式设计
* string $sidebar - 右边栏
* string $footer - 页脚
*/
?>
<?php

if(!isset($LPPath))
    $LPPath="";

if(isset($isMin) && $isMin)
    $isMin=".min";
else
    $isMin="";

if(!isset($isResponsive))
    $isResponsive=false;

?>
<!DOCTYPE html>
<html lang="cn">
  <head>
    <meta charset="utf-8">
    <?php if(isset($title)): ?>
      <title><?php echo $title;?></title>
    <?php endif; ?>
    <link href="<?php echo $LPPath;?>/lp-style/bootstrap/css/bootstrap<?php echo $isMin;?>.css" rel="stylesheet" type="text/css" />
    <?php if($isResponsive): ?>
      <link href="<?php echo $LPPath;?>/lp-style/bootstrap/css/bootstrap-responsive<?php echo $isMin;?>.css" rel="stylesheet" type="text/css" />
    <?php endif; ?>
    <link href="<?php echo $LPPath;?>/lp-style/LightPHP.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="#">LightPHP</a>
          <div class="nav-collapse">
            <ul class="nav">
              <li class="active"><a href="#">栏目0</a></li>
              <li><a href="#">栏目1</a></li>
              <li><a href="#">栏目2</a></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">栏目3<b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">子栏目A-1</a></li>
                  <li><a href="#">子栏目A-2</a></li>
                  <li class="divider"></li>
                  <li class="nav-header">B</li>
                  <li><a href="#">子栏目B-1</a></li>
                  <li><a href="#">子栏目B-2</a></li>
                </ul>
              </li>
            </ul>
            <form class="navbar-search pull-left">
              <input type="text" class="search-query span2" placeholder="搜索" />
            </form>
            <ul class="nav pull-right">
              <li><a href="#">栏目4</a></li>
              <li class="divider-vertical"></li>
              <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">栏目5<b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">栏目C-1</a></li>
                  <li><a href="#">栏目C-2</a></li>
                  <li class="divider"></li>
                  <li><a href="#">栏目C-3</a></li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div id="main" class="container">
      <div class="row-fluid">
        <div class="span9 well">
          <?php echo $lpContents;?>
        </div>
        <?php if(isset($sidebar)): ?>
          <div class="span3 well">
            <?php echo $sidebar;?>
          </div>
        <?php endif; ?>
      </div>
      <?php if(isset($footer)): ?>
        <hr />
        <footer>
          <?php echo $footer;?>
        </footer>
      <?php endif; ?>
    </div>
    <script type="text/javascript" src="<?php echo $LPPath;?>/lp-style/jquery/jquery-1.7.2<?php echo $isMin;?>.js"></script>
    <script type="text/javascript" src="<?php echo $LPPath;?>/lp-style/bootstrap/js/bootstrap<?php echo $isMin;?>.js"></script>
  </body>
</html>

